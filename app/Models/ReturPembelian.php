<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturPembelian extends Model
{
    use SoftDeletes;

    protected $table = 'retur_pembelians';

    protected $fillable = [
        'no_retur',
        'notabelis_id',
        'pegawai_id',
        'tanggal_retur',
        'tgl_retur',
        'total',
        'total_retur',
        'alasan',
        'keterangan',
    ];

    public function notabeli()
    {
        return $this->belongsTo(Notabeli::class, 'notabelis_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function details()
    {
        return $this->hasMany(ReturPembelianDetail::class, 'retur_pembelian_id');
    }

    // Alias agar controller/view lama yang memakai "items" tetap jalan
    public function items()
    {
        return $this->hasMany(ReturPembelianDetail::class, 'retur_pembelian_id');
    }
}