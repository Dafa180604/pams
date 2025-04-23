<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BebanBiaya extends Model
{
    use HasFactory;
    protected $table='biaya_beban';
    protected $primaryKey='id_beban_biaya';
    protected $fillable=['id_beban_biaya','tarif','keterangan'];

    public function Transaksi(){
        return $this->hasOne(Transaksi::class,'id_transaksi');
    }
}
