<?php

namespace App\Livewire\Student\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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

            // Cek apakah akun memiliki relasi student
            if (! Auth::user()->student) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                $this->addError('email', 'Akun ini bukan akun siswa. Silakan login via Admin Panel.');
                return;
            }

            // REDIRECT STANDAR BROWSER (Mencegah bentrokan AJAX Livewire & Filament Auth Guard)
            return redirect()->intended(route('student.dashboard'));
        }

        $this->addError('email', 'NISN / Email atau Password yang Anda masukkan salah.');
    }

    public function render()
    {
        return view('livewire.student.auth.login')
            ->layout('components.layouts.app', ['title' => 'Login Portal Siswa']);
    }
}