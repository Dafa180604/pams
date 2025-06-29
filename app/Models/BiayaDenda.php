<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class BiayaDenda extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'biaya_denda';
    protected $primaryKey = 'id_biaya_denda';
    protected $fillable = ['id_biaya_denda', 'jumlah_telat', 'biaya_telat'];

    public function Transaksi()
    {
        return $this->hasOne(Transaksi::class, 'id_biaya_denda');
    }
}
