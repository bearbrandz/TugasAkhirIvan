<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gudang extends Model
{
    use SoftDeletes;
    use HasFactory;

    public function produkbatches(): HasMany
    {
        return $this->hasMany(Produkbatches::class);
    }

    public function terimaBatches(): HasMany
    {
        return $this->hasMany(Terimabatches::class);
    }
}
