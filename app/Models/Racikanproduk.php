<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Racikanproduk extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'racikanproduks';
    protected $fillable = [
        'racikans_id',
        'produks_id',
        'quantity',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(produk::class, 'produks_id');
    }

    public function racikan(): BelongsTo
    {
        return $this->belongsTo(Racikan::class, 'racikans_id');
    }
}
