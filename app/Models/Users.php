<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use HasApiTokens;
    protected $table = 'users';
    protected $primaryKey = 'id_users';
    protected $fillable = ['id_users', 'nama', 'alamat', 'rw', 'rt', 'no_hp', 'username', 'password', 'role', 'foto_profile', 'golongan', 'status', 'jumlah_air','jadwal_keinginan_bayar', 'akses_pelanggan'];
    // Model Users
    public function pemakaian()
    {
        return $this->hasMany(Pemakaian::class, 'id_users'); // <- foreign key yang benar
    }
    public function Keluhan()
    {
        return $this->hasMany(Keluhan::class, 'id_users');
    }
}
