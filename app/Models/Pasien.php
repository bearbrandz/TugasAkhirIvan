<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasien extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pasiens';

    protected $fillable = [
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_telp',
        'alamat'
    ];

    public function notajuals()
    {
        return $this->hasMany(Notajual::class, 'pasien_id');
    }
}
