<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status'   => 'active',
        ]);

        $user->assignRole('client');

        Auth::login($user);

        $request->session()->regenerate();

return redirect()->route('dashboard')
    ->with('warning', 'Pensez à enregistrer un nom secret dans votre profil, il vous permettra de réinitialiser votre mot de passe en cas d\'oubli.');
    }

    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($request->throttleKey());

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        RateLimiter::clear($request->throttleKey());
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status === 'blocked') {
            Auth::logout();
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Votre compte a été suspendu. Contactez le support.',
            ]);
        }

        // Redirection selon le rôle
        return match (true) {
            $user->hasRole('super_admin') => redirect()->route('superadmin.dashboard'),
            $user->hasRole('admin')       => redirect()->route('admin.dashboard'),
            default                       => redirect()->route('dashboard'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
