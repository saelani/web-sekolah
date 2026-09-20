<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function login()
    {
        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'NISN / Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();

            $user = Auth::user();

            // 1. Jika user adalah admin, headmaster, atau teacher -> Arahkan ke panel Filament (/admin)
            if ($user->hasRole(['admin', 'headmaster', 'teacher'])) {
                return redirect()->intended('/admin');
            }

            // 2. Jika user adalah student tetapi data relasi student-nya belum ada
            if (! $user->student && ! $user->hasRole('student')) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                $this->addError('email', 'Data profil siswa tidak ditemukan untuk akun ini.');

                return;
            }

            // 3. Jika lolos sebagai student -> Arahkan mutlak ke dashboard siswa
            return redirect()->route('student.dashboard');
        }

        $this->addError('email', 'NISN / Email atau Password yang Anda masukkan salah.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.auth', ['title' => 'Login Sistem Sekolah']);
    }
}
