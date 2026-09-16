<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dokters';
    protected $fillable = [
        'nama',
        'sip',
        'no_telp',
        'alamat'
    ];

    public function notajuals()
    {
        return $this->hasMany(Notajual::class, 'dokter_id');
    }
}
