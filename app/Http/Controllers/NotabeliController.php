<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\Gudang;
use App\Models\Notabeli;
use App\Models\Notabeliproduk;
use App\Models\Produk;
use App\Models\Produkbatches;
use App\Models\Satuan;
use App\Models\SatuanKonversi;
use App\Models\User;
use App\Models\LogActivity;
use App\Services\HppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotabeliController extends Controller
{
    private function resolveKonversiKeSatuanJual(Produk $produk, int $satuanBeliId, ?int $satuanKonversiId = null, ?int $konversiKeSatuanId = null): array
    {
        $satuanJualId = (int) ($produk->satuan_jual_id ?? 0);
    
        if ($satuanJualId <= 0) {
            throw new \Exception('Produk "' . $produk->nama . '" belum memiliki satuan stok/jual utama. Silakan edit produk dan pilih satuan jual terlebih dahulu.');
        }
    
        if ($satuanBeliId <= 0) {
            throw new \Exception('Satuan beli tidak valid.');
        }
    
        // Jika satuan beli sama dengan satuan jual, tidak perlu konversi.
        if ($satuanBeliId === $satuanJualId) {
            return [
                'ada_konversi' => false,
                'satuan_konversi_id' => null,
                'satuan_beli_id' => $satuanBeliId,
                'satuan_jual_id' => $satuanJualId,
                'jumlah_konversi' => 1,
            ];
        }
    
        /*
        |--------------------------------------------------------------------------
        | Jika satuan beli beda dari satuan jual, wajib ada konversi langsung:
        | satuan_beli -> satuan_jual
        |--------------------------------------------------------------------------
        */
    
        if (!empty($satuanKonversiId)) {
            $konversi = SatuanKonversi::find($satuanKonversiId);
    
            if (!$konversi) {
                throw new \Exception('Konversi satuan tidak ditemukan.');
            }
    
            if ((int) $konversi->satuan_besar_id !== $satuanBeliId) {
                throw new \Exception('Konversi satuan tidak sesuai dengan satuan beli yang dipilih.');
            }
    
            if ((int) $konversi->satuan_kecil_id !== $satuanJualId) {
                throw new \Exception('Konversi satuan harus menuju satuan stok/jual produk.');
            }
    
            if ((float) $konversi->nilai_konversi <= 0) {
                throw new \Exception('Nilai konversi satuan tidak valid.');
            }
    
            return [
                'ada_konversi' => true,
                'satuan_konversi_id' => (int) $konversi->id,
                'satuan_beli_id' => $satuanBeliId,
                'satuan_jual_id' => $satuanJualId,
                'jumlah_konversi' => (float) $konversi->nilai_konversi,
            ];
        }
    
        /*
        |--------------------------------------------------------------------------
        | Kompatibel dengan view lama yang mengirim konversi_ke_satuan_id.
        |--------------------------------------------------------------------------
        */
        if (!empty($konversiKeSatuanId)) {
            if ((int) $konversiKeSatuanId !== $satuanJualId) {
                throw new \Exception('Konversi yang dipilih tidak menuju satuan stok/jual produk.');
            }
    
            $konversi = SatuanKonversi::where('satuan_dari_id', $satuanBeliId)
                ->where('satuan_ke_id', $satuanJualId)
                ->first();
    
            if (!$konversi) {
                throw new \Exception('Konversi satuan dari satuan beli ke satuan jual produk belum dibuat.');
            }
    
            return [
                'ada_konversi' => true,
                'satuan_konversi_id' => (int) $konversi->id,
                'satuan_beli_id' => $satuanBeliId,
                'satuan_jual_id' => $satuanJualId,
                'jumlah_konversi' => (float) $konversi->nilai_konversi,
            ];
        }
    
        /*
        |--------------------------------------------------------------------------
        | Auto cari konversi langsung jika user belum memilih, tapi konversinya ada.
        |--------------------------------------------------------------------------
        */
        $konversi = SatuanKonversi::where('satuan_dari_id', $satuanBeliId)
            ->where('satuan_ke_id', $satuanJualId)
            ->first();
    
        if (!$konversi) {
            throw new \Exception('Satuan beli berbeda dengan satuan jual produk. Silakan buat/pilih konversi dari satuan beli ke satuan jual produk.');
        }
    
        return [
            'ada_konversi' => true,
            'satuan_konversi_id' => (int) $konversi->id,
            'satuan_beli_id' => $satuanBeliId,
            'satuan_jual_id' => $satuanJualId,
            'jumlah_konversi' => (float) $konversi->nilai_konversi,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Notabeliproduk::query()
            ->select(
                'notabelis_has_produks.*',
                'produks.id as produks_id',
                'produks.nama as nama_produk',
                'distributors.id as distributors_id',
                'distributors.nama as nama_distributor',
                'satuans.nama as nama_satuan',
                'users.nama as nama_pegawai'
            )
            ->join('notabelis', 'notabelis_has_produks.notabelis_id', '=', 'notabelis.id')
            ->join('users', 'notabelis.pegawai_id', '=', 'users.id')
            ->join('produkbatches', 'notabelis_has_produks.produkbatches_id', '=', 'produkbatches.id')
            ->join('produks', 'produkbatches.produks_id', '=', 'produks.id')
            ->join('distributors', 'produkbatches.distributors_id', '=', 'distributors.id')
            ->join('satuans', 'produkbatches.satuans_id', '=', 'satuans.id');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('produkbatches.id', 'LIKE', "%$search%")
                    ->orWhere('produks.nama', 'LIKE', "%$search%")
                    ->orWhere('distributors.nama', 'LIKE', "%$search%")
                    ->orWhere('satuans.nama', 'LIKE', "%$search%")
                    ->orWhere('users.nama', 'LIKE', "%$search%")
                    ->orWhere('notabelis_has_produks.quantity', 'LIKE', "%$search%")
                    ->orWhere('notabelis_has_produks.subtotal', 'LIKE', "%$search%")
                    ->orWhere('notabelis_has_produks.created_at', 'LIKE', "%$search%")
                    ->orWhere('notabelis_has_produks.updated_at', 'LIKE', "%$search%");
            });
        }

        $sortBy = $request->get('sort_by', 'notabelis_id');
        $sortOrder = $request->get('sort_order', 'desc');

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        switch ($sortBy) {
            case 'id_batch':
                $query->orderBy('produkbatches.id', $sortOrder);
                break;

            case 'nama_pegawai':
                $query->orderBy('users.nama', $sortOrder);
                break;

            case 'nama_produk':
                $query->orderBy('produks.nama', $sortOrder);
                break;

            case 'nama_dist':
                $query->orderBy('distributors.nama', $sortOrder);
                break;

            case 'satuan':
                $query->orderBy('satuans.nama', $sortOrder);
                break;

            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        $datas = $query->paginate(10)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ]);

        return view('transaksi.daftarPembelian', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search,
        ]);
    }

    public function report(Request $request)
    {
        $filter = $request->get('filter', 'day');

        $query = Notabeliproduk::with('notabeli', 'produkbatches.produks');

        switch ($filter) {
            case 'week':
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
                break;

            case 'year':
                $query->whereYear('created_at', now()->year);
                break;

            case 'day':
            default:
                $query->whereDate('created_at', now()->toDateString());
                break;
        }

        $purchases = $query->get();
        $total = $purchases->sum('subtotal');

        return view('transaksi.reportPembelian', compact('purchases', 'total', 'filter'));
    }

    public function reportCsv(Request $request)
    {
        $filter = $request->get('filter', 'day');

        $query = Notabeliproduk::with('notabeli', 'produkbatches.produks', 'produkbatches.satuan');

        switch ($filter) {
            case 'week':
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
                break;

            case 'year':
                $query->whereYear('created_at', now()->year);
                break;

            case 'day':
            default:
                $query->whereDate('created_at', now()->toDateString());
                break;
        }

        $purchases = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan_pembelian.csv"',
        ];

        $callback = function () use ($purchases) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Nota ID',
                'ID Produk',
                'Nama Produk',
                'Quantity',
                'Harga Satuan',
                'Subtotal',
            ], ',');

            foreach ($purchases as $purchase) {
                $satuan = $purchase->produkbatches?->satuan?->nama ?? $purchase->produkbatches?->satuans?->nama ?? '';
                fputcsv($file, [
                    $purchase->notabeli?->id ?? '-',
                    $purchase->produkbatches?->produks_id ?? '-',
                    $purchase->produkbatches?->produks?->nama ?? '-',
                    $purchase->quantity . ' ' . $satuan,
                    number_format($purchase->produkbatches?->unitprice ?? 0, 0, '.', ','),
                    number_format($purchase->subtotal ?? 0, 0, '.', ','),
                ], ',');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $search = $request->input('search');
        $cart = session('cart_beli', []);
    
        $distributors = Distributor::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();
        $gudangs = Gudang::orderBy('lokasi')->get();
        $notabelis = Notabeli::all();
        $users = User::all();
    
        $produksQuery = DB::table('produks')
            ->select(
                'produks.id',
                'produks.nama',
                'produks.golongan',
                'produks.deskripsi',
                'produks.sellingprice',
                'produks.satuan_jual_id',
                'sj.nama as nama_satuan_jual',
    
                DB::raw('COALESCE(SUM(
                    CASE
                        WHEN produkbatches.status = "tersedia"
                        AND produkbatches.stok > 0
                        THEN produkbatches.stok
                        ELSE 0
                    END
                ), 0) as total_stok'),
    
                DB::raw('COALESCE(MAX(NULLIF(produkbatches.hpp_avg_per_unit, 0)), MAX(NULLIF(produkbatches.unitprice, 0)), 0) as harga_beli_terakhir'),
    
                DB::raw('MAX(produkbatches.tgl_kadaluarsa) as kadaluarsa_terakhir')
            )
            ->leftJoin('produkbatches', function ($join) {
                $join->on('produks.id', '=', 'produkbatches.produks_id')
                    ->where(function ($q) {
                        $q->whereDate('produkbatches.tgl_kadaluarsa', '>', now())
                            ->orWhereNull('produkbatches.tgl_kadaluarsa');
                    });
    
                if (Schema::hasColumn('produkbatches', 'deleted_at')) {
                    $join->whereNull('produkbatches.deleted_at');
                }
            })
            ->leftJoin('satuans as sj', 'produks.satuan_jual_id', '=', 'sj.id');
    
        // Ini yang penting: sembunyikan produk lama / soft delete
        if (Schema::hasColumn('produks', 'deleted_at')) {
            $produksQuery->whereNull('produks.deleted_at');
        }
    
        // Kalau kolom is_active ada, hanya tampilkan produk aktif
        if (Schema::hasColumn('produks', 'is_active')) {
            $produksQuery->where('produks.is_active', 1);
        }
    
        if ($search) {
            $produksQuery->where(function ($q) use ($search) {
                $q->where('produks.nama', 'like', '%' . $search . '%')
                    ->orWhere('produks.golongan', 'like', '%' . $search . '%')
                    ->orWhere('produks.deskripsi', 'like', '%' . $search . '%');
            });
        }
    
        $produks = $produksQuery
            ->groupBy(
                'produks.id',
                'produks.nama',
                'produks.golongan',
                'produks.deskripsi',
                'produks.sellingprice',
                'produks.satuan_jual_id',
                'sj.nama'
            )
            ->orderBy('produks.nama', 'asc')
            ->paginate(9)
            ->appends($request->query());
    
        $satuanKonversis = SatuanKonversi::with([
            'satuanDari',
            'satuanKe',
        ])->get();
    
        return view('transaksi.beliProduk', [
            'distributors' => $distributors,
            'satuans' => $satuans,
            'gudangs' => $gudangs,
            'satuanKonversis' => $satuanKonversis,
            'data' => $notabelis,
            'prod' => $produks,
            'user' => $users,
            'search' => $search,
            'cart' => $cart,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required',
        ]);
    
        $cart = session('cart_beli', []);
    
        if (empty($cart)) {
            return redirect()->back()->withErrors('Keranjang kosong.');
        }
    
        DB::beginTransaction();
    
        try {
            $nota = Notabeli::create([
                'pegawai_id' => $request->pegawai_id,
            ]);
    
            foreach ($cart as $cartKey => $item) {
                $produkId = (int) ($item['id'] ?? 0);
    
                if ($produkId <= 0) {
                    throw new \Exception('Produk tidak valid pada keranjang.');
                }
    
                $produk = Produk::find($produkId);
    
                if (!$produk) {
                    throw new \Exception('Produk pada keranjang tidak ditemukan.');
                }
    
                $qtyInput = (float) ($item['quantity'] ?? 0);
                $hargaInput = (float) ($item['unitprice'] ?? 0);
                $jumlahKonversi = (float) ($item['jumlah_konversi'] ?? 1);
                $satuanBeliId = (int) ($item['satuans_id'] ?? 0);
    
                if ($qtyInput <= 0) {
                    throw new \Exception('Jumlah beli tidak boleh 0.');
                }
    
                if ($hargaInput <= 0) {
                    throw new \Exception('Harga beli tidak boleh 0.');
                }
    
                $konversi = $this->resolveKonversiKeSatuanJual(
                    $produk,
                    $satuanBeliId,
                    !empty($item['satuan_konversi_id']) ? (int) $item['satuan_konversi_id'] : null,
                    null
                );
    
                $jumlahKonversi = (float) $konversi['jumlah_konversi'];
    
                if ($jumlahKonversi <= 0) {
                    $jumlahKonversi = 1;
                }
    
                if ($konversi['ada_konversi']) {
                    $stokMasuk = $qtyInput * $jumlahKonversi;
                    $hargaPerSatuanJual = $hargaInput / $jumlahKonversi;
                } else {
                    $stokMasuk = $qtyInput;
                    $hargaPerSatuanJual = $hargaInput;
                }
    
                $hargaPerSatuanJual = round($hargaPerSatuanJual, 4);
                $satuanJualId = (int) $konversi['satuan_jual_id'];
    
                /*
                |--------------------------------------------------------------------------
                | Hitung HPP berdasarkan stok yang sudah dikonversi ke satuan jual.
                |--------------------------------------------------------------------------
                */
                $hppBaru = HppService::hitungUlang(
                    $produkId,
                    (int) $stokMasuk,
                    $hargaPerSatuanJual,
                    'pembelian',
                    $nota->id
                );
    
                if (isset($item['sellingprice'])) {
                    Produk::where('id', $produkId)->update([
                        'sellingprice' => (float) $item['sellingprice'],
                    ]);
                }
    
                /*
                |--------------------------------------------------------------------------
                | Cari batch yang sama.
                | Batch SELALU disimpan dalam satuan jual produk.
                |--------------------------------------------------------------------------
                */
                $batchQuery = Produkbatches::where('produks_id', $produkId)
                    ->where('distributors_id', $item['distributors_id'])
                    ->where('satuans_id', $satuanJualId)
                    ->where('gudangs_id', $item['gudangs_id'])
                    ->where('unitprice', $hargaPerSatuanJual);
    
                if (!empty($item['tgl_kadaluarsa'])) {
                    $batchQuery->whereDate('tgl_kadaluarsa', $item['tgl_kadaluarsa']);
                } else {
                    $batchQuery->whereNull('tgl_kadaluarsa');
                }
    
                $batch = $batchQuery->first();
    
                if ($batch) {
                    $batch->update([
                        'stok'             => $batch->stok + $stokMasuk,
                        'unitprice'        => $hargaPerSatuanJual,
                        'hpp_avg_per_unit' => $hppBaru,   // update HPP avg ke nilai terbaru
                        'status'           => 'tersedia',
                    ]);
                } else {
                    $batch = Produkbatches::create([
                        'produks_id' => $produkId,
                        'stok' => $stokMasuk,
                        'unitprice' => $hargaPerSatuanJual,
                        'hpp_avg_per_unit' => $hppBaru,
                        'distributors_id' => $item['distributors_id'],
                        'tgl_kadaluarsa' => $item['tgl_kadaluarsa'] ?? null,
                        'tgl_produksi' => $item['tgl_produksi'] ?? null,
                        'tgl_datang' => now(),
                        'status' => 'tersedia',
                        'satuans_id' => $satuanJualId,
                        'gudangs_id' => $item['gudangs_id'],
                    ]);
                }
    
                /*
                |--------------------------------------------------------------------------
                | Detail nota pembelian disimpan dalam satuan jual/stok produk.
                |--------------------------------------------------------------------------
                */
                Notabeliproduk::create([
                    'notabelis_id' => $nota->id,
                    'produkbatches_id' => $batch->id,
                    'quantity' => $stokMasuk,
                    'subtotal' => $stokMasuk * $hargaPerSatuanJual,
                ]);
    
                HppService::updateBatchHpp($produkId, $hppBaru);
            }
    
            DB::commit();

            session()->forget('cart_beli');

            // Catat log aktivitas pembelian (di luar transaksi DB agar tidak rollback)
            try {
                $pegawaiIdInt = (int) $request->pegawai_id;
                $namaPegawai = \App\Models\User::find($pegawaiIdInt)?->nama
                    ?? auth()->user()?->nama
                    ?? 'Unknown';
                LogActivity::catat(
                    'pembelian_baru',
                    'Transaksi Pembelian',
                    'Nota pembelian #' . $nota->id . ' berhasil disimpan oleh ' . $namaPegawai . '.',
                    $pegawaiIdInt ?: null
                );
            } catch (\Throwable $logErr) {
                // Log gagal tidak boleh mengganggu proses utama
                \Illuminate\Support\Facades\Log::warning('LogActivity pembelian gagal: ' . $logErr->getMessage());
            }

            return redirect()
                ->route('notabelis.print', $nota->id)
                ->with('status', 'Pembelian tercatat dan HPP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors('Gagal menyimpan pembelian: ' . $e->getMessage());
        }
    }

    public function beliProdukBaru(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:users,id',
            'nama' => 'required|string|max:255',
            'golongan' => 'required|string',
            'deskripsi' => 'nullable|string',
            'stok' => 'required|numeric|min:1',
            'unitprice' => 'required|numeric|min:0.01',
            'sellingprice' => 'required|numeric|min:0',
            'satuans' => 'required|exists:satuans,id',
            'satuan_jual_id' => 'nullable|exists:satuans,id',
            'satuan_konversi_id' => 'nullable|exists:satuan_konversi,id',
            'distributors' => 'required|exists:distributors,id',
            'gudangs' => 'required|exists:gudangs,id',
            'tgl_kadaluarsa' => 'nullable|date',
            'tgl_produksi' => 'nullable|date',
        ]);
    
        DB::beginTransaction();
    
        try {
            /*
            |--------------------------------------------------------------------------
            | Untuk produk baru:
            | - satuans = satuan beli
            | - satuan_jual_id = satuan stok/jual utama
            | Jika view belum punya satuan_jual_id, default-nya sama dengan satuan beli.
            |--------------------------------------------------------------------------
            */
            $satuanBeliId = (int) $request->satuans;
            $satuanJualId = (int) ($request->satuan_jual_id ?: $request->satuans);
    
            $defaultMin = \App\Models\Produk::getDefaultStokMinimum($request->golongan);
    
            $produk = Produk::create([
                'nama' => $request->nama,
                'kode_produk' => Produk::generateKodeProduk($request->golongan),
                'golongan' => $request->golongan,
                'deskripsi' => $request->deskripsi,
                'sellingprice' => $request->sellingprice,
                'satuan_jual_id' => $satuanJualId,
                'stok_minimum' => $defaultMin,
            ]);
    
            if (Schema::hasColumn('produks', 'is_active')) {
                $produk->forceFill([
                    'is_active' => 1,
                ])->save();
            }
    
            $konversi = $this->resolveKonversiKeSatuanJual(
                $produk,
                $satuanBeliId,
                $request->input('satuan_konversi_id') ? (int) $request->input('satuan_konversi_id') : null,
                null
            );
    
            $qtyInput = (float) $request->stok;
            $hargaInput = (float) $request->unitprice;
            $jumlahKonversi = (float) $konversi['jumlah_konversi'];
    
            if ($konversi['ada_konversi']) {
                $stokMasuk = $qtyInput * $jumlahKonversi;
                $hargaPerSatuanJual = $hargaInput / $jumlahKonversi;
            } else {
                $stokMasuk = $qtyInput;
                $hargaPerSatuanJual = $hargaInput;
            }
    
            $hargaPerSatuanJual = round($hargaPerSatuanJual, 4);
    
            $nota = Notabeli::create([
                'pegawai_id' => $request->pegawai_id,
            ]);
    
            $batch = Produkbatches::create([
                'produks_id' => $produk->id,
                'distributors_id' => $request->distributors,
                'satuans_id' => $satuanJualId,
                'gudangs_id' => $request->gudangs,
                'stok' => $stokMasuk,
                'unitprice' => $hargaPerSatuanJual,
                'hpp_avg_per_unit' => $hargaPerSatuanJual,
                'tgl_datang' => now()->toDateString(),
                'tgl_produksi' => $request->tgl_produksi,
                'tgl_kadaluarsa' => $request->tgl_kadaluarsa,
                'status' => 'tersedia',
            ]);
    
            Notabeliproduk::create([
                'notabelis_id' => $nota->id,
                'produkbatches_id' => $batch->id,
                'quantity' => $stokMasuk,
                'subtotal' => $stokMasuk * $hargaPerSatuanJual,
            ]);
    
            HppService::hitungUlang(
                $produk->id,
                (int) $stokMasuk,
                $hargaPerSatuanJual,
                'pembelian',
                $nota->id
            );
    
            DB::commit();

            // Catat log aktivitas pembelian produk baru
            try {
                $pegawaiIdInt = (int) $request->pegawai_id;
                $namaPegawai = \App\Models\User::find($pegawaiIdInt)?->nama
                    ?? auth()->user()?->nama
                    ?? 'Unknown';
                LogActivity::catat(
                    'pembelian_baru',
                    'Transaksi Pembelian',
                    'Nota pembelian #' . $nota->id . ' (Produk Baru: ' . $produk->nama . ') berhasil disimpan oleh ' . $namaPegawai . '.',
                    $pegawaiIdInt ?: null
                );
            } catch (\Throwable $logErr) {
                \Illuminate\Support\Facades\Log::warning('LogActivity pembelian produk baru gagal: ' . $logErr->getMessage());
            }

            return redirect()
                ->route('notabelis.print', $nota->id)
                ->with('success', 'Produk baru berhasil dibeli dan masuk ke Nota Penerimaan.');
        } catch (\Throwable $e) {
            DB::rollBack();
    
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pembelian produk baru: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deletedData = Notabeli::findOrFail($id);

            $deletedData->notaBeliProduks()->delete();
            $deletedData->delete();

            return redirect('notabelis')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            $msg = 'Failed to delete data ! Make sure there is no related data before deleting it';

            return redirect('notabelis')->with('status', $msg);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $produkId = $request->input('id') ?? $request->input('produk_id');
            $quantity = (float) ($request->input('quantity') ?? $request->input('stok') ?? 0);
            $unitprice = (float) ($request->input('unitprice') ?? 0);
    
            if (empty($produkId)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Produk tidak valid.');
            }
    
            if ($quantity <= 0) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Jumlah beli tidak boleh 0.');
            }
    
            if ($unitprice <= 0) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Harga beli per unit harus lebih dari 0.');
            }
    
            $produk = Produk::with('satuanJual')->find($produkId);
    
            if (!$produk) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Produk tidak ditemukan.');
            }
    
            if (Schema::hasColumn('produks', 'is_active') && (int) ($produk->is_active ?? 1) !== 1) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Produk ini sudah dinonaktifkan dan tidak bisa dibeli lagi.');
            }
    
            $distributorId = $request->input('distributors_id');
            $satuanBeliId = (int) $request->input('satuans_id');
            $gudangId = $request->input('gudangs_id');
    
            if (empty($distributorId)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Distributor wajib dipilih.');
            }
    
            if (empty($satuanBeliId)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Satuan beli wajib dipilih.');
            }
    
            if (empty($gudangId)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Gudang wajib dipilih.');
            }
    
            $konversi = $this->resolveKonversiKeSatuanJual(
                $produk,
                $satuanBeliId,
                $request->input('satuan_konversi_id') ? (int) $request->input('satuan_konversi_id') : null,
                $request->input('konversi_ke_satuan_id') ? (int) $request->input('konversi_ke_satuan_id') : null
            );
    
            $satuanBeli = Satuan::find($satuanBeliId);
            $satuanJual = Satuan::find($konversi['satuan_jual_id']);
    
            $cart = session()->get('cart_beli', []);
    
            $key = 'produk_' . $produkId . '_' . now()->format('YmdHis') . '_' . rand(100, 999);
    
            $cart[$key] = [
                'id' => (int) $produkId,
                'nama' => $request->input('nama') ?: $produk->nama,
    
                // Data input pembelian asli
                'quantity' => $quantity,
                'unitprice' => $unitprice,
                'satuans_id' => $satuanBeliId,
                'nama_satuan_beli' => $satuanBeli->nama ?? '-',
    
                // Data satuan stok/jual produk
                'satuan_jual_id' => $konversi['satuan_jual_id'],
                'nama_satuan_jual' => $satuanJual->nama ?? '-',
    
                // Data konversi
                'satuan_konversi_id' => $konversi['satuan_konversi_id'],
                'jumlah_konversi' => $konversi['jumlah_konversi'],
    
                'sellingprice' => (float) ($request->input('sellingprice') ?? $produk->sellingprice ?? 0),
                'tgl_produksi' => $request->input('tgl_produksi'),
                'tgl_kadaluarsa' => $request->input('tgl_kadaluarsa'),
                'distributors_id' => $distributorId,
                'gudangs_id' => $gudangId,
            ];
    
            session()->put('cart_beli', $cart);
    
            return redirect()
                ->route('notabelis.create')
                ->with('status', 'Produk berhasil ditambahkan ke keranjang pembelian.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk ke keranjang: ' . $e->getMessage());
        }
    }

    public function deleteFromCart($id)
    {
        if (auth()->user()->tipe_user !== 'admin') {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak memiliki hak untuk menghapus item dari keranjang pembelian.');
        }

        $cart = session()->get('cart_beli', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart_beli', $cart);
        }

        return redirect()
            ->back()
            ->with('status', 'Produk telah dibuang dari keranjang');
    }

    public function print($id)
    {
        $nota = DB::table('notabelis')
            ->leftJoin('users', 'notabelis.pegawai_id', '=', 'users.id')
            ->where('notabelis.id', $id)
            ->select(
                'notabelis.*',
                'users.nama as nama_pegawai'
            )
            ->first();

        if (!$nota) {
            abort(404, 'Nota pembelian tidak ditemukan.');
        }

        $items = DB::table('notabelis_has_produks as nbp')
            ->join('produkbatches as pb', 'nbp.produkbatches_id', '=', 'pb.id')
            ->join('produks as p', 'pb.produks_id', '=', 'p.id')
            ->leftJoin('distributors as d', 'pb.distributors_id', '=', 'd.id')
            ->leftJoin('satuans as s', 'pb.satuans_id', '=', 's.id')
            ->where('nbp.notabelis_id', $id)
            ->select(
                'nbp.notabelis_id',
                'nbp.produkbatches_id',
                'nbp.quantity',
                'nbp.subtotal',
                'pb.id as batch_id',
                'pb.produks_id',
                'pb.unitprice',
                'pb.tgl_produksi',
                'pb.tgl_kadaluarsa',
                'p.nama as nama_produk',
                'd.nama as nama_distributor',
                's.nama as nama_satuan'
            );

        if (Schema::hasColumn('notabelis_has_produks', 'deleted_at')) {
            $items->whereNull('nbp.deleted_at');
        }

        $items = $items->get();

        $total = $items->sum('subtotal');

        return view('transaksi.nbPrint', [
            'nota' => $nota,
            'items' => $items,
            'total' => $total,
        ]);
    }
}