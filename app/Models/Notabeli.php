<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notabeli extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['pegawai_id'];

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


    public function notaBeliProduks()
    {
        return $this->hasMany(NotaBeliProduk::class, 'notabelis_id');
    }
}
