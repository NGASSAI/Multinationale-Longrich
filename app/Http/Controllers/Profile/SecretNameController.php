<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\SetSecretNameRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretNameController extends Controller
{
    public function update(SetSecretNameRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'secret_name' => Hash::make(strtolower(trim($request->secret_name))),
            'secret_name_set_at' => now(),
        ]);

        return back()->with('status', 'Nom secret enregistré avec succès.');
    }
}
