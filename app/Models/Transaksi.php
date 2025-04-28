<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    protected $fillable = ['id_transaksi','jumlah_rp', 'status_pembayaran','rp_denda', 'tgl_pembayaran', 'uang_bayar' ,'kembalian','detail_biaya'];

    public function pemakaian()
    {
        return $this->belongsTo(Pemakaian::class, 'id_pemakaian');
    }
    public function biaya_beban()
    {
        return $this->belongsTo(BebanBiaya::class, 'id_beban_biaya');
    }
    public function kategori_biaya_air()
    {
        return $this->belongsTo(KategoriBiayaAir::class, 'id_kategori_biaya');
    }
    public function biaya_denda()
    {
        return $this->belongsTo(BiayaDenda::class, 'id_biaya_denda');
    }
    public function Laporan()
    {
        return $this->hasMany(Laporan::class, 'id_laporan');
    }
}
