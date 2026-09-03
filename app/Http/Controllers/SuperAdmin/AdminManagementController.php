<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminManagementController extends Controller
{
    public function index()
    {
        return Inertia::render("SuperAdmin/Admins/Index", [
            "admins" => User::role(["admin", "super_admin"])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "name"     => ["required", "string", "max:255"],
            "email"    => ["required", "email", "unique:users,email"],
            "password" => ["required", "confirmed", "min:8"],
            "role"     => ["required", "in:admin,super_admin"],
        ]);

        $admin = User::create([
            "name"     => $validated["name"],
            "email"    => $validated["email"],
            "password" => Hash::make($validated["password"]),
            "status"   => "active",
            "email_verified_at" => now(),
        ]);

        $admin->assignRole($validated["role"]);

        ActivityLog::record("admin_created", "Compte {$validated['role']} créé : {$admin->email}");

        return back()->with("status", "Compte administrateur créé.");
    }

    public function toggleStatus(User $admin)
    {
        $admin->update(["status" => $admin->status === "active" ? "blocked" : "active"]);

        ActivityLog::record("admin_status_changed", "Statut de {$admin->email} changé en {$admin->status}");

        return back()->with("status", "Statut mis à jour.");
    }

    public function destroy(User $admin)
    {
        ActivityLog::record("admin_deleted", "Compte supprimé : {$admin->email}");

        $admin->delete();

        return back()->with("status", "Compte supprimé.");
    }
}

