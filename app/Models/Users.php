<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Users extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    protected $table='users';
    protected $primaryKey='id_users';
    protected $fillable=['id_users','nama','alamat','rw','rt','no_hp','username','password','role','foto_profile','golongan','jumlah_air','akses_pelanggan'];
    public function Pemakaian(){
        return $this->hasMany(Pemakaian::class,'id_pemakaian');
    }
}
