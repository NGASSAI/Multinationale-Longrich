<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;
  use HasApiTokens, HasFactory, Notifiable, HasRoles;
  protected $fillable = [
    'name', 'email', 'phone', 'avatar', 'status', 'password',
    'secret_name', 'secret_name_set_at',
];

protected $hidden = [
    'password', 'remember_token', 'secret_name',
];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'secret_name_set_at' => 'datetime',
        'password' => 'hashed',
    ];
}
}
