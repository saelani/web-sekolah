<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';

    public string $token = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $isTokenValid = false;

    // Menangkap token dari URL jika pengguna mengklik link dari email
    public function mount($token = null)
    {
        if ($token) {
            $this->token = $token;
            $this->isTokenValid = true; // Atau lakukan validasi token database di sini
        }
    }

    // Aksi mengirim tautan reset password
    public function sendResetLink()
    {
        $this->validate([
            'email' => 'required|email|exists:sys_users,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar dalam sistem.',
        ]);

        // Logika pengiriman token / tautan pemulihan sandi
        // (Anda bisa menggunakan fitur bawaan Password::broker()->sendResetLink(...) atau kustom token)

        session()->flash('status', 'Tautan pemulihan kata sandi telah dikirim ke email Anda.');
    }

    // Aksi memperbarui password baru
    public function resetPassword()
    {
        $this->validate([
            'email' => 'required|email|exists:sys_users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::where('email', $this->email)->first();

        if ($user) {
            $user->password = Hash::make($this->password);
            $user->save();

            session()->flash('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');

            return redirect()->route('login');
        }

        $this->addError('email', 'Terjadi kesalahan. Pengguna tidak ditemukan.');
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.auth', ['title' => 'Pemulihan Kata Sandi']);
    }
}
