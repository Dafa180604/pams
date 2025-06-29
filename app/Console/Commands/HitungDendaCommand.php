<?php

namespace App\Console\Commands;

use App\Models\Users;
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

            // Check if should apply, update, or remove late fee
            if ($daysDifference >= 1) {

                // Find the appropriate late fee entry based on days late
                $biayaDenda = BiayaDenda::where('jumlah_telat', $daysDifference)
                    ->first();

                // If no exact match, find the appropriate category
                if (!$biayaDenda) {
                    // Get all late fee categories ordered by jumlah_telat descending
                    // This ensures we check from highest threshold to lowest
                    $allDendaCategories = BiayaDenda::orderBy('jumlah_telat', 'desc')->get();

                    // Find the appropriate category based on days difference
                    // Use the highest threshold that the daysDifference meets or exceeds
                    foreach ($allDendaCategories as $category) {
                        if ($daysDifference >= $category->jumlah_telat) {
                            $biayaDenda = $category;
                            break; // Take the first match (highest applicable threshold)
                        }
                    }
                }

                // If a matching late fee category is found
                if ($biayaDenda) {
                    // Check if we need to update the late fee
                    $needsUpdate = false;

                    if (!$data->id_biaya_denda) {
                        // No late fee applied yet
                        $needsUpdate = true;
                    } else {
                        // Late fee exists, check if we need to update
                        $detailBiaya = json_decode($data->detail_biaya, true);

                        // Update if:
                        // 1. Days have changed (increased or decreased), OR
                        // 2. Category has changed (different id_biaya_denda), OR
                        // 3. No detail recorded properly
                        if (isset($detailBiaya['denda']['jumlah_telat'])) {
                            $savedDays = $detailBiaya['denda']['jumlah_telat'];
                            $savedCategoryId = $detailBiaya['denda']['id'] ?? null;

                            if (
                                $daysDifference != $savedDays ||
                                $biayaDenda->id_biaya_denda != $savedCategoryId
                            ) {
                                $needsUpdate = true;
                            }
                        } else {
                            // Late fee exists but no detail recorded, update it
                            $needsUpdate = true;
                        }
                    }

                    if ($needsUpdate) {
                        // Use the direct Rupiah amount from the biaya_telat column
                        $rpDenda = $biayaDenda->biaya_telat;

                        // Calculate original total (subtract old late fee if exists)
                        $originalTotal = $data->jumlah_rp;
                        if ($data->rp_denda) {
                            $originalTotal -= $data->rp_denda;
                        }

                        // Update the transaction data with late fee information
                        $data->id_biaya_denda = $biayaDenda->id_biaya_denda;
                        $data->rp_denda = $rpDenda;

                        // Update the total amount to include the new late fee
                        $data->jumlah_rp = $originalTotal + $rpDenda;

                        // Update the detail_biaya JSON to include late fee
                        $detailBiaya = json_decode($data->detail_biaya, true) ?? [];
                        $detailBiaya['denda'] = [
                            'id' => $biayaDenda->id_biaya_denda,
                            'jumlah_telat' => $daysDifference, // Actual days late
                            'kategori_telat' => $biayaDenda->jumlah_telat, // Category threshold used
                            'biaya_telat' => $rpDenda, // Direct Rupiah amount
                            'rp_denda' => $rpDenda,  // Same as biaya_telat since it's a direct amount
                            'updated_at' => date('Y-m-d H:i:s') // Track when fee was last updated
                        ];
                        $data->detail_biaya = json_encode($detailBiaya);

                        // Save the updated transaction
                        $data->save();
                        $updated++;

                        $this->info("Updated denda for transaksi ID: {$data->id_transaksi} - Days: {$daysDifference} - Amount: {$rpDenda}");
                    }

                    // Check if user should be deactivated based on days late OR denda amount
                    // Deactivate user if late for more than 7 days OR if denda reaches 1,000,000
                    $currentDenda = $data->rp_denda ?? 0; // Get current denda from database
                    if (($daysDifference >= 7 || $currentDenda >= 1000000) && $data->pemakaian && $data->pemakaian->id_users) {
                        $user = Users::find($data->pemakaian->id_users);
                        if ($user && ($user->status == 'Aktif' || $user->status == null || $user->status == '')) {
                            $user->status = 'Tidak Aktif';
                            $user->save();
                            $userStatusUpdated++;

                            // Log for debugging with specific reason
                            if ($currentDenda >= 1000000) {
                                $this->info("User {$user->id_users} status changed to 'Tidak Aktif' due to denda reaching Rp " . number_format($currentDenda));
                            } else {
                                $this->info("User {$user->id_users} status changed to 'Tidak Aktif' due to {$daysDifference} days late");
                            }
                        }
                    }
                }
            } else {
                // Not late anymore, remove late fee if exists
                if ($data->id_biaya_denda) {
                    // Calculate original total (subtract old late fee)
                    $originalTotal = $data->jumlah_rp;
                    if ($data->rp_denda) {
                        $originalTotal -= $data->rp_denda;
                    }

                    // Remove late fee
                    $data->id_biaya_denda = null;
                    $data->rp_denda = 0;
                    $data->jumlah_rp = $originalTotal;

                    // Update detail_biaya to remove late fee
                    $detailBiaya = json_decode($data->detail_biaya, true) ?? [];
                    if (isset($detailBiaya['denda'])) {
                        unset($detailBiaya['denda']);
                    }
                    $data->detail_biaya = json_encode($detailBiaya);

                    // Save the updated transaction
                    $data->save();
                    $updated++;

                    $this->info("Removed denda for transaksi ID: {$data->id_transaksi} - No longer late");

                    // Reactivate user if payment is no longer late
                    if ($data->pemakaian && $data->pemakaian->id_users) {
                        $user = Users::find($data->pemakaian->id_users);
                        if ($user && $user->status == 'Tidak Aktif') {
                            // Check if user has other unpaid late transactions before reactivating
                            $otherLateTransactions = Transaksi::join('pemakaian', 'transaksi.id_pemakaian', '=', 'pemakaian.id_pemakaian')
                                ->where('pemakaian.id_users', $user->id_users)
                                ->where('transaksi.id_transaksi', '!=', $data->id_transaksi)
                                ->whereNotNull('transaksi.id_biaya_denda')
                                ->where('transaksi.rp_denda', '>', 0)
                                ->exists();

                            // Only reactivate if no other late transactions exist
                            if (!$otherLateTransactions) {
                                $user->status = 'Aktif';
                                $user->save();
                                $userStatusUpdated++;

                                $this->info("User {$user->id_users} status changed back to 'Aktif' - no more late payments");
                            }
                        }
                    }
                }
            }
        }

        $this->info("Perhitungan denda selesai!");
        $this->info("Total diproses: {$processed} transaksi");
        $this->info("Total diupdate: {$updated} transaksi");
        $this->info("Total user status diupdate: {$userStatusUpdated} user");

        return Command::SUCCESS;
    }
}