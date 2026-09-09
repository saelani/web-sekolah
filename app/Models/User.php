<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. Import Trait Sanctum

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens; // 2. Tambahkan HasApiTokens di sini

    protected $table = 'sys_users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // Ditambahkan agar bisa diisi
        'role_type', // Ditambahkan jika kolom ini digunakan
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; 
    }

    /**
     * Method kustom untuk memeriksa role pengguna (tanpa package Spatie).
     * Menerima string tunggal, banyak argumen, atau array role.
     */
    public function hasRole(...$roles): bool
    {
        // Ratakan argumen jika dikirim berupa array
        $roles = is_array($roles[0] ?? null) ? $roles[0] : $roles;

        // Ambil nilai role dari atribut di tabel sys_users
        $userRole = $this->role ?? $this->role_type ?? '';

        return in_array($userRole, $roles, true);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}