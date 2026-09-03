<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render("SuperAdmin/Settings", [
            "settings" => SiteSetting::pluck("value", "key"),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            "settings" => ["required", "array"],
        ]);

        foreach ($validated["settings"] as $key => $value) {
            SiteSetting::set($key, $value);
        }

        ActivityLog::record("settings_updated", "Paramètres du site modifiés");

        return back()->with("status", "Paramètres mis à jour.");
    }
}

