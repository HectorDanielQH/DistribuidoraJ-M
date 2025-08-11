<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'cedulaidentidad',
        'nombres',
        'apellido_materno',
        'apellido_paterno',
        'celular',
        'email',
        'direccion',
        'estado',
        'foto_perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function adminlte_image()
    {
        return '/images/logo_profile.webp';
    }

    public function adminlte_desc()
    {
        try {
            return auth()->user()->roles->first()->name;
        } catch (\Exception $e) {
            return 'Sin rol';
        }
    }

    public function adminlte_profile_url()
    {
        return 'profile/username';
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_usuario');
    }
}
