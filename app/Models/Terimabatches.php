<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terimabatches extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'terimabatches';
    protected $fillable = [
        'produkbatches_id',
        'stok',
        'pegawai_id',
        'gudangs_id',
    ];

    public function gudangs(): BelongsTo
    {
        return $this->belongsTo(Gudang::class, 'gudangs_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(user::class, 'pegawai_id');
    }   

    public function produkbatches(): BelongsTo
    {
        return $this->BelongsTo(Produkbatches::class, 'produkbatches_id');
    }
}
