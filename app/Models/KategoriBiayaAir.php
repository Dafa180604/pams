<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class KategoriBiayaAir extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table='kategori_biaya_air';
    protected $primaryKey='id_kategori_biaya';
    protected $fillable=['id_kategori_biaya','batas_bawah','batas_atas','tarif'];

    public function Transaksi(){
        return $this->hasOne(Transaksi::class,'id_kategori_biaya');
    }
}
