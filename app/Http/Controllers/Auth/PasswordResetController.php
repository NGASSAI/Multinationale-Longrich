<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyIdentityRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    // Étape 1 : formulaire email
    public function showEmailForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    // Étape 2 : vérification email + nom secret
    public function showVerifyForm(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return Inertia::render('Auth/VerifyIdentity', [
            'email' => $request->email,
        ]);
    }

    public function verifyIdentity(VerifyIdentityRequest $request)
    {
        $request->ensureIsNotRateLimited();

        $user = User::where('email', $request->email)->first();

        $normalizedSecret = strtolower(trim($request->secret_name));

        // Comparaison volontairement en temps constant (Hash::check) même si $user est null
        $isValid = $user
            && $user->secret_name
            && Hash::check($normalizedSecret, $user->secret_name);

        if (!$isValid) {
            RateLimiter::hit($request->throttleKey());

            throw ValidationException::withMessages([
                'secret_name' => 'Email ou nom secret incorrect.',
            ]);
        }

        RateLimiter::clear($request->throttleKey());

        // Génère un accès temporaire de 10 minutes, invisible pour l'utilisateur
        $accessToken = Str::random(64);

        Cache::put(
            "password_reset_access:{$user->id}",
            Hash::make($accessToken),
            now()->addMinutes(10)
        );

        $request->session()->put('password_reset_user_id', $user->id);
        $request->session()->put('password_reset_access_token', $accessToken);
        $request->session()->put('password_reset_expires_at', now()->addMinutes(10));

        return redirect()->route('password.reset.form');
    }

    // Étape 3 : nouveau mot de passe
    public function showResetForm(Request $request)
    {
        $this->ensureValidResetSession($request);

        return Inertia::render('Auth/ResetPassword');
    }

    public function reset(Request $request)
    {
        $userId = $this->ensureValidResetSession($request);

        $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
        ]);

        $user = User::findOrFail($userId);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Sécurité : invalide toutes les autres sessions actives de ce compte
        $user->tokens()->delete(); // si Sanctum utilisé pour API
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Nettoyage de l'accès temporaire
        Cache::forget("password_reset_access:{$user->id}");
        $request->session()->forget([
            'password_reset_user_id',
            'password_reset_access_token',
            'password_reset_expires_at',
        ]);

        return redirect()->route('login')
            ->with('status', 'Mot de passe réinitialisé avec succès. Connectez-vous.');
    }

    /**
     * Vérifie que l'utilisateur a bien complété l'étape 2
     * avant d'autoriser l'accès à l'étape 3. Bloque tout accès direct.
     */
    private function ensureValidResetSession(Request $request): int
    {
        $userId = $request->session()->get('password_reset_user_id');
        $token = $request->session()->get('password_reset_access_token');
        $expiresAt = $request->session()->get('password_reset_expires_at');

        if (!$userId || !$token || !$expiresAt || now()->greaterThan($expiresAt)) {
            abort(403, 'Session de réinitialisation expirée ou invalide. Recommencez la procédure.');
        }

        $cachedHash = Cache::get("password_reset_access:{$userId}");

        if (!$cachedHash || !Hash::check($token, $cachedHash)) {
            abort(403, 'Session de réinitialisation invalide.');
        }

        return $userId;
    }
}
