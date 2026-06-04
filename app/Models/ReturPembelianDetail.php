<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetail extends Model
{
    protected $table = 'retur_pembelian_details';

    protected $fillable = [
        'retur_pembelian_id',
        'notabelis_has_produks_id',
        'produkbatches_id',
        'produks_id',
        'qty',
        'harga_satuan',
        'subtotal',
        'alasan',
    ];

    public function retur()
    {
        return $this->belongsTo(ReturPembelian::class, 'retur_pembelian_id');
    }

    public function batch()
    {
        return $this->belongsTo(Produkbatches::class, 'produkbatches_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produks_id')->withTrashed();
    }

    public function notabeliProduk()
    {
        return $this->belongsTo(Notabeliproduk::class, 'notabelis_has_produks_id');
    }
}