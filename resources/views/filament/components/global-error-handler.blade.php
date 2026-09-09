<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, content, preventDefault }) => {
                // Hentikan perilaku bawaan Livewire (membuka tab/modal error mentah)
                preventDefault();

                let errorMessage = 'Terjadi kesalahan pada sistem.';

                if (status === 419) {
                    errorMessage = 'Sesi Anda telah berakhir. Silakan muat ulang halaman.';
                } else if (status === 403) {
                    errorMessage = 'Anda tidak memiliki hak akses untuk tindakan ini.';
                } else if (status === 500) {
                    errorMessage = 'Terjadi kesalahan pada server. Coba beberapa saat lagi.';
                }

                // Kirim Notifikasi Filament bergaya Modal Toast
                new FilamentNotification()
                    .title('Gagal Memproses Data')
                    .body(errorMessage)
                    .danger()
                    .send();
            });
        });
    });

    // Tangkap unhandled error dari JavaScript di WebView Android
    window.addEventListener('error', function (event) {
        console.error('JS Error Captured:', event.error);
    });
</script>