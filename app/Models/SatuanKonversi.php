<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuanKonversi extends Model
{
    protected $table = 'satuan_konversi';

    protected $fillable = [
        'satuan_besar_id',
        'satuan_kecil_id',
        'nilai_konversi',
    ];

    public function satuanBesar()
    {
        return $this->belongsTo(Satuan::class, 'satuan_besar_id');
    }

    public function satuanKecil()
    {
        return $this->belongsTo(Satuan::class, 'satuan_kecil_id');
    }

    // Alias agar kode lama yang memakai satuanDari tetap jalan
    public function satuanDari()
    {
        return $this->belongsTo(Satuan::class, 'satuan_besar_id');
    }

    // Alias agar kode lama yang memakai satuanKe tetap jalan
    public function satuanKe()
    {
        return $this->belongsTo(Satuan::class, 'satuan_kecil_id');
    }
}