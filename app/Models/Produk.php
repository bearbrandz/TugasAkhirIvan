<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * The project originally included a number of unused fields such as
     * `kategori`, `satuan`, `buyprice`, `margin` and `image`. To align with
     * the functional requirements in the PDF specification we simplify
     * products down to their essentials:
     *
     * - `nama`: Name of the product.
     * - `kode_produk`: Optional SKU or unique code used on the shelves.
     * - `bentuk_sediaan`: Physical form of the medicine, e.g. tablet, sirup, kapsul.
     * - `golongan`: Legal classification of the medicine. Includes the
     *   categories bebas, terbatas, keras, narkotika and psikotropika.
     * - `stok_minimum`: Threshold quantity. When total stock falls below this
     *   number the product is considered critical and highlighted on the dashboard.
     * - `sellingprice`: Percentage margin above HPP Average. Kept for
     *   backwards‑compatibility with the existing pricing logic (20 => 20%).
     * - `deskripsi`: A text description or indication for the product.
     */
    protected $fillable = [
        'nama',
        'kode_produk',
        'bentuk_sediaan',
        'golongan',
        'stok_minimum',
        'sellingprice',
        'satuan_jual_id',
        'deskripsi'
    ];

    public function produkbatches(): HasMany
    {
        return $this->hasMany(Produkbatches::class, 'produks_id');
    }

    /**
     * Relasi khusus untuk membaca hpp_avg_per_unit dari semua batch aktif
     * (termasuk batch dengan stok=0). Digunakan untuk menampilkan HPP Moving Average
     * yang benar di halaman Daftar Produk.
     */
    public function allBatchesForHpp(): HasMany
    {
        return $this->hasMany(Produkbatches::class, 'produks_id');
    }

    public function satuanJual()
    {
        return $this->belongsTo(\App\Models\Satuan::class, 'satuan_jual_id');
    }

    public function racikanProduks(): HasMany
    {
        return $this->hasMany(Racikanproduk::class, 'produks_id');
    }

    public function produkOpnames(): HasMany
    {
        return $this->hasMany(Produkopnames::class, 'produks_id');
    }

    public static function generateKodeProduk($golongan)
    {
        $prefix = 'OBT-';
        if ($golongan === 'bmhp') $prefix = 'BHP-';
        elseif ($golongan === 'alkes') $prefix = 'ALK-';
        elseif ($golongan === 'pkrt') $prefix = 'PKR-';

        // Find the highest sequence number for this prefix
        $latest = static::where('kode_produk', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(kode_produk, 5) AS UNSIGNED) DESC')
            ->first();

        if ($latest && preg_match('/^' . $prefix . '(\d+)$/', $latest->kode_produk, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
