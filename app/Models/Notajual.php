<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notajual extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_nota',
        'pegawai_id',
        'total_bayar',
        'nominal_bayar',
        'kembalian',
        'metode_bayar',
    ];

    /**
     * Get the user that owns the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(user::class, 'pegawai_id');
    }

    /**
     * Get the user that customer the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function notaJualProduks()
    {
        return $this->hasMany(Notajualproduk::class, 'notajuals_id');
    }

    public function notaJualRacikans()
    {
        return $this->hasMany(Notajualracikan::class, 'notajuals_id');
    }
}
