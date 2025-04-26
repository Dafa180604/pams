<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class Keluhan extends Model
{
    use HasFactory;
    protected $table = 'keluhan';
    protected $primaryKey = 'id_keluhan';
    protected $fillable = ['id_keluhan', 'keterangan', 'foto_keluhan', 'tanggal', 'tanggapan'];

    public function Users()
    {
        return $this->belongsTo(Users::class, foreignKey: 'id_users');
    }
}
