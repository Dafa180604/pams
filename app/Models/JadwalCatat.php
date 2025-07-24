<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalCatat extends Model
{
    use HasFactory;
    protected $table='jadwal_catat';
    protected $primaryKey='id_jadwal_catat';
    protected $fillable=['id_jadwal_catat','tanggal_awal','tanggal_akhir'];
}
