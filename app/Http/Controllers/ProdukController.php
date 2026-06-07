<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\Gudang;
use App\Models\Satuan;
use App\Models\Produk;
use App\Models\Produkbatches;
use App\Models\Terimabatches;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\LogActivity;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produks = $this->getFilteredProduk($request);
        // dd($produks);
        return view('produk.index', [
            'datas' => $produks,
            'sortBy' => $request->get('sort_by', 'nama'),
            'sortOrder' => $request->get('sort_order', 'asc'),
            'search' => $request->get('search')
        ]);
    }

    public function arsip(Request $request)
    {
        $search = $request->get('search');
        
        $query = Produk::onlyTrashed();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%$search%")
                    ->orWhere('golongan', 'LIKE', "%$search%")
                    ->orWhere('deskripsi', 'LIKE', "%$search%");
            });
        }

        $datas = $query->orderBy('deleted_at', 'desc')->paginate(12)->appends(['search' => $search]);

        return view('produk.arsip', [
            'datas' => $datas,
            'search' => $search
        ]);
    }

    public function restore(Request $request, $id)
    {
        try {
            $produk = Produk::onlyTrashed()->findOrFail($id);
            $produk->restore();

            LogActivity::catat(
                'restore_produk',
                'Master Produk',
                'Berhasil memulihkan produk ' . $produk->nama . ' dari arsip.'
            );

            return redirect()->route('produks.arsip')->with('status', 'Produk ' . $produk->nama . ' berhasil dikembalikan ke daftar aktif!');
        } catch (\Exception $e) {
            return redirect()->route('produks.arsip')->withErrors('Gagal mengembalikan produk: ' . $e->getMessage());
        }
    }

    public function batch(Request $request)
    {
        $id = $request->id;
        $data = Produk::findOrFail($id);

        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $search = $request->get('search');

        $query = Produkbatches::with(['produks', 'satuan', 'distributor', 'gudang', 'notaBeliProduks', 'terimaBatches'])
            ->where('produks_id', $id);
        // ->where('status', 'tersedia')
        // ->whereDate('tgl_kadaluarsa', '>', now());

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('stok', 'LIKE', "%$search%")
                    ->orWhere('unitprice', 'LIKE', "%$search%")
                    ->orWhere('status', 'LIKE', "%$search%")
                    ->orWhere('tgl_kadaluarsa', 'LIKE', "%$search%")
                    ->orWhere('tgl_produksi', 'LIKE', "%$search%")
                    ->orWhere('tgl_datang', 'LIKE', "%$search%")
                    ->orWhere('created_at', 'LIKE', "%$search%")
                    ->orWhere('updated_at', 'LIKE', "%$search%")
                    ->orWhereHas('satuan', fn($q) => $q->where('nama', 'LIKE', "%$search%"))
                    ->orWhereHas('distributor', fn($q) => $q->where('nama', 'LIKE', "%$search%"))
                    ->orWhereHas('gudang', fn($q) => $q->where('lokasi', 'LIKE', "%$search%"));
            });
        }

        if (in_array($sortBy, ['stok', 'unitprice', 'tgl_kadaluarsa', 'tgl_produksi', 'tgl_datang', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $produks = $query->paginate(8);

        $expiredBatches = $produks->filter(function ($batch) {
            return $batch->tgl_kadaluarsa <= now() && $batch->tgl_kadaluarsa !== null && $batch->status === 'tersedia' && $batch->stok > 0;
        });

        $expiredBatchList = null;
        if ($expiredBatches->isNotEmpty()) {
            $expiredBatchList = $expiredBatches->map(function ($b) {
                return "Batch ID: {$b->id} telah kadaluarsa, harap segera ganti status batch!";
            })->implode('\n');
        }

        $sixmonthexpiredBatches = $produks->filter(function ($batch) {
            return $batch->tgl_kadaluarsa >= now() && $batch->tgl_kadaluarsa <= Carbon::now()->addMonths(6) && $batch->tgl_kadaluarsa !== null && $batch->status === 'tersedia' && $batch->stok > 0;
        });

        $sixmonthexpiredBatchesList = null;
        if ($sixmonthexpiredBatches->isNotEmpty()) {
            $sixmonthexpiredBatchesList = $sixmonthexpiredBatches->map(function ($b) {
                return "Batch ID: {$b->id} akan kadaluarsa dalam 6 bulan, harap periksa batch!";
            })->implode('\n');
        }

        return view('produk.batch', [
            'datas' => $produks,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search,
            'produk' => $data,
            'expired_batches' => $expiredBatchList,
            'sixmonthsexpired_batches' => $sixmonthexpiredBatchesList
        ]);
    }

    private function getFilteredProduk(Request $request)
    {
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $search = $request->get('search');



        // Load produk with summed stok from batches
        $query = Produk::with('satuanJual')
        ->withSum(
            ['produkbatches as total_stok' => function ($q) {
                $q->where('status', 'tersedia')
                    ->where(function ($sub) {
                        $sub->whereDate('tgl_kadaluarsa', '>', now())
                            ->orWhereNull('tgl_kadaluarsa');
                    });
            }],
            'stok'
        )
        // Batch aktif untuk kalkulasi stok & WAC fallback
        ->with(['produkbatches' => function ($q) {
            $q->where('status', 'tersedia')
                ->where(function ($sub) {
                    $sub->whereDate('tgl_kadaluarsa', '>', now())
                        ->orWhereNull('tgl_kadaluarsa');
                });
        }])
        // Semua batch (termasuk stok=0) untuk membaca hpp_avg_per_unit Moving Average
        ->with(['allBatchesForHpp' => function ($q) {
            $q->where('status', 'tersedia')
                ->where(function ($sub) {
                    $sub->whereDate('tgl_kadaluarsa', '>', now())
                        ->orWhereNull('tgl_kadaluarsa');
                })
                ->select('id', 'produks_id', 'hpp_avg_per_unit');
        }])
        ->withAvg(
            ['produkbatches as avg_unitprice' => function ($q) {
                $q->where('status', 'tersedia')
                    ->where(function ($sub) {
                        $sub->whereDate('tgl_kadaluarsa', '>', now())
                            ->orWhereNull('tgl_kadaluarsa');
                    });
            }],
            'unitprice'
        );


        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%$search%")
                    ->orWhere('golongan', 'LIKE', "%$search%")
                    ->orWhere('deskripsi', 'LIKE', "%$search%")
                    ->orWhere('sellingprice', 'LIKE', "%$search%")
                    ->orWhereHas('produkbatches', function ($qb) use ($search) {
                        $qb->where('status', 'tersedia')
                            ->whereDate('tgl_kadaluarsa', '>', now())
                            ->where(function ($sub) use ($search) {
                                $sub->where('stok', 'LIKE', "%$search%")
                                    ->orWhere('unitprice', 'LIKE', "%$search%")
                                    ->orWhere('tgl_kadaluarsa', 'LIKE', "%$search%")
                                    ->orWhere('tgl_datang', 'LIKE', "%$search%")
                                    ->orWhere('created_at', 'LIKE', "%$search%")
                                    ->orWhere('updated_at', 'LIKE', "%$search%")
                                    ->orWhereHas('satuan', fn($q) => $q->where('nama', 'LIKE', "%$search%"))
                                    ->orWhereHas('distributor', fn($q) => $q->where('nama', 'LIKE', "%$search%"))
                                    ->orWhereHas('gudang', fn($q) => $q->where('lokasi', 'LIKE', "%$search%"));
                            });
                    });
            });
        }

        // Allow sorting only on selected fields
        if (in_array($sortBy, ['nama', 'sellingprice', 'golongan', 'deskripsi', 'total_stok'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // return $query->paginate(6)->appends([
        //     'search' => $search,
        //     'sort_by' => $sortBy,
        //     'sort_order' => $sortOrder,
        // ]);

        $produks = $query->paginate(12)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ]);

        // Calculate HPP Average + Final Price
        $produks->getCollection()->transform(function ($produk) {

            /*
            |--------------------------------------------------------------------------
            | TOTAL STOCK
            |--------------------------------------------------------------------------
            */
            $totalStock = (int) ($produk->total_stok ?? 0);

            /*
            |--------------------------------------------------------------------------
            | HPP AVERAGE (Moving Average / Weighted Average)
            | Prioritas:
            |   1. Ambil hpp_avg_per_unit dari batch aktif (sudah diisi oleh HppService)
            |      hpp_avg_per_unit nilainya sama di semua batch (di-sync oleh updateBatchHpp)
            |   2. Fallback: hitung WAC dari unitprice × stok jika hpp_avg_per_unit tidak ada
            |--------------------------------------------------------------------------
            */

            // Cari hpp_avg_per_unit pertama yang valid dari SEMUA batch aktif (termasuk stok=0)
            // Gunakan allBatchesForHpp yang tidak filter stok>0
            $hppAvgPerUnit = ($produk->allBatchesForHpp ?? collect())
                ->first(fn($b) => (float) ($b->hpp_avg_per_unit ?? 0) > 0)
                ?->hpp_avg_per_unit;

            // Fallback ke relasi produkbatches biasa jika allBatchesForHpp kosong
            if (!$hppAvgPerUnit) {
                $hppAvgPerUnit = $produk->produkbatches
                    ->first(fn($b) => (float) ($b->hpp_avg_per_unit ?? 0) > 0)
                    ?->hpp_avg_per_unit;
            }

            if ($hppAvgPerUnit && (float) $hppAvgPerUnit > 0) {
                // Gunakan Moving Average yang tersimpan di hpp_avg_per_unit
                $basePrice = (float) $hppAvgPerUnit;
            } else {
                // Fallback: hitung WAC dari unitprice × stok (batch dengan stok > 0)
                $totalCost = $produk->produkbatches->sum(function ($batch) {
                    return ((float) $batch->unitprice) * ((int) $batch->stok);
                });
                $basePrice = $totalStock > 0
                    ? ($totalCost / $totalStock)
                    : ((float) ($produk->avg_unitprice ?? 0));
            }

            /*
            |--------------------------------------------------------------------------
            | FORMAT DATA
            |--------------------------------------------------------------------------
            */
            $produk->base_price = round($basePrice);

            $produk->final_price = round(
                $basePrice * (1 + ((float) $produk->sellingprice / 100)),
                0
            );

            return $produk;
        });

        return $produks;
    }

    public function welcomeProduk(Request $request)
    {
        $produks = $this->getFilteredProduk($request);

        return view('welcome', [
            'datas' => $produks,
            'sortBy' => $request->get('sort_by', 'nama'),
            'sortOrder' => $request->get('sort_order', 'asc'),
            'search' => $request->get('search')
        ]);
    }

    private function getExpiredBatchNotifications()
    {
        // Gunakan Eloquent with('produks') + whereHas agar hanya batch dengan produk
        // yang masih aktif (belum soft-deleted) yang diambil, mencegah ->produks->nama null.
        $batches = Produkbatches::with('produks')
            ->whereHas('produks')          // filter out batch yg produknya sudah soft-delete
            ->where('status', 'tersedia')
            ->where('stok', '>', 0)
            ->whereNotNull('tgl_kadaluarsa')
            ->get();

        $produks = Produk::withSum(
            ['produkbatches as total_stok' => function ($q) {
                $q->where('status', 'tersedia')
                    ->where(function ($sub) {
                        $sub->whereDate('tgl_kadaluarsa', '>', now())
                            ->orWhereNull('tgl_kadaluarsa');
                    });
            }],
            'stok'
        )->get();

        $expiredBatches = $batches->filter(function ($batch) {
            return \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->startOfDay() <= now()->startOfDay();
        });

        $criticalQtyProducts = $produks->map(function ($produk) {
            $totalStok = $produk->total_stok ?? 0;

            return [
                'nama'        => $produk->nama,
                'total_stok'  => $totalStok,
                'is_critical' => $totalStok < 10,
            ];
        });

        $sixMonthBatches = $batches->filter(function ($batch) {
            $tgl = \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->startOfDay();
            return $tgl > now()->startOfDay() &&
                $tgl <= now()->addMonths(6)->endOfDay();
        });

        $expiredBatchList = $expiredBatches->map(function ($b) {
            $namaProduk = optional($b->produks)->nama ?? 'Produk tidak ditemukan';
            return "Produk: {$namaProduk} telah kadaluarsa! (Batch ID: {$b->id})";
        });

        $criticalQtyBatchesList = $criticalQtyProducts
            ->filter(fn($p) => $p['is_critical'])
            ->map(fn($p) => "Produk: {$p['nama']} stok kurang dari 10! (Total Stok: {$p['total_stok']})");

        $sixMonthBatchList = $sixMonthBatches->map(function ($b) {
            $namaProduk = optional($b->produks)->nama ?? 'Produk tidak ditemukan';
            return "Produk: {$namaProduk} akan kadaluarsa dalam 6 bulan! (Batch ID: {$b->id})";
        });

        return [
            'expired'      => $expiredBatchList,
            'stockcritical' => $criticalQtyBatchesList,
            'sixmonths'    => $sixMonthBatchList,
        ];
    }

    public function homeProduk(Request $request)
    {
        if (auth()->check() && auth()->user()->tipe_user === 'kasir') {
            return redirect('notajuals/create');
        }

        $produks = $this->getFilteredProduk($request);

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $batchNotifications = $this->getExpiredBatchNotifications();

        // Sales data
        $salesData = DB::table('notajuals_has_produks')
            ->join('produkbatches', 'produkbatches.id', '=', 'notajuals_has_produks.produkbatches_id')
            ->join('notajuals', 'notajuals.id', '=', 'notajuals_has_produks.notajuals_id')
            ->whereBetween('notajuals.created_at', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw("WEEK(notajuals.created_at, 1) - WEEK('$startOfMonth', 1) + 1 as week_number"),
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_rupiah')
            )
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->get();

        $chartLabelsSales = $salesData->pluck('week_number')->map(fn($w) => 'Minggu ' . $w);
        $chartDataSales = $salesData->pluck('total_qty');
        $totalSalesRupiah = $salesData->sum('total_rupiah');

        // Purchase data (fixed typo on notabelis)
        $purchasesData = DB::table('notabelis_has_produks')
            ->join('produkbatches', 'produkbatches.id', '=', 'notabelis_has_produks.produkbatches_id')
            ->join('notabelis', 'notabelis.id', '=', 'notabelis_has_produks.notabelis_id')
            ->whereBetween('notabelis.created_at', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw("WEEK(notabelis.created_at, 1) - WEEK('$startOfMonth', 1) + 1 as week_number"),
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_rupiah')
            )
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->get();

        $chartLabelsPurchases = $purchasesData->pluck('week_number')->map(fn($w) => 'Minggu ' . $w);
        $chartDataPurchases = $purchasesData->pluck('total_qty');
        
        $totalReturnsRupiah = DB::table('retur_pembelians')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_retur');

        $totalPurchasesRupiah = $purchasesData->sum('total_rupiah') - $totalReturnsRupiah;

        return view('home', [
            'datas' => $produks,
            'sortBy' => $request->get('sort_by', 'nama'),
            'sortOrder' => $request->get('sort_order', 'asc'),
            'search' => $request->get('search'),
            'chartLabelsSales' => $chartLabelsSales,
            'chartDataSales' => $chartDataSales,
            'totalSalesRupiah' => $totalSalesRupiah,
            'chartLabelsPurchases' => $chartLabelsPurchases,
            'chartDataPurchases' => $chartDataPurchases,
            'totalPurchasesRupiah' => $totalPurchasesRupiah,
            'expired_batches' => $batchNotifications['expired'],
            'cirital_stocks' => $batchNotifications['stockcritical'],
            'sixmonthsexpired_batches' => $batchNotifications['sixmonths']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produks = Produk::all();
        return view('produk.create', ['produks' => $produks]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request. Nama, sellingprice, golongan and deskripsi
        // remain mandatory. Kode produk, bentuk sediaan and stok minimum are optional
        // but will be persisted if supplied.
        $request->validate([
            'nama'           => 'required',
            'sellingprice'   => 'required|numeric',
            'golongan'       => 'required',
            'deskripsi'      => 'required',
            'kode_produk'    => 'nullable|string',
            'bentuk_sediaan' => 'nullable|string',
            'stok_minimum'   => 'nullable|integer',
        ]);

        $produk = new Produk();
        $produk->nama           = $request->nama;
        $produk->kode_produk    = $request->kode_produk;
        $produk->bentuk_sediaan = $request->bentuk_sediaan;
        $produk->golongan       = $request->golongan;
        // Stock minimum uses a default of 0 when not provided
        $produk->stok_minimum   = $request->stok_minimum ?? 0;
        // sellingprice acts as a margin percentage (e.g. 20 for 20%)
        $produk->sellingprice   = $request->sellingprice;
        $produk->deskripsi      = $request->deskripsi;
        $produk->save();

        if (empty($produk->kode_produk)) {
            $prefix = 'OBT-';
            if ($produk->golongan === 'bmhp') $prefix = 'BHP-';
            elseif ($produk->golongan === 'alkes') $prefix = 'ALK-';
            elseif ($produk->golongan === 'pkrt') $prefix = 'PKR-';

            $produk->kode_produk = $prefix . str_pad($produk->id, 4, '0', STR_PAD_LEFT);
            $produk->save();
        }

        return redirect('produks')->with('status', 'Produk baru berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $produk = Produk::findOrFail($id);

        // Get the latest available batch for this product
        $latestBatch = Produkbatches::where('produks_id', $id)
            ->where('status', 'tersedia')
            ->where(function ($q) {
                $q->whereDate('tgl_kadaluarsa', '>', now())
                    ->orWhereNull('tgl_kadaluarsa');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        // Sum of all valid available stock
        $stok = Produkbatches::where('produks_id', $id)
            ->where('status', 'tersedia')
            ->where(function ($q) {
                $q->whereDate('tgl_kadaluarsa', '>', now())
                    ->orWhereNull('tgl_kadaluarsa');
            })
            ->sum('stok');

        // Get satuan name or fallback
        $satuan = $latestBatch && $latestBatch->satuan ? $latestBatch->satuan->nama : 'Tidak tersedia';

        return view('pdetail', compact('produk', 'stok', 'satuan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Produk::findOrFail($id);
        $satuans = Satuan::orderBy('nama')->get();
    
        return view('produk.edit', [
            'datas' => $data,
            'satuans' => $satuans,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = Produk::findOrFail($id);

        // Validate updated data; allow nullable fields for optional inputs
        $request->validate([
            'nama'           => 'required',
            'sellingprice'   => 'required|numeric',
            'golongan'       => 'required',
            'deskripsi'      => 'required',
            'kode_produk'    => 'nullable|string',
            'bentuk_sediaan' => 'nullable|string',
            'stok_minimum'   => 'nullable|integer',
            'satuan_jual_id' => 'required|exists:satuans,id',
        ]);

        $data->nama           = $request->get('nama');
        $data->kode_produk    = $request->get('kode_produk');
        $data->bentuk_sediaan = $request->get('bentuk_sediaan');
        $data->golongan       = $request->get('golongan');
        $data->stok_minimum   = $request->get('stok_minimum', 0);
        $data->sellingprice   = $request->get('sellingprice');
        $data->deskripsi      = $request->get('deskripsi');
        $data->satuan_jual_id = $request->satuan_jual_id;
        $data->save();

        if (empty($data->kode_produk)) {
            $prefix = 'OBT-';
            if ($data->golongan === 'bmhp') $prefix = 'BHP-';
            elseif ($data->golongan === 'alkes') $prefix = 'ALK-';
            elseif ($data->golongan === 'pkrt') $prefix = 'PKR-';

            $data->kode_produk = $prefix . str_pad($data->id, 4, '0', STR_PAD_LEFT);
            $data->save();
        }

        return redirect()
        ->route('produks.index')
        ->with('status', 'Produk berhasil diperbarui.');
    }

    public function terimaBatch($id)
    {
        $data = Produkbatches::with(['notaBeliProduks', 'terimabatches'])->findOrFail($id);
        $users = User::all();
        $gudangs = Gudang::all();

        $qtyOrdered = $data->notaBeliProduks->sum('quantity');
        $qtyReceived = $data->terimaBatches->sum('stok');
        $qtyRemaining = $qtyOrdered - $qtyReceived;

        return view('produk.terimaBatch', [
            'datas' => $data,
            'gudangs' => $gudangs,
            'user' => $users,
            'qtyOrdered' => $qtyOrdered,
            'qtyReceived' => $qtyReceived,
            'qtyRemaining' => $qtyRemaining,
        ]);
    }

    public function updateTerimaBatch(Request $request, $id)
    {
        $batch = Produkbatches::with(['terimaBatches', 'notaBeliProduks'])->findOrFail($id);

        $stokBaru = (int) $request->get('stok');
        $newGudangId = $request->get('gudangs');

        $qtyOrdered = $batch->notaBeliProduks->sum('quantity');
        $qtyReceived = $batch->terimaBatches->sum('stok');
        $qtyRemaining = $qtyOrdered - $qtyReceived;

        if ($stokBaru > $qtyRemaining) {
            return redirect()
                ->back()
                ->withInput()
                ->with('status', "Jumlah yang diterima ($stokBaru) melebihi sisa pesanan ($qtyRemaining).");
        }

        if ($batch->gudangs_id != $newGudangId) {
            // Create a new batch with the same data but a different gudang
            Produkbatches::create([
                'produks_id' => $batch->produks_id,
                'stok' => $stokBaru,
                'unitprice' => $batch->unitprice,
                'distributors_id' => $batch->distributors_id,
                'tgl_produksi' => $batch->tgl_produksi,
                'tgl_kadaluarsa' => $batch->tgl_kadaluarsa ?? null,
                'tgl_datang' => $request->get('tgl_datang'),
                'status' => 'tersedia',
                'satuans_id' => $batch->satuans_id,
                'gudangs_id' => $newGudangId,
            ]);
        } else {
            // Just update the current batch
            // $batch->increment('stok', $stokBaru);
            $batch->update([
                'stok' => $batch->stok + $stokBaru,
                'tgl_datang' => $request->get('tgl_datang'),
                'status' => 'tersedia',
            ]);
        }

        // dd($request->get('pegawai_id'));
        Terimabatches::create([
            'pegawai_id' => $request->get('pegawai_id'),
            'produkbatches_id' => $batch->id,
            'stok' => $stokBaru,
            'gudangs_id' => $newGudangId,
        ]);

        return redirect()->route('produks.batch', [
            'id' => $request->get('produks_id')
        ]);
    }


    public function editBatch($id)
    {
        
        $data = Produkbatches::find($id);
        $distributors = Distributor::all();
        $satuans = Satuan::all();
        $gudangs = Gudang::all();
        // echo'masuk form edit';
        return view('produk.editBatch', ['datas' => $data, 'distributors' => $distributors, 'satuans' => $satuans, 'gudangs' => $gudangs]);
    }

    public function updateBatch(Request $request, $id)
    {
        $data = Produkbatches::where('id', $id)
            ->update([
                'stok' => $request->get('stok'),
                'status' => $request->get('status'),
                'unitprice' => $request->get('unitprice'),
                'tgl_produksi' => $request->get('tgl_produksi'),
                'tgl_datang' => $request->get('tgl_datang'),
                'tgl_kadaluarsa' => $request->get('tgl_kadaluarsa') ?: null,
                'satuans_id' => $request->get('satuans'),
                'distributors_id' => $request->get('distributors'),
                'gudangs_id' => $request->get('gudangs'),
            ]);

        // Rekalkulasi HPP setelah edit batch manual
        $produkId = $request->get('produks_id');
        $activeBatches = Produkbatches::where('produks_id', $produkId)
            ->where('status', 'tersedia')
            ->where(function ($q) {
                $q->whereDate('tgl_kadaluarsa', '>', now())
                    ->orWhereNull('tgl_kadaluarsa');
            })
            ->get();

        $totalNilai = $activeBatches->sum(fn($b) => (float) $b->unitprice * (int) $b->stok);
        $totalStok  = $activeBatches->sum(fn($b) => (int) $b->stok);
        $hppBaru    = $totalStok > 0 ? ($totalNilai / $totalStok) : 0;

        if ($hppBaru > 0) {
            // Update nilai HPP baru ke semua batch aktif produk ini
            Produkbatches::where('produks_id', $produkId)
                ->where('status', 'tersedia')
                ->where(function ($q) {
                    $q->whereDate('tgl_kadaluarsa', '>', now())
                        ->orWhereNull('tgl_kadaluarsa');
                })
                ->update(['hpp_avg_per_unit' => $hppBaru]);

            // Catat ke HppRecord agar sinkronisasi dan sistem history tetap terjaga
            \App\Models\HppRecord::create([
                'produks_id' => $produkId,
                'notabelis_id' => null,
                'stok_lama' => $totalStok,
                'hpp_avg_lama' => 0, // Penyesuaian
                'qty_transaksi' => 0,
                'harga_transaksi' => 0,
                'stok_baru' => $totalStok,
                'hpp_avg_baru' => $hppBaru,
                'keterangan' => 'Penyesuaian manual (Edit Batch ID: ' . $id . ')',
            ]);
        }

        // Type::create($request->all());
        return redirect()->route('produks.batch', [
            'id' => $request->get('produks_id')
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = Produk::find($id);
            $deletedData->delete();
            return redirect('produks')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('produks')->with('status', $msg);
        }
    }

    public function destroyBatch($id)
    {
        $batch = Produkbatches::where('id', $id)->first();
        // dd($id);
        try {
            $batch->delete(); // Delete the batch, not the produk!
            return redirect()->route('produks.batch', [
                'id' => $batch->produks_id
            ])->with('status', 'Batch deleted successfully!');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('produks.batch', [
                'id' => $batch->produks_id
            ])->with('status', $msg);
        }
    }

    public function destroyTerima($id)
    {
        // dd($id);
        $deletedData = Terimabatches::find($id);
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData->delete();
            return redirect('daftarTerima')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('daftarTerima')->with('status', $msg);
        }
    }

    public function daftarTerima(Request $request)
    {
        $query = \DB::table('notabelis_has_produks as nbp')
            ->join('notabelis as nb', 'nbp.notabelis_id', '=', 'nb.id')
            ->leftJoin('users as u', 'nb.pegawai_id', '=', 'u.id')
            ->join('produkbatches as pb', 'nbp.produkbatches_id', '=', 'pb.id')
            ->join('produks as p', 'pb.produks_id', '=', 'p.id')
            ->leftJoin('gudangs as g', 'pb.gudangs_id', '=', 'g.id')
            ->leftJoin('distributors as d', 'pb.distributors_id', '=', 'd.id')
            ->leftJoin('satuans as s', 'pb.satuans_id', '=', 's.id')
            ->select(
                'nbp.notabelis_id as terima_id',
                'nbp.notabelis_id',
                \DB::raw("CONCAT(nbp.notabelis_id, '-', nbp.produkbatches_id) as detail_pembelian_id"),

                'nb.pegawai_id',
                'u.nama as nama_pegawai',

                'pb.id as batch_id',
                'pb.id as id_batch',
                'pb.produks_id',
                'pb.distributors_id',
                'pb.satuans_id',
                'pb.gudangs_id',
                'pb.stok as stok_tersisa',
                'pb.unitprice',
                'pb.hpp_avg_per_unit',
                'pb.tgl_datang',
                'pb.tgl_produksi',
                'pb.tgl_kadaluarsa',
                'pb.status',

                'nbp.quantity as jumlah_diterima',
                'nbp.subtotal',

                'p.nama as nama_produk',
                'g.lokasi as nama_gudang',
                'd.nama as nama_distributor',
                's.nama as nama_satuan'
            );
    
        if (\Schema::hasColumn('notabelis_has_produks', 'deleted_at')) {
            $query->whereNull('nbp.deleted_at');
        }
    
        if (\Schema::hasColumn('notabelis', 'deleted_at')) {
            $query->whereNull('nb.deleted_at');
        }
    
        if (\Schema::hasColumn('produkbatches', 'deleted_at')) {
            $query->whereNull('pb.deleted_at');
        }
    
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nbp.notabelis_id', 'LIKE', "%{$search}%")
                    ->orWhere('nbp.notabelis_id', 'LIKE', "%{$search}%")
                    ->orWhere('pb.id', 'LIKE', "%{$search}%")
                    ->orWhere('p.nama', 'LIKE', "%{$search}%")
                    ->orWhere('u.nama', 'LIKE', "%{$search}%")
                    ->orWhere('d.nama', 'LIKE', "%{$search}%")
                    ->orWhere('g.lokasi', 'LIKE', "%{$search}%")
                    ->orWhere('s.nama', 'LIKE', "%{$search}%")
                    ->orWhere('nbp.quantity', 'LIKE', "%{$search}%")
                    ->orWhere('pb.stok', 'LIKE', "%{$search}%")
                    ->orWhere('pb.unitprice', 'LIKE', "%{$search}%")
                    ->orWhere('pb.tgl_datang', 'LIKE', "%{$search}%")
                    ->orWhere('pb.tgl_produksi', 'LIKE', "%{$search}%")
                    ->orWhere('pb.tgl_kadaluarsa', 'LIKE', "%{$search}%");
            });
        }
    
        $sortBy = $request->get('sort_by', 'tgl_datang');
        $sortOrder = $request->get('sort_order', 'desc');
    
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
    
        switch ($sortBy) {
            case 'id_batch':
                $query->orderBy('pb.id', $sortOrder);
                break;
    
            case 'id_terima':
                $query->orderBy('nbp.notabelis_id', $sortOrder);
                break;
    
            case 'id_nota':
                $query->orderBy('nbp.notabelis_id', $sortOrder);
                break;
    
            case 'pegawai_id':
                $query->orderBy('nb.pegawai_id', $sortOrder);
                break;
    
            case 'nama_pegawai':
                $query->orderBy('u.nama', $sortOrder);
                break;
    
            case 'nama_produk':
                $query->orderBy('p.nama', $sortOrder);
                break;
    
            case 'nama_gudang':
                $query->orderBy('g.lokasi', $sortOrder);
                break;
    
            case 'nama_dist':
                $query->orderBy('d.nama', $sortOrder);
                break;
    
            case 'nama_satuan':
                $query->orderBy('s.nama', $sortOrder);
                break;
    
            case 'jumlah_diterima':
                $query->orderBy('nbp.quantity', $sortOrder);
                break;
    
            case 'stok':
                $query->orderBy('pb.stok', $sortOrder);
                break;
    
            case 'tgl_datang':
                $query->orderBy('pb.tgl_datang', $sortOrder);
                break;
    
            case 'tgl_kadaluarsa':
                $query->orderBy('pb.tgl_kadaluarsa', $sortOrder);
                break;
    
            default:
                $query->orderByDesc('pb.tgl_datang')
                    ->orderByDesc('pb.id');
                break;
        }
    
        $datas = $query->paginate(10)->appends($request->query());
    
        return view('transaksi.daftarPenerimaan', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search,
        ]);
    }


    public function printTerima($id)
    {
        $data = \DB::table('produkbatches')
            ->join('produks', 'produkbatches.produks_id', '=', 'produks.id')
            ->leftJoin('distributors', 'produkbatches.distributors_id', '=', 'distributors.id')
            ->leftJoin('satuans', 'produkbatches.satuans_id', '=', 'satuans.id')
            ->leftJoin('gudangs', 'produkbatches.gudangs_id', '=', 'gudangs.id')
            ->select(
                'produkbatches.*',
                'produkbatches.id as batch_id',
                \DB::raw('NULL as nota_beli_id'),
                \DB::raw('NULL as pegawai_id'),
                \DB::raw("'-' as nama_pegawai"),
                'produks.nama as nama_produk',
                'distributors.nama as nama_distributor',
                'satuans.nama as nama_satuan',
                'gudangs.lokasi as nama_gudang'
            )
            ->where('produkbatches.id', $id)
            ->first();

        if (!$data) {
            abort(404, 'Data penerimaan tidak ditemukan.');
        }

        return view('transaksi.printPenerimaan', compact('data'));
    }

    private function kadaluarsaQuery()
    {
        return Produk::query()
            ->join('produkbatches', 'produkbatches.produks_id', '=', 'produks.id')
            ->join('satuans', 'produkbatches.satuans_id', '=', 'satuans.id')
            ->select(
                'produks.id as produk_id',
                'produks.nama as nama_produk',
                'satuans.nama as nama_satuan',
                'produkbatches.id as batch_id',
                'produkbatches.tgl_kadaluarsa'
            )
            ->selectRaw('SUM(produkbatches.stok) as stok')
            ->selectRaw('
            (SUM(produkbatches.unitprice * produkbatches.stok) / NULLIF(SUM(produkbatches.stok),0))
            * (1 + (produks.sellingprice/100)) as hpp
        ')
            ->selectRaw('
            (
                (SUM(produkbatches.unitprice * produkbatches.stok) / NULLIF(SUM(produkbatches.stok),0))
                * (1 + (produks.sellingprice/100))
            ) * SUM(produkbatches.stok) as total_harga
        ')
            ->where('produkbatches.tgl_kadaluarsa', '<', now())
            ->groupBy(
                'produkbatches.id',
                'produks.id',
                'produks.nama',
                'satuans.nama',
                'produkbatches.tgl_kadaluarsa',
                'produks.sellingprice'
            );
    }

        public function daftarKadaluarsa(Request $request)
    {
        $today = \Carbon\Carbon::today();
        $filter = $request->get('filter', 'year');

        // Tentukan batas tanggal kadaluarsa berdasarkan filter
        switch ($filter) {
            case 'expired':
                // Sudah kadaluarsa (sebelum hari ini)
                $batasKadaluarsa = $today->copy()->subDay();
                $sudahKadaluarsa = true;
                break;
            case 'month':
                $batasKadaluarsa = $today->copy()->endOfMonth();
                $sudahKadaluarsa = false;
                break;
            case '3month':
                $batasKadaluarsa = $today->copy()->addMonths(3);
                $sudahKadaluarsa = false;
                break;
            case '6month':
                $batasKadaluarsa = $today->copy()->addMonths(6);
                $sudahKadaluarsa = false;
                break;
            case 'year':
            default:
                $batasKadaluarsa = $today->copy()->endOfYear();
                $sudahKadaluarsa = false;
                break;
        }

        $query = \DB::table('produkbatches')
            ->select(
                'produkbatches.*',
                'produkbatches.id as batch_id',
                'produks.nama as nama_produk',
                'gudangs.lokasi as nama_gudang',
                'distributors.nama as nama_distributor',
                'satuans.nama as nama_satuan',
                \DB::raw('COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0) as hpp_produk'),
                \DB::raw('(produkbatches.stok * COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)) as total_harga')
            )
            ->join('produks', 'produkbatches.produks_id', '=', 'produks.id')
            ->leftJoin('gudangs', 'produkbatches.gudangs_id', '=', 'gudangs.id')
            ->leftJoin('distributors', 'produkbatches.distributors_id', '=', 'distributors.id')
            ->leftJoin('satuans', 'produkbatches.satuans_id', '=', 'satuans.id')
            ->whereNotNull('produkbatches.tgl_kadaluarsa');

        // Untuk filter "sudah kadaluarsa", tampilkan yang expired terlepas dari stok
        if (isset($sudahKadaluarsa) && $sudahKadaluarsa) {
            $query->whereDate('produkbatches.tgl_kadaluarsa', '<', $today->toDateString());
        } else {
            // Untuk yang belum kadaluarsa, tampilkan yang masih ada stok dan akan expired
            $query->where('produkbatches.stok', '>', 0)
                  ->whereDate('produkbatches.tgl_kadaluarsa', '<=', $batasKadaluarsa->toDateString());

        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('produkbatches.id', 'LIKE', "%$search%")
                    ->orWhere('produks.nama', 'LIKE', "%$search%")
                    ->orWhere('distributors.nama', 'LIKE', "%$search%")
                    ->orWhere('gudangs.lokasi', 'LIKE', "%$search%")
                    ->orWhere('satuans.nama', 'LIKE', "%$search%")
                    ->orWhere('produkbatches.stok', 'LIKE', "%$search%")
                    ->orWhere('produkbatches.unitprice', 'LIKE', "%$search%")
                    ->orWhere('produkbatches.hpp_avg_per_unit', 'LIKE', "%$search%")
                    ->orWhere('produkbatches.tgl_kadaluarsa', 'LIKE', "%$search%");
            });
        }

        $sortBy = $request->get('sort_by', 'tgl_kadaluarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'id_batch':
                $query->orderBy('produkbatches.id', $sortOrder);
                break;

            case 'nama_produk':
                $query->orderBy('produks.nama', $sortOrder);
                break;

            case 'nama_gudang':
                $query->orderBy('gudangs.lokasi', $sortOrder);
                break;

            case 'nama_dist':
                $query->orderBy('distributors.nama', $sortOrder);
                break;

            case 'stok':
                $query->orderBy('produkbatches.stok', $sortOrder);
                break;

            case 'hpp_produk':
                $query->orderBy('hpp_produk', $sortOrder);
                break;

            case 'total_harga':
                $query->orderBy('total_harga', $sortOrder);
                break;

            case 'tgl_kadaluarsa':
                $query->orderBy('produkbatches.tgl_kadaluarsa', $sortOrder);
                break;

            default:
                $query->orderBy('produkbatches.tgl_kadaluarsa', 'asc');
                break;
        }

        $datas = $query->paginate(10)->appends($request->query());

        return view('transaksi.daftarKadaluarsa', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search,
            'today' => $today,
            'batasKadaluarsa' => $batasKadaluarsa,
            'filter' => $filter,
        ]);
    }

    public function reportKadaluarsa(Request $request)
    {
        $filter = $request->get('filter', 'day');

        $query = $this->kadaluarsaQuery();

        switch ($filter) {
            case 'week':
                $query->whereBetween('produkbatches.tgl_kadaluarsa', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;

            case 'month':
                $query->whereYear('produkbatches.tgl_kadaluarsa', now()->year)
                    ->whereMonth('produkbatches.tgl_kadaluarsa', now()->month);
                break;

            case 'year':
                $query->whereYear('produkbatches.tgl_kadaluarsa', now()->year);
                break;

            case 'day':
            default:
                $query->whereDate('produkbatches.tgl_kadaluarsa', now());
        }

        $expires = $query->get();

        $total = $expires->sum('total_harga');

        return view('transaksi.reportKadaluarsa', compact('expires', 'total', 'filter'));
    }

    public function reportCsvKadaluarsa(Request $request)
    {
        $filter = $request->get('filter', 'day');

        $query = $this->kadaluarsaQuery();

        switch ($filter) {
            case 'week':
                $query->whereBetween('produkbatches.tgl_kadaluarsa', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;

            case 'month':
                $query->whereYear('produkbatches.tgl_kadaluarsa', now()->year)
                    ->whereMonth('produkbatches.tgl_kadaluarsa', now()->month);
                break;

            case 'year':
                $query->whereYear('produkbatches.tgl_kadaluarsa', now()->year);
                break;

            case 'day':
            default:
                $query->whereDate('produkbatches.tgl_kadaluarsa', now());
        }

        $expires = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan_kadaluarsa.csv"',
        ];

        $callback = function () use ($expires) {

            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Batch ID',
                'ID Produk',
                'Nama Produk',
                'Stok',
                'Satuan',
                'HPP',
                'Total Harga',
                'Tanggal Kadaluarsa'
            ], ',');

            foreach ($expires as $expire) {

                fputcsv($file, [
                    $expire->batch_id,
                    $expire->produk_id,
                    $expire->nama_produk,
                    $expire->stok,
                    $expire->nama_satuan,
                    number_format($expire->hpp, 0, '.', ','),
                    number_format($expire->total_harga, 0, '.', ','),
                    $expire->tgl_kadaluarsa
                ], ',');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printKadaluarsa($id)
    {
        $nota = $this->kadaluarsaQuery()
            ->where('produkbatches.id', $id)
            ->firstOrFail();

        return view('transaksi.nkPrint', compact('nota'));
    }

    public function print($id)
    {
        $nota = Terimabatches::with(['user', 'produkbatches.produks', 'gudangs'])->findOrFail($id);

        // dd($nota);
        return view('transaksi.ntPrint', compact('nota'));
    }
}
