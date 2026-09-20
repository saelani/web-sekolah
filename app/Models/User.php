<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'sys_users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_type',
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

    /**
     * Kontrak Akses Panel Filament Admin
     * HANYA MENGEMBALIKAN BOOLEAN (true/false)
     * DILARANG PAKAI redirect()->send() ATAU exit DI SINI!
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Jika user adalah siswa, kembalikan false (akses ditolak)
        if ($this->hasRole('student')) {
            return false;
        }

        // Hanya izinkan admin, teacher, dan headmaster
        return $this->hasRole('admin', 'teacher', 'headmaster');
    }

    /**
     * Method kustom untuk memeriksa role pengguna (tanpa package Spatie).
     * Menerima string tunggal, banyak argumen, atau array role.
     */
    public function hasRole(...$roles): bool
    {
        $roles = is_array($roles[0] ?? null) ? $roles[0] : $roles;
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
