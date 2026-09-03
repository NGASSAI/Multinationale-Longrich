<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Role::firstOrCreate(["name" => "client"]);

        $response = $this->post("/inscription", [
            "name" => "Jean Test",
            "email" => "jean@test.com",
            "password" => "MotDePasse123!",
            "password_confirmation" => "MotDePasse123!",
        ]);

        $this->assertDatabaseHas("users", ["email" => "jean@test.com"]);
        $this->assertTrue(User::where("email", "jean@test.com")->first()->hasRole("client"));
    }

    public function test_user_can_login(): void
    {
        Role::firstOrCreate(["name" => "client"]);

        $user = User::factory()->create(["password" => Hash::make("password123")]);
        $user->assignRole("client");

        $response = $this->post("/connexion", [
            "email" => $user->email,
            "password" => "password123",
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_blocked_user_cannot_login(): void
    {
        Role::firstOrCreate(["name" => "client"]);

        $user = User::factory()->create([
            "password" => Hash::make("password123"),
            "status" => "blocked",
        ]);
        $user->assignRole("client");

        $response = $this->post("/connexion", [
            "email" => $user->email,
            "password" => "password123",
        ]);

        $this->assertGuest();
    }
}
