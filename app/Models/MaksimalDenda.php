<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaksimalDenda extends Model
{
    use HasFactory;
    protected $table='maksimal_denda';
    protected $primaryKey='id_maksimal_denda';
    protected $fillable=['id_maksimal_denda','jumlah_maksimal'];
}
