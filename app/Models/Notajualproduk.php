<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notajualproduk extends Model//pakai model agar bisa dipakai query untuk forecasting
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'notajuals_has_produks';
    protected $fillable = [
        'notajuals_id',
        'produkbatches_id',
        'quantity',
        'subtotal',
    ];

    public function Produkbatches()
    {
        return $this->belongsTo(ProdukBatches::class, 'produkbatches_id');
    }

    public function notajual()
    {
        return $this->belongsTo(Notajual::class, 'notajuals_id');
    }
}
