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

            // Validasi: Pastikan akun ini benar-benar terhubung ke data siswa
            if (! Auth::user()->student) {
                Auth::logout();
                $this->addError('email', 'Akun ini bukan akun siswa. Silakan login via Admin Panel.');
                return;
            }

            // REDIRECT EKSPLISIT (Bukan intended) agar tidak masuk ke Filament/Admin Panel
            //  return redirect(route('student.dashboard'));
            // Gunakan redirectRoute milik Livewire
            $this->redirectRoute('student.dashboard', navigate: true);
            return;
        }

        $this->addError('email', 'NISN / Email atau Password yang Anda masukkan salah.');
    }

    public function render()
    {
        return view('livewire.student.auth.login')
            ->layout('components.layouts.app', ['title' => 'Login Portal Siswa']);
    }
}