<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profilapotek extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'profilapoteks';
    protected $fillable = [
        'nama',
        'alamat',
        'no_hp',
        'email',
        'logo',
        'deskripsi',
        'jam_operasional',
        'pemilik_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(user::class, 'pemilik_id');
    } 
}
