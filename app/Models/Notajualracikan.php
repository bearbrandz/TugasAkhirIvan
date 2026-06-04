<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notajualracikan extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'notajuals_has_racikans';
    protected $fillable = [
        'notajuals_id',
        'racikans_id',
        'quantity',
        'subtotal',
    ];

    public function racikan()
    {
        return $this->belongsTo(Racikan::class, 'racikans_id');
    }

    public function notajual()
    {
        return $this->belongsTo(Notajual::class, 'notajuals_id');
    }
}
