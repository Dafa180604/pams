<?php

namespace App\Console\Commands;

use App\Models\Users;
use DB;
use Illuminate\Console\Command;
use App\Models\Transaksi;
use App\Models\BiayaDenda;
use DateTime;

class HitungDendaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'denda:hitung';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung denda untuk transaksi yang belum bayar secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai perhitungan denda...');

        // Ambil semua transaksi yang belum bayar dengan relasi user
        $transaksiList = Transaksi::with(['pemakaian.users'])
            ->where('status_pembayaran', 'Belum Bayar')
            ->get();

        $updated = 0;
        $processed = 0;
        $userStatusUpdated = 0;
        $notificationsSent = 0;

        foreach ($transaksiList as $data) {
            $processed++;

            if (!$data->pemakaian || !$data->pemakaian->waktu_catat) {
                continue;
            }

            // Calculate days since the recording date
            $waktuCatat = new DateTime($data->pemakaian->waktu_catat);
            $today = new DateTime();
            $interval = $waktuCatat->diff($today);
            $daysDifference = $interval->days;

            $this->info("Processing transaksi ID: {$data->id_transaksi} - Days late: {$daysDifference}");

            // Kirim notifikasi peringatan berdasarkan data biaya_denda
            $this->checkAndSendWarningNotifications($data, $daysDifference, $notificationsSent);

            // --- [AWAL PERBAIKAN LOGIKA] ---

            $biayaDenda = null; // Inisialisasi denda sebagai null

            // Cek apakah transaksi telat dan cari kategori denda yang sesuai
            if ($daysDifference >= 1) {
                // Cari kategori denda yang paling sesuai (terbesar yang kurang dari atau sama dengan jumlah hari telat)
                $biayaDenda = BiayaDenda::where('jumlah_telat', '<=', $daysDifference)
                    ->orderBy('jumlah_telat', 'desc')
                    ->first();
            }

            // SEKARANG, kita tentukan tindakan berdasarkan apakah $biayaDenda ditemukan atau tidak.

            // KASUS 1: ADA KATEGORI DENDA YANG SESUAI (TRANSAKSI TELAT)
            if ($biayaDenda) {
                $this->info("Found BiayaDenda for {$daysDifference} days: ID {$biayaDenda->id_biaya_denda}, threshold {$biayaDenda->jumlah_telat}, amount {$biayaDenda->biaya_telat}");

                $needsUpdate = false;

                if (!$data->id_biaya_denda) {
                    // Belum ada denda, perlu ditambah
                    $needsUpdate = true;
                    $this->info("No late fee applied yet, needs update");
                } else {
                    // Sudah ada denda, cek apakah perlu diupdate
                    $detailBiaya = json_decode($data->detail_biaya, true);
                    if (isset($detailBiaya['denda']['jumlah_telat'])) {
                        $savedDays = $detailBiaya['denda']['jumlah_telat'];
                        $savedCategoryId = $detailBiaya['denda']['id'] ?? null;

                        if ($daysDifference != $savedDays || $biayaDenda->id_biaya_denda != $savedCategoryId) {
                            $needsUpdate = true;
                            $this->info("Late fee needs update - Days changed: {$savedDays} -> {$daysDifference}, Category changed: {$savedCategoryId} -> {$biayaDenda->id_biaya_denda}");
                        }
                    } else {
                        $needsUpdate = true; // Detail denda tidak ada, perbarui
                        $this->info("Late fee exists but no detail recorded, needs update");
                    }
                }

                if ($needsUpdate) {
                    // Logika untuk MENAMBAH atau MENGUPDATE denda
                    $rpDenda = $biayaDenda->biaya_telat;
                    $originalTotal = $data->jumlah_rp - ($data->rp_denda ?? 0);

                    $data->id_biaya_denda = $biayaDenda->id_biaya_denda;
                    $data->rp_denda = $rpDenda;
                    $data->jumlah_rp = $originalTotal + $rpDenda;

                    $detailBiaya = json_decode($data->detail_biaya, true) ?? [];
                    $detailBiaya['denda'] = [
                        'id' => $biayaDenda->id_biaya_denda,
                        'jumlah_telat' => $daysDifference,
                        'kategori_telat' => $biayaDenda->jumlah_telat,
                        'biaya_telat' => $rpDenda,
                        'rp_denda' => $rpDenda,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $data->detail_biaya = json_encode($detailBiaya);

                    $data->save();
                    $updated++;

                    $this->info("Updated denda for transaksi ID: {$data->id_transaksi} - Days: {$daysDifference} - Amount: {$rpDenda}");

                    // Refresh data after update
                    $data = Transaksi::with(['pemakaian.users'])->find($data->id_transaksi);

                    // PERBAIKAN: Kirim notifikasi denda hanya jika memenuhi kondisi
                    if ($data->pemakaian->users) {
                        $this->sendUserDendaNotification($data, $daysDifference, $rpDenda);
                        // Tidak increment $notificationsSent di sini karena sudah dihandle di dalam method
                    }
                }

                // Check if user should be deactivated based on BiayaDenda configuration
                if ($biayaDenda->biaya_telat >= 1000000 && $data->pemakaian && $data->pemakaian->id_users) {
                    $user = Users::find($data->pemakaian->id_users);
                    if ($user && ($user->status == 'Aktif' || $user->status == null || $user->status == '')) {
                        $user->status = 'Tidak Aktif';
                        $user->save();
                        $userStatusUpdated++;

                        $this->info("User {$user->id_users} status changed to 'Tidak Aktif' due to biaya_telat reaching Rp " . number_format($biayaDenda->biaya_telat) . " (threshold: Rp 1,000,000)");
                    }
                }
            }
            // KASUS 2: TIDAK ADA KATEGORI DENDA / TIDAK TELAT, TAPI DENDA SEBELUMNYA ADA
            else {
                // [PERBAIKAN KUNCI] - Blok ini akan dieksekusi jika:
                // 1. $daysDifference < 1 (tidak telat)
                // 2. $daysDifference >= 1 tapi tidak ada kategori denda yang cocok

                // Cek apakah ada denda yang tercatat yang perlu dihapus
                if ($data->id_biaya_denda || $data->rp_denda > 0) {
                    $this->info("Removing late fee for transaction {$data->id_transaksi}. Days difference: {$daysDifference} (no applicable late fee category or not late)");

                    // Hapus informasi denda
                    $originalTotal = $data->jumlah_rp - ($data->rp_denda ?? 0);
                    $data->id_biaya_denda = null;
                    $data->rp_denda = 0;
                    $data->jumlah_rp = $originalTotal;

                    // Hapus denda dari detail_biaya JSON
                    $detailBiaya = json_decode($data->detail_biaya, true) ?? [];
                    if (isset($detailBiaya['denda'])) {
                        unset($detailBiaya['denda']);
                    }
                    $data->detail_biaya = json_encode($detailBiaya);

                    $data->save();
                    $updated++;

                    $this->info("Removed denda for transaksi ID: {$data->id_transaksi}");

                    // Refresh the data after updates
                    $data = Transaksi::with(['pemakaian.users'])->find($data->id_transaksi);

                    // [PERBAIKAN] Reactivate user if payment is no longer late and they don't have other high penalty transactions
                    if ($data->pemakaian && $data->pemakaian->id_users) {
                        $user = Users::find($data->pemakaian->id_users);
                        if ($user && $user->status == 'Tidak Aktif') {
                            // Check if user has other transactions with biaya_telat >= 1000000 before reactivating
                            $otherHighPenaltyTransactions = Transaksi::join('pemakaian', 'transaksi.id_pemakaian', '=', 'pemakaian.id_pemakaian')
                                ->join('biaya_denda', 'transaksi.id_biaya_denda', '=', 'biaya_denda.id_biaya_denda')
                                ->where('pemakaian.id_users', $user->id_users)
                                ->where('transaksi.id_transaksi', '!=', $data->id_transaksi)
                                ->where('transaksi.status_pembayaran', 'Belum Bayar')
                                ->where('biaya_denda.biaya_telat', '>=', 1000000)
                                ->exists();

                            // Only reactivate if no other high penalty transactions exist
                            if (!$otherHighPenaltyTransactions) {
                                $user->status = 'Aktif';
                                $user->save();
                                $userStatusUpdated++;

                                // Log for debugging
                                $this->info("User {$user->id_users} status changed back to 'Aktif' - no more high penalty transactions (>= Rp 1,000,000)");
                            }
                        }
                    }
                } else {
                    // Tidak ada denda yang perlu dihapus (kondisi normal)
                    if ($daysDifference >= 1) {
                        $this->info("Transaction {$data->id_transaksi} is {$daysDifference} days late but no applicable late fee category found");
                    } else {
                        $this->info("Transaction {$data->id_transaksi} is not late (days difference: {$daysDifference})");
                    }
                }
            }
            // --- [AKHIR PERBAIKAN LOGIKA] ---
        }

        $this->info("Perhitungan denda selesai!");
        $this->info("Total diproses: {$processed} transaksi");
        $this->info("Total diupdate: {$updated} transaksi");
        $this->info("Total user status diupdate: {$userStatusUpdated} user");
        $this->info("Total notifikasi WhatsApp dikirim: {$notificationsSent} notifikasi");

        return Command::SUCCESS;
    }

    /**
     * Periksa dan kirim notifikasi peringatan berdasarkan threshold biaya_denda
     */
    /**
     * Periksa dan kirim notifikasi peringatan berdasarkan threshold biaya_denda
     */
    private function checkAndSendWarningNotifications($transaksi, $daysDifference, &$notificationsSent)
    {
        try {
            if (!$transaksi->pemakaian->users) {
                $this->info("No user found for transaction {$transaksi->id_transaksi}");
                return;
            }

            // Ambil semua kategori biaya denda yang tersedia, urutkan dari kecil ke besar
            $dendaCategories = BiayaDenda::orderBy('jumlah_telat', 'asc')->get();

            $this->info("Checking warning notifications for user {$transaksi->pemakaian->users->id_users} - Days late: {$daysDifference}");

            // Cari kategori denda yang akan berlaku selanjutnya
            $nextDendaCategory = null;
            foreach ($dendaCategories as $biayaDenda) {
                if ($biayaDenda->jumlah_telat > $daysDifference) {
                    $nextDendaCategory = $biayaDenda;
                    break;
                }
            }

            if ($nextDendaCategory) {
                $daysToNextDenda = $nextDendaCategory->jumlah_telat - $daysDifference;
                $this->info("Next denda category: {$nextDendaCategory->jumlah_telat} days (Rp {$nextDendaCategory->biaya_telat}), Days to next denda: {$daysToNextDenda}");

                // PERBAIKAN UTAMA: Kirim notifikasi HANYA pada H-3 (tepat 3 hari sebelum denda)
                if ($daysToNextDenda == 3) {
                    // Cek apakah sudah pernah kirim notifikasi peringatan untuk threshold ini hari ini
                    $today = date('Y-m-d');
                    $notificationKey = "warning_{$transaksi->id_transaksi}_{$nextDendaCategory->jumlah_telat}_{$today}";

                    // Cek di cache atau session untuk memastikan tidak double kirim
                    $cacheKey = "notification_sent_{$notificationKey}";

                    // Gunakan cache sederhana dengan file atau bisa pakai Redis/Memcached
                    $cacheFile = storage_path("app/notification_cache/{$cacheKey}.lock");

                    // Jika file cache tidak ada, berarti belum pernah kirim notifikasi hari ini
                    if (!file_exists($cacheFile)) {
                        // Buat direktori jika belum ada
                        $cacheDir = dirname($cacheFile);
                        if (!is_dir($cacheDir)) {
                            mkdir($cacheDir, 0755, true);
                        }

                        // Kirim notifikasi
                        $this->sendUserWarningNotification($transaksi, $daysDifference, $nextDendaCategory, $daysToNextDenda);
                        $notificationsSent++;

                        // Buat file cache untuk mencegah double kirim
                        file_put_contents($cacheFile, json_encode([
                            'sent_at' => date('Y-m-d H:i:s'),
                            'transaction_id' => $transaksi->id_transaksi,
                            'denda_threshold' => $nextDendaCategory->jumlah_telat,
                            'user_id' => $transaksi->pemakaian->users->id_users
                        ]));

                        $this->info("Warning notification sent: Days={$daysDifference}, Next threshold={$nextDendaCategory->jumlah_telat}, Days remaining={$daysToNextDenda}, User={$transaksi->pemakaian->users->id_users}");
                    } else {
                        $this->info("Warning notification already sent today for transaction {$transaksi->id_transaksi} - threshold {$nextDendaCategory->jumlah_telat}");
                    }
                } else {
                    // Tidak kirim notifikasi jika bukan H-3
                    if ($daysToNextDenda > 0) {
                        $this->info("Not sending warning notification - Days to next denda: {$daysToNextDenda} (will send only on H-3)");
                    }
                }
            } else {
                $this->info("No next denda category found for {$daysDifference} days");
            }

            // Juga cek apakah user sudah melewati threshold denda dan perlu notifikasi denda
            $currentDendaCategory = BiayaDenda::where('jumlah_telat', '<=', $daysDifference)
                ->orderBy('jumlah_telat', 'desc')
                ->first();

            if ($currentDendaCategory) {
                $this->info("Current denda category: {$currentDendaCategory->jumlah_telat} days (Rp {$currentDendaCategory->biaya_telat})");
            }

        } catch (\Exception $e) {
            \Log::error('Error checking warning notifications: ' . $e->getMessage());
            $this->error('Error checking warning notifications: ' . $e->getMessage());
        }
    }
    private function shouldSendDendaNotification($transaksi, $biayaDenda, $daysDifference)
    {
        try {
            // KUNCI: Hanya kirim notifikasi jika hari ini TEPAT sama dengan threshold BiayaDenda
            if ($daysDifference != $biayaDenda->jumlah_telat) {
                $this->info("Skipping denda notification for transaction {$transaksi->id_transaksi} - Current days: {$daysDifference}, Threshold: {$biayaDenda->jumlah_telat}");
                return false;
            }

            $today = date('Y-m-d');

            // Cek apakah sudah pernah kirim notifikasi denda untuk threshold ini hari ini
            $cacheKey = "denda_notification_{$transaksi->id_transaksi}_{$biayaDenda->jumlah_telat}_{$today}";
            $cacheFile = storage_path("app/notification_cache/{$cacheKey}.lock");

            // Jika file cache ada, berarti sudah pernah kirim notifikasi hari ini
            if (file_exists($cacheFile)) {
                $this->info("Denda notification already sent today for transaction {$transaksi->id_transaksi} - threshold {$biayaDenda->jumlah_telat}");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error checking denda notification status: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Cleanup cache files yang sudah expired (optional - bisa dipanggil terpisah)
     */
    private function cleanupNotificationCache()
    {
        try {
            $cacheDir = storage_path("app/notification_cache");
            if (!is_dir($cacheDir)) {
                return;
            }

            $files = glob("{$cacheDir}/*.lock");
            $yesterday = date('Y-m-d', strtotime('-1 day'));

            foreach ($files as $file) {
                $content = file_get_contents($file);
                $data = json_decode($content, true);

                if (isset($data['sent_at'])) {
                    $sentDate = date('Y-m-d', strtotime($data['sent_at']));

                    // Hapus cache yang lebih dari 1 hari
                    if ($sentDate < $yesterday) {
                        unlink($file);
                        $this->info("Cleaned up old notification cache: " . basename($file));
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error cleaning notification cache: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi peringatan ke user yang hampir kena denda
     */
    /**
     * Kirim notifikasi peringatan ke user yang hampir kena denda
     */
    private function sendUserWarningNotification($transaksi, $daysDifference, $biayaDenda, $daysRemaining)
    {
        try {
            $user = $transaksi->pemakaian->users;
            $userName = $user->nama ?? 'Pelanggan';
            $userPhone = $user->no_hp ?? null;

            if (!$userPhone) {
                \Log::warning("User {$user->id_users} tidak memiliki nomor HP untuk notifikasi peringatan.");
                $this->warn("User {$user->id_users} tidak memiliki nomor HP untuk notifikasi peringatan.");
                return;
            }

            // Format nomor user
            $formattedUserPhone = $this->formatPhoneNumber($userPhone);
            if (!$formattedUserPhone) {
                return;
            }

            // Cek apakah ini adalah denda terbanyak (yang akan menyebabkan pencabutan layanan)
            $maxDendaCategory = BiayaDenda::orderBy('jumlah_telat', 'desc')->first();
            $isMaxDendaCategory = $maxDendaCategory && ($biayaDenda->id_biaya_denda == $maxDendaCategory->id_biaya_denda);

            // Siapkan pesan WhatsApp berdasarkan jenis peringatan
            if ($isMaxDendaCategory) {
                // PERINGATAN KHUSUS: H-3 SEBELUM PENCABUTAN LAYANAN
                $message = "🔴 *PERINGATAN KRITIS - PENCABUTAN LAYANAN* 🔴\n\n";
                $message .= "Halo *{$userName}*,\n\n";
                $message .= "🚨 *LAYANAN AKAN DICABUT DALAM {$daysRemaining} HARI!* 🚨\n\n";
                $message .= "Tagihan air Anda akan dikenakan denda maksimal dan layanan akan dinonaktifkan dalam *{$daysRemaining} hari* lagi jika tidak segera dibayar!\n\n";
                $message .= "*Detail Tagihan:*\n";
                $message .= "=====================================\n";
                $message .= "*ID Transaksi:* {$transaksi->id_transaksi}\n";
                $message .= "*Periode:* " . date('F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
                $message .= "*Jumlah Tagihan Saat Ini:* Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n";
                $message .= "*Tanggal Catat:* " . date('d F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
                $message .= "*Telah Terlambat:* {$daysDifference} hari\n";
                $message .= "*Batas Akhir Pembayaran:* " . date('d F Y', strtotime($transaksi->pemakaian->waktu_catat . " +{$biayaDenda->jumlah_telat} days")) . "\n";
                // $message .= "*Denda Maksimal:* Rp " . number_format($biayaDenda->biaya_telat, 0, ',', '.') . "\n";
                // $message .= "*Total Jika Terkena Denda:* Rp " . number_format($transaksi->jumlah_rp + $biayaDenda->biaya_telat, 0, ',', '.') . "\n\n";
                // // $message .= "⛔ *KONSEKUENSI JIKA TIDAK BAYAR:*\n";
                // $message .= "• Layanan air akan DINONAKTIFKAN\n";
                // $message .= "• Denda maksimal Rp " . number_format($biayaDenda->biaya_telat, 0, ',', '.') . " akan dikenakan\n";
                // $message .= "• Biaya reaktivasi akan dikenakan\n";
                // $message .= "• Proses reaktivasi membutuhkan waktu hingga 1x24 jam\n\n";
                $message .= "💡 *SEGERA LAKUKAN PEMBAYARAN:*\n";
                $message .= "Bayar sekarang untuk menghindari pencabutan layanan!\n";
                $message .= "Sisa waktu: *{$daysRemaining} hari*\n\n";
            } else {
                // PERINGATAN BIASA: H-3 SEBELUM DENDA BERIKUTNYA
                $message = "⚠️ *PERINGATAN PEMBAYARAN MENDESAK* ⚠️\n\n";
                $message .= "Halo *{$userName}*,\n\n";
                $message .= "🚨 *SEGERA LUNASI PEMBAYARAN!*\n";
                $message .= "Tagihan air Anda akan dikenakan denda dalam *{$daysRemaining} hari* lagi!\n\n";
                $message .= "*Detail Tagihan:*\n";
                $message .= "-----------------------------------\n";
                $message .= "*ID Transaksi:* {$transaksi->id_transaksi}\n";
                $message .= "*Periode:* " . date('F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
                $message .= "*Jumlah Tagihan:* Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n";
                $message .= "*Tanggal Catat:* " . date('d F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
                $message .= "*Telah Terlambat:* {$daysDifference} hari\n";
                $message .= "*Akan Kena Denda:* " . date('d F Y', strtotime($transaksi->pemakaian->waktu_catat . " +{$biayaDenda->jumlah_telat} days")) . "\n";
                $message .= "*Denda yang Akan Dikenakan:* Rp " . number_format($biayaDenda->biaya_telat, 0, ',', '.') . "\n\n";

                // Tambahkan informasi tentang risiko pencabutan jika mendekati denda maksimal
                if ($maxDendaCategory && $biayaDenda->jumlah_telat > ($maxDendaCategory->jumlah_telat * 0.7)) {
                    $message .= "⚠️ *PERINGATAN TAMBAHAN:*\n";
                    $message .= "Jika terlambat hingga {$maxDendaCategory->jumlah_telat} hari, layanan akan dicabut!\n\n";
                }

                $message .= "💡 *HINDARI DENDA:*\n";
                $message .= "Lakukan pembayaran sebelum {$daysRemaining} hari ke depan!\n\n";
                $message .= "Terima kasih atas perhatiannya! 🙏";
            }

            $this->sendWhatsAppMessage($formattedUserPhone, $message);

            // Log dengan keterangan jenis peringatan
            $warningType = $isMaxDendaCategory ? "KRITIS (H-3 PENCABUTAN)" : "BIASA (H-3 DENDA)";
            \Log::info("Peringatan {$warningType} dikirim ke user {$user->id_users} - {$userName} ({$formattedUserPhone}) - Sisa {$daysRemaining} hari - Threshold: {$biayaDenda->jumlah_telat} hari");
            $this->info("Peringatan {$warningType} dikirim ke user {$user->id_users} - {$userName} ({$formattedUserPhone}) - Sisa {$daysRemaining} hari");

        } catch (\Exception $e) {
            \Log::error('Error sending user warning notification: ' . $e->getMessage());
            $this->error('Error sending user warning notification: ' . $e->getMessage());
        }
    }

   /**
 * Kirim notifikasi denda ke user
 */
private function sendUserDendaNotification($transaksi, $daysDifference, $rpDenda)
{
    try {
        // Dapatkan kategori denda saat ini
        $currentDendaCategory = BiayaDenda::where('jumlah_telat', '<=', $daysDifference)
            ->orderBy('jumlah_telat', 'desc')
            ->first();

        if (!$currentDendaCategory) {
            return; // Tidak ada kategori denda yang berlaku
        }

        // PERBAIKAN UTAMA: Cek apakah harus kirim notifikasi
        if (!$this->shouldSendDendaNotification($transaksi, $currentDendaCategory, $daysDifference)) {
            return;
        }

        $user = $transaksi->pemakaian->users;
        $userName = $user->nama ?? 'Pelanggan';
        $userPhone = $user->no_hp ?? null;

        if (!$userPhone) {
            \Log::warning("User {$user->id_users} tidak memiliki nomor HP untuk notifikasi denda.");
            return;
        }

        // Format nomor user
        $formattedUserPhone = $this->formatPhoneNumber($userPhone);
        if (!$formattedUserPhone) {
            return;
        }

        // PERBAIKAN: Ambil kategori denda dengan jumlah_telat terbanyak untuk threshold deaktivasi
        $maxDendaCategory = BiayaDenda::orderBy('jumlah_telat', 'desc')->first();
        
        // Cek apakah ini adalah denda maksimal (yang menyebabkan deaktivasi)
        $isMaxDenda = $maxDendaCategory && ($currentDendaCategory->id_biaya_denda == $maxDendaCategory->id_biaya_denda);
        
        // Cek apakah user sudah dinonaktifkan
        $isUserDeactivated = ($user->status == 'Tidak Aktif');

        // Siapkan pesan WhatsApp berdasarkan kondisi
        if ($isMaxDenda && !$isUserDeactivated) {
            // NOTIFIKASI KHUSUS: LAYANAN TELAH DINONAKTIFKAN
            $message = "🔴 *LAYANAN TELAH DINONAKTIFKAN* 🔴\n\n";
            $message .= "Halo *{$userName}*,\n\n";
            $message .= "🚨 **PENTING:** Layanan air Anda telah **DINONAKTIFKAN** karena denda keterlambatan telah mencapai batas maksimal.\n\n";
            // $message .= "*Detail Tagihan & Denda:*\n";
            // $message .= "=====================================\n";
            // $message .= "*ID Transaksi:* {$transaksi->id_transaksi}\n";
            // $message .= "*Periode:* " . date('F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
            // $message .= "*Tagihan Pokok:* Rp " . number_format($transaksi->jumlah_rp - $rpDenda, 0, ',', '.') . "\n";
            // $message .= "*Denda Maksimal:* Rp " . number_format($rpDenda, 0, ',', '.') . "\n";
            // $message .= "*Total Tagihan:* Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n";
            // // $message .= "*Terlambat:* {$daysDifference} hari\n";
            // $message .= "*Status Layanan:* **TIDAK AKTIF** ❌\n\n";
            
            $message .= "⛔ **KONSEKUENSI DEAKTIVASI:**\n";
            $message .= "• Aliran air telah dihentikan\n";
            $message .= "• Layanan tidak dapat digunakan\n";
            // $message .= "• Denda maksimal telah dikenakan\n\n";
            
            // $message .= "🔧 **UNTUK REAKTIVASI LAYANAN:**\n";
            // $message .= "1️⃣ **LUNASI SEMUA TAGIHAN** (Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . ")\n";
            // $message .= "2️⃣ **HUBUNGI CUSTOMER SERVICE** untuk proses reaktivasi\n";
            // $message .= "3️⃣ **TUNGGU PROSES REAKTIVASI** (maksimal 1x24 jam)\n";
            // $message .= "4️⃣ **BIAYA REAKTIVASI** mungkin akan dikenakan\n\n";
            
            // $message .= "📞 **SEGERA HUBUNGI CUSTOMER SERVICE:**\n";
            // $message .= "Jangan tunda lagi! Hubungi kami sekarang untuk proses reaktivasi.\n\n";
            $message .= "Terima kasih atas pengertiannya! 🙏";
            
        // } elseif ($isMaxDenda && !$isUserDeactivated) {
        //     // NOTIFIKASI KHUSUS: DENDA MAKSIMAL TAPI BELUM DINONAKTIFKAN
        //     $message = "🚨 *DENDA MAKSIMAL - LAYANAN AKAN DINONAKTIFKAN* 🚨\n\n";
        //     $message .= "Halo *{$userName}*,\n\n";
        //     $message .= "⚠️ **PERINGATAN KRITIS:** Denda keterlambatan Anda telah mencapai **BATAS MAKSIMAL**!\n";
        //     $message .= "Layanan akan dinonaktifkan dalam waktu dekat jika tidak segera dibayar.\n\n";
        //     $message .= "*Detail Tagihan & Denda:*\n";
        //     $message .= "=====================================\n";
        //     $message .= "*ID Transaksi:* {$transaksi->id_transaksi}\n";
        //     $message .= "*Periode:* " . date('F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
        //     $message .= "*Tagihan Pokok:* Rp " . number_format($transaksi->jumlah_rp - $rpDenda, 0, ',', '.') . "\n";
        //     $message .= "*Denda Maksimal:* Rp " . number_format($rpDenda, 0, ',', '.') . "\n";
        //     $message .= "*Total Tagihan:* Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n";
        //     $message .= "*Terlambat:* {$daysDifference} hari\n\n";
            
        //     $message .= "🔴 **RISIKO DEAKTIVASI:**\n";
        //     $message .= "• Layanan akan dinonaktifkan segera\n";
        //     $message .= "• Aliran air akan dihentikan\n";
        //     $message .= "• Proses reaktivasi diperlukan\n";
        //     $message .= "• Biaya tambahan mungkin dikenakan\n\n";
            
        //     $message .= "💡 **HINDARI DEAKTIVASI:**\n";
        //     $message .= "**SEGERA LAKUKAN PEMBAYARAN SEKARANG JUGA!**\n";
        //     $message .= "Total yang harus dibayar: Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n\n";
            
        //     $message .= "Hubungi customer service jika memerlukan bantuan. Terima kasih! 🙏";
            
        } else {
            // NOTIFIKASI BIASA: DENDA BELUM MAKSIMAL
            $message = "🚨 *PEMBERITAHUAN DENDA* 🚨\n\n";
            $message .= "Halo *{$userName}*,\n\n";
            $message .= "Tagihan air Anda telah dikenakan denda keterlambatan.\n\n";
            $message .= "*Detail Tagihan & Denda:*\n";
            $message .= "-----------------------------------\n";
            $message .= "*ID Transaksi:* {$transaksi->id_transaksi}\n";
            $message .= "*Periode:* " . date('F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
            $message .= "*Tagihan Pokok:* Rp " . number_format($transaksi->jumlah_rp - $rpDenda, 0, ',', '.') . "\n";
            $message .= "*Denda Keterlambatan:* Rp " . number_format($rpDenda, 0, ',', '.') . "\n";
            $message .= "*Total Tagihan:* Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n";
            $message .= "*Terlambat:* {$daysDifference} hari\n\n";

            // Tambahkan peringatan berdasarkan kategori denda maksimal
            if ($maxDendaCategory) {
                $daysToMaxDenda = $maxDendaCategory->jumlah_telat - $daysDifference;
                if ($daysToMaxDenda > 0) {
                    $message .= "⚠️ **PERHATIAN:** Jika keterlambatan mencapai {$maxDendaCategory->jumlah_telat} hari ({$daysToMaxDenda} hari lagi), layanan akan dinonaktifkan.\n\n";
                } else {
                    $message .= "⚠️ **PERHATIAN:** Anda telah melewati batas keterlambatan. Layanan dapat dinonaktifkan sewaktu-waktu.\n\n";
                }
            }

            $message .= "Silakan segera lakukan pembayaran untuk menghindari penonaktifan layanan.\n\n";
            $message .= "Hubungi customer service jika ada pertanyaan. Terima kasih! 🙏";
        }

        $this->sendWhatsAppMessage($formattedUserPhone, $message);

        // PENTING: Buat file cache untuk mencegah double kirim
        $today = date('Y-m-d');
        $cacheKey = "denda_notification_{$transaksi->id_transaksi}_{$currentDendaCategory->jumlah_telat}_{$today}";
        $cacheFile = storage_path("app/notification_cache/{$cacheKey}.lock");

        // Buat direktori jika belum ada
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Buat file cache dengan informasi tambahan
        file_put_contents($cacheFile, json_encode([
            'sent_at' => date('Y-m-d H:i:s'),
            'transaction_id' => $transaksi->id_transaksi,
            'denda_threshold' => $currentDendaCategory->jumlah_telat,
            'user_id' => $user->id_users,
            'denda_amount' => $rpDenda,
            'days_late' => $daysDifference,
            'is_max_denda' => $isMaxDenda,
            'is_user_deactivated' => $isUserDeactivated,
            'notification_type' => $isMaxDenda && $isUserDeactivated ? 'deactivation' : ($isMaxDenda ? 'max_denda_warning' : 'regular_denda')
        ]));

        // Log dengan detail kondisi
        $notificationType = $isMaxDenda && $isUserDeactivated ? 'DEAKTIVASI' : ($isMaxDenda ? 'DENDA MAKSIMAL' : 'DENDA BIASA');
        \Log::info("Notifikasi {$notificationType} dikirim ke user {$user->id_users} - {$userName} ({$formattedUserPhone}) - Denda: Rp " . number_format($rpDenda) . " pada hari ke-{$daysDifference} (threshold: {$currentDendaCategory->jumlah_telat}) - Status User: {$user->status}");

    } catch (\Exception $e) {
        \Log::error('Error sending user denda notification: ' . $e->getMessage());
    }
}


   

    /**
     * Format nomor telepon ke format internasional
     */
    private function formatPhoneNumber($phone)
    {
        if (!$phone || $phone === '-') {
            return null;
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Handle different phone number formats
        if (substr($phone, 0, 1) === '0') {
            // Indonesian local format (08xxx) -> 628xxx
            $formattedPhone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) === '62') {
            // Already in international format
            $formattedPhone = $phone;
        } elseif (substr($phone, 0, 3) === '+62') {
            // International format with + 
            $formattedPhone = substr($phone, 1);
        } else {
            // Assume it's Indonesian number without leading 0
            $formattedPhone = '62' . $phone;
        }

        // Validate Indonesian phone number format
        if (!preg_match('/^628[0-9]{8,12}$/', $formattedPhone)) {
            \Log::warning("Invalid phone number format: {$phone} -> {$formattedPhone}");
            return null;
        }

        return $formattedPhone;
    }

    /**
     * Kirim pesan WhatsApp menggunakan API Fonnte
     */
    private function sendWhatsAppMessage($targetPhone, $message)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => $targetPhone,
                    'message' => $message,
                    'countryCode' => '62',
                    'device' => '085735326182', // Ganti sesuai Device ID Anda
                    'typing' => true,
                    'delay' => 2,
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: hzDxTTTbvEgUw8XzpMFR', // Ganti dengan token API Anda
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (curl_errno($curl)) {
                \Log::error('cURL Error: ' . curl_error($curl));
            } elseif ($httpCode !== 200) {
                \Log::error("HTTP Error {$httpCode}: {$response}");
            }

            curl_close($curl);

            // Log response untuk debugging
            \Log::info("WhatsApp message sent to {$targetPhone} - HTTP: {$httpCode} - Response: " . $response);

        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp message: ' . $e->getMessage());
        }
    }
}


/**
 * Alternative: Menggunakan database untuk tracking notifikasi denda (lebih robust)
 */
// private function sendUserDendaNotificationDB($transaksi, $daysDifference, $rpDenda)
// {
//     try {
//         // PERBAIKAN UTAMA: Cek apakah ini adalah hari PERTAMA denda berlaku
//         $currentDendaCategory = BiayaDenda::where('jumlah_telat', '<=', $daysDifference)
//             ->orderBy('jumlah_telat', 'desc')
//             ->first();

//         if (!$currentDendaCategory) {
//             return; // Tidak ada kategori denda yang berlaku
//         }

//         // HANYA kirim notifikasi jika hari ini TEPAT adalah hari threshold denda
//         if ($daysDifference != $currentDendaCategory->jumlah_telat) {
//             \Log::info("Skipping denda notification for transaction {$transaksi->id_transaksi} - Current days: {$daysDifference}, Threshold: {$currentDendaCategory->jumlah_telat}");
//             return;
//         }

//         $today = date('Y-m-d');

//         // Cek di database apakah sudah pernah kirim notifikasi denda untuk threshold ini hari ini
//         $existingNotification = DB::table('notification_logs')
//             ->where('transaction_id', $transaksi->id_transaksi)
//             ->where('denda_threshold', $currentDendaCategory->jumlah_telat)
//             ->where('notification_type', 'denda')
//             ->where('sent_date', $today)
//             ->first();

//         if ($existingNotification) {
//             \Log::info("Denda notification already sent today for transaction {$transaksi->id_transaksi} - threshold {$currentDendaCategory->jumlah_telat}");
//             return;
//         }

//         $user = $transaksi->pemakaian->users;
//         $userName = $user->nama ?? 'Pelanggan';
//         $userPhone = $user->no_hp ?? null;

//         if (!$userPhone) {
//             \Log::warning("User {$user->id_users} tidak memiliki nomor HP untuk notifikasi denda.");
//             return;
//         }

//         // Format nomor user
//         $formattedUserPhone = $this->formatPhoneNumber($userPhone);
//         if (!$formattedUserPhone) {
//             return;
//         }

//         // Siapkan pesan WhatsApp
//         $message = "🚨 *PEMBERITAHUAN DENDA* 🚨\n\n";
//         $message .= "Halo *{$userName}*,\n\n";
//         $message .= "Tagihan air Anda telah dikenakan denda keterlambatan.\n\n";
//         $message .= "*Detail Tagihan & Denda:*\n";
//         $message .= "-----------------------------------\n";
//         $message .= "*ID Transaksi:* {$transaksi->id_transaksi}\n";
//         $message .= "*Periode:* " . date('F Y', strtotime($transaksi->pemakaian->waktu_catat)) . "\n";
//         $message .= "*Tagihan Pokok:* Rp " . number_format($transaksi->jumlah_rp - $rpDenda, 0, ',', '.') . "\n";
//         $message .= "*Denda Keterlambatan:* Rp " . number_format($rpDenda, 0, ',', '.') . "\n";
//         $message .= "*Total Tagihan:* Rp " . number_format($transaksi->jumlah_rp, 0, ',', '.') . "\n";
//         $message .= "*Terlambat:* {$daysDifference} hari\n\n";
//         $message .= "⚠️ *PERHATIAN:* Jika denda mencapai Rp 1.000.000, layanan akan dinonaktifkan.\n\n";
//         $message .= "Silakan segera lakukan pembayaran untuk menghindari penonaktifan layanan.\n\n";
//         $message .= "Hubungi customer service jika ada pertanyaan. Terima kasih! 🙏";

//         $this->sendWhatsAppMessage($formattedUserPhone, $message);

//         // Simpan log notifikasi ke database
//         DB::table('notification_logs')->insert([
//             'transaction_id' => $transaksi->id_transaksi,
//             'user_id' => $user->id_users,
//             'denda_threshold' => $currentDendaCategory->jumlah_telat,
//             'notification_type' => 'denda',
//             'sent_date' => $today,
//             'days_late' => $daysDifference,
//             'denda_amount' => $rpDenda,
//             'message_sent' => 'success',
//             'created_at' => now(),
//             'updated_at' => now()
//         ]);

//         \Log::info("Notifikasi denda dikirim dan dicatat ke database - User: {$user->id_users} - {$userName} ({$formattedUserPhone}) - Denda: Rp " . number_format($rpDenda) . " pada hari ke-{$daysDifference}");

//     } catch (\Exception $e) {
//         \Log::error('Error sending user denda notification: ' . $e->getMessage());
//     }
// }

 // private function sendUserDeactivationNotification($user, $currentDenda, $daysDifference)
    // {
    //     try {
    //         $userName = $user->nama ?? 'Pelanggan';
    //         $userPhone = $user->no_hp ?? null;

    //         if (!$userPhone) {
    //             \Log::warning("User {$user->id_users} tidak memiliki nomor HP untuk notifikasi deaktivasi.");
    //             return;
    //         }

    //         // Format nomor user
    //         $formattedUserPhone = $this->formatPhoneNumber($userPhone);
    //         if (!$formattedUserPhone) {
    //             return;
    //         }

    //         // Tentukan alasan deaktivasi
    //         $alasanDeaktivasi = '';
    //         if ($currentDenda >= 1000000) {
    //             $alasanDeaktivasi = "denda telah mencapai Rp " . number_format($currentDenda, 0, ',', '.');
    //         } else {
    //             $alasanDeaktivasi = "keterlambatan pembayaran selama {$daysDifference} hari.";
    //         }

    //         // Siapkan pesan WhatsApp
    //         $message = "🔴 *LAYANAN DINONAKTIFKAN* 🔴\n\n";
    //         $message .= "Halo *{$userName}*,\n\n";
    //         $message .= "Layanan air Anda telah dinonaktifkan karena {$alasanDeaktivasi}\n\n";
    //         $message .= "*Informasi Tagihan:*\n";
    //         $message .= "-----------------------------------\n";
    //         if ($currentDenda > 0) {
    //             $message .= "*Total Denda:* Rp " . number_format($currentDenda, 0, ',', '.') . "\n";
    //         }
    //         $message .= "*Terlambat:* {$daysDifference} hari\n\n";
    //         $message .= "🚨 *UNTUK REAKTIVASI LAYANAN:*\n";
    //         $message .= "1. Lunasi semua tagihan yang tertunggak\n";
    //         $message .= "2. Hubungi customer service kami\n";
    //         $message .= "3. Tunggu proses reaktivasi (maks 1x24 jam)\n\n";
    //         $message .= "Hubungi customer service segera untuk informasi lebih lanjut.\n\n";
    //         $message .= "Terima kasih atas pengertiannya! 🙏";

    //         $this->sendWhatsAppMessage($formattedUserPhone, $message);

    //         \Log::info("Notifikasi deaktivasi dikirim ke user {$user->id_users} - {$userName} ({$formattedUserPhone})");

    //     } catch (\Exception $e) {
    //         \Log::error('Error sending user deactivation notification: ' . $e->getMessage());
    //     }
    // }