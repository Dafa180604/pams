<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BiayaGolonganBerbayar extends Model
{
    use HasFactory;
    protected $table='biaya_golongan_berbayar';
    protected $primaryKey='id_biaya_golongan';
    protected $fillable=['id_biaya_golongan','tarif','keterangan'];

    // public function Laporan()
    // {
    //     return $this->hasOne(Laporan::class, 'id_laporan');
    // }
}
