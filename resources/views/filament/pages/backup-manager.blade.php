<x-filament-panels::page>
    <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
            🛡️ Pusat Pengamanan Data Aplikasi (Full Database Backup)
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
            Fitur ini digunakan untuk membuat cadangan (*backup*) atau memulihkan (*restore*) 
            <strong>seluruh database `web_sekolah`</strong> yang mencakup data kelas, siswa, presensi, 
            tabungan, kalender akademik, nilai, dan akun pengguna.
        </p>
        
        <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded text-amber-900 text-xs dark:bg-amber-900/30 dark:text-amber-200">
            <strong>Tips Keamanan:</strong> Lakukan pencadangan (Export) secara berkala (misalnya 1 minggu sekali) dan simpan file <code>.sql</code> hasil unduhan di tempat aman (Flashdisk/Google Drive).
        </div>
    </div>
</x-filament-panels::page>