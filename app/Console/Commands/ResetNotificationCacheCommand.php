<?php
//php artisan denda:reset-cache --today 
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ResetNotificationCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'denda:reset-cache {--all : Hapus semua cache} {--today : Hapus cache hari ini saja} {--transaction= : Hapus cache untuk transaksi tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset notification cache untuk testing ulang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Memulai pembersihan cache notifikasi...');

        $cacheDir = storage_path('app/notification_cache');
        
        // Buat directory jika belum ada
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
            $this->info("📁 Directory cache dibuat: {$cacheDir}");
        }

        $deletedCount = 0;

        if ($this->option('all')) {
            // Hapus semua cache
            $files = glob("{$cacheDir}/*.lock");
            foreach ($files as $file) {
                unlink($file);
                $deletedCount++;
            }
            $this->info("✅ Semua cache dihapus ({$deletedCount} files)");
            
        } elseif ($this->option('today')) {
            // Hapus cache hari ini saja
            $today = date('Y-m-d');
            $files = glob("{$cacheDir}/*_{$today}.lock");
            foreach ($files as $file) {
                unlink($file);
                $deletedCount++;
            }
            $this->info("✅ Cache hari ini dihapus ({$deletedCount} files)");
            
        } elseif ($transactionId = $this->option('transaction')) {
            // Hapus cache untuk transaksi tertentu
            $files = glob("{$cacheDir}/*_{$transactionId}_*.lock");
            foreach ($files as $file) {
                unlink($file);
                $deletedCount++;
            }
            $this->info("✅ Cache untuk transaksi {$transactionId} dihapus ({$deletedCount} files)");
            
        } else {
            // Tampilkan menu interaktif
            $choice = $this->choice(
                'Pilih jenis pembersihan cache:',
                [
                    'all' => 'Hapus SEMUA cache notifikasi',
                    'today' => 'Hapus cache HARI INI saja',
                    'specific' => 'Hapus cache TRANSAKSI TERTENTU',
                    'view' => 'LIHAT cache yang ada',
                    'cancel' => 'Batal'
                ],
                'view'
            );

            switch ($choice) {
                case 'all':
                    if ($this->confirm('⚠️ Yakin ingin hapus SEMUA cache notifikasi?')) {
                        $files = glob("{$cacheDir}/*.lock");
                        foreach ($files as $file) {
                            unlink($file);
                            $deletedCount++;
                        }
                        $this->info("✅ Semua cache dihapus ({$deletedCount} files)");
                    }
                    break;

                case 'today':
                    $today = date('Y-m-d');
                    $files = glob("{$cacheDir}/*_{$today}.lock");
                    foreach ($files as $file) {
                        unlink($file);
                        $deletedCount++;
                    }
                    $this->info("✅ Cache hari ini dihapus ({$deletedCount} files)");
                    break;

                case 'specific':
                    $transactionId = $this->ask('Masukkan ID Transaksi:');
                    if ($transactionId) {
                        $files = glob("{$cacheDir}/*_{$transactionId}_*.lock");
                        foreach ($files as $file) {
                            unlink($file);
                            $deletedCount++;
                        }
                        $this->info("✅ Cache untuk transaksi {$transactionId} dihapus ({$deletedCount} files)");
                    }
                    break;

                case 'view':
                    $this->viewCacheFiles($cacheDir);
                    break;

                case 'cancel':
                    $this->info('❌ Dibatalkan');
                    return;
            }
        }

        $this->info('');
        $this->info('🎯 Status cache setelah pembersihan:');
        $this->viewCacheFiles($cacheDir);
        
        $this->info('');
        $this->info('🚀 Siap untuk testing ulang!');
        $this->info('💡 Jalankan: php artisan denda:hitung');

        return Command::SUCCESS;
    }

    private function viewCacheFiles($cacheDir)
    {
        $files = glob("{$cacheDir}/*.lock");
        
        if (empty($files)) {
            $this->info('📂 Tidak ada cache files');
            return;
        }

        $this->info("📂 Cache files yang ada ({count($files)} files):");
        $this->info('─────────────────────────────────────────────────────────');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $content = json_decode(file_get_contents($file), true);
            
            $info = '';
            if (isset($content['transaction_id'])) {
                $info .= "Trans: {$content['transaction_id']} | ";
            }
            if (isset($content['user_id'])) {
                $info .= "User: {$content['user_id']} | ";
            }
            if (isset($content['sent_at'])) {
                $info .= "Sent: {$content['sent_at']}";
            }
            
            $this->line("• {$filename}");
            if ($info) {
                $this->line("  └─ {$info}");
            }
        }
    }
}