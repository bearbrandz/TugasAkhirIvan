<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Produkbatches;

class Notabeliproduk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notabelis_has_produks';
    protected $fillable = [
        'notabelis_id',
        'produkbatches_id',
        'quantity',
        'subtotal',
    ];

    public function produkbatches()
    {
        return $this->belongsTo(ProdukBatches::class, 'produkbatches_id');
    }

    public function batch()
    {
        return $this->belongsTo(Produkbatches::class, 'produkbatches_id');
    }

    public function notabeli()
    {
        return $this->belongsTo(Notabeli::class, 'notabelis_id');
    }


}
