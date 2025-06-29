<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Pemakaian extends Model
{
    use HasFactory;
    protected $table = 'pemakaian';
    protected $primaryKey = 'id_pemakaian';
    protected $fillable = ['id_pemakaian', 'meter_awal', 'meter_akhir', 'jumlah_pemakaian', 'foto_meteran','waktu_catat', 'petugas'];


    public function users()
    {
        return $this->belongsTo(Users::class, 'id_users');
    }
    
    public function Transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_pemakaian');
    }
}
