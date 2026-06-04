<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelianItem extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian_items';

    protected $fillable = [
        // Foreign key linking to the retur header
        'retur_pembelian_id',
        // Foreign key linking to the returned product
        'produk_id',
        // Quantity being returned
        'jumlah',
        // Unit price used to calculate the return
        'harga_satuan',
        // Pre‑calculated subtotal for this line
        'subtotal',
    ];

    public function retur()
    {
        return $this->belongsTo(ReturPembelian::class, 'retur_pembelian_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
