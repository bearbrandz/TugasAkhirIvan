<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Produk;
use App\Models\Produkbatches;

class Produkopnames extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'produkopnames';
    /**
     * The attributes that are mass assignable.
     *
     * The original implementation used a different set of column names
     * (e.g. stok_sys, stok_nyata, tgl_opname, deskripsi) which did not match the
     * schema defined in the SQL dump.  To align the model with the existing
     * database structure as defined in tugasakhirs.sql, we map the fillable
     * attributes to the real column names: stok_sistem, stok_fisik,
     * tanggal and keterangan.  We also store the user who performed the
     * opname via users_id so that audits and reports can reference the
     * originating account.
     */
    protected $fillable = [
        // The associated batch rather than just the product.  Stock
        // differences are recorded per batch so we can update the
        // underlying inventory accurately.  See revisions to the
        // controller for usage.
        'produkbatches_id',
        // Maintain the product id for backward compatibility with old
        // schema and relationships.  This field will be set
        // automatically based on the selected batch.
        'produks_id',
        'stok_sistem',    // jumlah stok menurut sistem sebelum opname
        'stok_fisik',     // stok fisik hasil perhitungan manual
        'selisih',        // selisih antara sistem dan fisik
        'tanggal',        // tanggal opname dilaksanakan
        'keterangan',     // catatan atau deskripsi tambahan
        'users_id',       // pengguna yang melakukan opname
    ];

    /**
     * Relasi ke produk.
     * Tetap dipakai untuk kompatibilitas controller/view lama.
     */
    public function produks(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produks_id');
    }

    /**
     * Relasi ke produk.  Setiap baris opname dimiliki oleh satu produk.
     */
    /**
     * Relasi ke batch yang diopname.  Setiap stok opname berhubungan
     * dengan satu batch sehingga stok sistem bisa dihitung dan
     * diperbarui dengan benar.  Melalui batch ini kita dapat
     * mengakses data produk.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Produkbatches::class, 'produkbatches_id');
    }

    /**
     * Dapatkan entitas produk melalui relasi batch.  Kita tidak
     * menggunakan relasi belongsTo secara langsung di sini karena
     * Produkopnames menyimpan foreign key ke batch, bukan ke produk.
     * Akseslah produk melalui properti batch: $opname->batch->produks.
     */
    public function getProdukAttribute()
    {
        return $this->batch ? $this->batch->produks : null;
    }

    /**
     * Relasi ke user yang melakukan opname.  Ini opsional karena field users_id
     * boleh null pada beberapa data lama.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
