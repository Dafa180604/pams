<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Laporan extends Model
{
    use HasFactory;
    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';
    protected $fillable = ['id_laporan', 'keterangan','status', 'uang_masuk', 'uang_keluar', 'sisa_saldo'];

    public function Transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }
    public function biayagolongan()
    {
        return $this->belongsTo(BiayaGolonganBerbayar::class, 'id_biaya_golongan');
    }
}
