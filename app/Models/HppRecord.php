<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HppRecord extends Model
{
    use HasFactory;

    protected $table = 'hpp_records';

    /**
     * The attributes that are mass assignable.
     *
     * We track which batch and product the change relates to, the
     * quantity involved, the unit price of the transaction, the
     * previous and new average costs, and an optional description.
     */
    protected $fillable = [
        'produkbatches_id',
        'produks_id',
        'stok_lama',
        'harga_lama',
        'stok_baru',
        'harga_baru',
        'hpp_avg_baru',
        'tipe',
        'notabelis_id',
    ];

    public function batch()
    {
        return $this->belongsTo(Produkbatches::class, 'produkbatches_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produks_id')->withTrashed();
    }
}
