<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    protected $table = 'log_activities';

    protected $fillable = [
        'users_id',
        'nama_user',
        'tipe_user',
        'aksi',
        'modul',
        'deskripsi',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Helper statis untuk mencatat aktivitas dari controller mana saja.
     * @param int|null $asUserId Jika diisi, gunakan user ini sebagai pelaku (override auth user)
     */
    public static function catat(string $aksi, string $modul, string $deskripsi = '', ?int $asUserId = null): void
    {
        if ($asUserId) {
            $user = self::getUser($asUserId);
        } else {
            $user = auth()->user();
        }

        self::create([
            'users_id'   => $user?->id,
            'nama_user'  => $user?->nama ?? 'System',
            'tipe_user'  => $user?->tipe_user ?? '-',
            'aksi'       => $aksi,
            'modul'      => $modul,
            'deskripsi'  => $deskripsi,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Helper untuk mengambil user dari DB (digunakan saat auth() tidak tersedia).
     */
    private static function getUser(?int $userId): ?\App\Models\User
    {
        if (!$userId) return null;
        return \App\Models\User::find($userId);
    }
}
