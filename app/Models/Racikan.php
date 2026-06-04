<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Racikan extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'nama',
        'biaya_embalase',
        'deskripsi',
        'nama_dokter',
        'nama_pasien',
        'alamat_dokter',
        'alamat_pasien',
        'aturan_pakai',
        'bukti_resep',
        'tgl_ambil',
    ];

    public function racikanProduks()
    {
        return $this->hasMany(Racikanproduk::class, 'racikans_id');
    }

    public function notaJualRacikans()
    {
        return $this->hasMany(Notajualracikan::class, 'racikans_id');
    }

    public function produks()
    {
        return $this->belongsToMany(
            Produk::class,
            'racikanproduks',    
            'racikans_id',       
            'produks_id'         
        )
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
