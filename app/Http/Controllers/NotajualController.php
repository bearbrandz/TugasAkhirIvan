<?php

namespace App\Http\Controllers;

// use App\Models\GeneralModel;
use App\Models\Notajual;
use App\Models\Notajualproduk;
use App\Models\Produk;
use App\Models\Produkbatches;
use App\Models\Racikan;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\HppService;
use App\Models\LogActivity;

class NotajualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
    
        $query = \DB::table('notajuals')
            ->leftJoin('users', 'notajuals.pegawai_id', '=', 'users.id')
            ->leftJoin('notajuals_has_produks', 'notajuals.id', '=', 'notajuals_has_produks.notajuals_id')
            ->leftJoin('produkbatches', 'notajuals_has_produks.produkbatches_id', '=', 'produkbatches.id')
            ->leftJoin('produks', 'produkbatches.produks_id', '=', 'produks.id')
            ->leftJoin('distributors', 'produkbatches.distributors_id', '=', 'distributors.id')
            ->leftJoin('satuans', 'produkbatches.satuans_id', '=', 'satuans.id')
            ->select(
                'notajuals.id',
                'notajuals.pegawai_id',
                'users.nama as nama_pegawai',
                'notajuals.created_at',
    
                \DB::raw('COUNT(notajuals_has_produks.produkbatches_id) as jumlah_item'),
                \DB::raw('COALESCE(SUM(notajuals_has_produks.quantity), 0) as total_qty'),
                \DB::raw('COALESCE(SUM(notajuals_has_produks.subtotal), 0) as total_transaksi'),
                'notajuals.total_bayar',
                'notajuals.nominal_bayar',
                'notajuals.kembalian',
                'notajuals.metode_bayar',
    
                \DB::raw("GROUP_CONCAT(DISTINCT produkbatches.id ORDER BY produkbatches.id SEPARATOR ', ') as batch_ids"),
                \DB::raw("GROUP_CONCAT(DISTINCT produks.id ORDER BY produks.id SEPARATOR ', ') as produk_ids"),
                \DB::raw("GROUP_CONCAT(DISTINCT produks.nama ORDER BY produks.nama SEPARATOR ', ') as nama_produk"),
                \DB::raw("GROUP_CONCAT(DISTINCT distributors.nama ORDER BY distributors.nama SEPARATOR ', ') as nama_distributor"),
                \DB::raw("GROUP_CONCAT(DISTINCT satuans.nama ORDER BY satuans.nama SEPARATOR ', ') as nama_satuan"),
    
                \DB::raw("
                    GROUP_CONCAT(
                        CONCAT(produks.nama, ' x', notajuals_has_produks.quantity, ' = Rp ', FORMAT(notajuals_has_produks.subtotal, 0))
                        ORDER BY produks.nama
                        SEPARATOR ' | '
                    ) as detail_produk
                ")
            )
            ->whereNull('notajuals_has_produks.deleted_at')
            ->groupBy(
                'notajuals.id',
                'notajuals.pegawai_id',
                'users.nama',
                'notajuals.created_at',
                'notajuals.total_bayar',
                'notajuals.nominal_bayar',
                'notajuals.kembalian',
                'notajuals.metode_bayar'
            );
    
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('notajuals.id', 'LIKE', "%$search%")
                    ->orWhere('users.nama', 'LIKE', "%$search%")
                    ->orWhere('produks.nama', 'LIKE', "%$search%")
                    ->orWhere('distributors.nama', 'LIKE', "%$search%")
                    ->orWhere('satuans.nama', 'LIKE', "%$search%")
                    ->orWhere('notajuals.created_at', 'LIKE', "%$search%")
                    ->orWhere('notajuals.metode_bayar', 'LIKE', "%$search%");
            });
        }
    
        switch ($sortBy) {
            case 'id':
                $query->orderBy('notajuals.id', $sortOrder);
                break;
    
            case 'pegawai_id':
                $query->orderBy('notajuals.pegawai_id', $sortOrder);
                break;
    
            case 'nama_pegawai':
                $query->orderBy('users.nama', $sortOrder);
                break;
    
            case 'nama_produk':
                $query->orderBy('nama_produk', $sortOrder);
                break;
    
            case 'total_qty':
                $query->orderBy('total_qty', $sortOrder);
                break;
    
            case 'total_transaksi':
                $query->orderBy('total_transaksi', $sortOrder);
                break;
            
            case 'total_bayar':
                $query->orderBy('notajuals.total_bayar', $sortOrder);
                break;    
    
            case 'created_at':
            default:
                $query->orderBy('notajuals.created_at', $sortOrder);
                break;
        }
    
        $datas = $query->paginate(15)->appends($request->query());
    
        return view('transaksi.daftarPenjualan', [
            'datas' => $datas,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }
    public function report(Request $request)
    {
        $filter = $request->get('filter', 'day');

        // Base query: eager load related produk info
        $query = Notajualproduk::with('notajual', 'produkbatches.produks', 'produkbatches.satuan');

        // Grouping and filtering by created_at according to filter
        switch ($filter) {
            case 'week':
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
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
        }

        $sales = $query->get();

        // Group by notajuals_id and produks_id to consolidate split batches
        $groupedSales = $sales->groupBy(function($item) {
            return $item->notajuals_id . '_' . ($item->produkbatches->produks_id ?? 0);
        })->map(function($group) {
            $first = $group->first();
            return (object) [
                'notajual_id' => $first->notajuals_id ?? '-',
                'produk_id'   => $first->produkbatches->produks_id ?? '-',
                'nama_produk' => $first->produkbatches->produks->nama ?? '-',
                'nama_satuan' => $first->produkbatches->satuan->nama ?? '',
                'quantity'    => $group->sum('quantity'),
                'subtotal'    => $group->sum('subtotal')
            ];
        })->values();

        // Calculate total sales (sum of subtotal)
        $total = $groupedSales->sum('subtotal');

        return view('transaksi.reportPenjualan', ['sales' => $groupedSales, 'total' => $total, 'filter' => $filter]);
    }

    public function reportCsv(Request $request)
    {
        $filter = $request->get('filter', 'day');

        $query = Notajualproduk::with('notajual', 'produkbatches.produks', 'produkbatches.satuan');

        switch ($filter) {
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
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
        }

        $sales = $query->get();

        // Group by notajuals_id and produks_id to consolidate split batches
        $groupedSales = $sales->groupBy(function($item) {
            return $item->notajuals_id . '_' . ($item->produkbatches->produks_id ?? 0);
        })->map(function($group) {
            $first = $group->first();
            return (object) [
                'notajual_id' => $first->notajuals_id ?? '-',
                'produk_id'   => $first->produkbatches->produks_id ?? '-',
                'nama_produk' => $first->produkbatches->produks->nama ?? '-',
                'nama_satuan' => $first->produkbatches->satuan->nama ?? '',
                'quantity'    => $group->sum('quantity'),
                'subtotal'    => $group->sum('subtotal')
            ];
        })->values();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan_penjualan.csv"',
        ];

        $callback = function () use ($groupedSales) {
            // Open PHP output stream
            $file = fopen('php://output', 'w');

            // BOM to ensure Excel reads UTF-8 properly
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($file, "sep=,\n"); // Petunjuk separator untuk Excel

            // Column headers
            fputcsv($file, ['Nota ID', 'ID Produk', 'Nama Produk', 'Quantity', 'Subtotal']);

            // Data rows
            foreach ($groupedSales as $sale) {
                fputcsv($file, [
                    $sale->notajual_id,
                    $sale->produk_id,
                    $sale->nama_produk,
                    $sale->quantity . ' ' . $sale->nama_satuan,
                    number_format($sale->subtotal, 0, '.', ',')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    
    public function create(Request $request)
    {
        // session()->forget('cart');
        $search = $request->input('search');
        // Use dedicated session key for sales cart to avoid collision with purchase cart
        $cart = session('cart_jual', []);

        // Produk query: tampilkan per produk dengan info FEFO (batch expired paling dekat di-highlight)
        // Harga jual = HPP Avg terbaru x (1 + markup%)
        $produksQuery = DB::table('produks')
            ->join('produkbatches', function ($join) {
                $join->on('produkbatches.produks_id', '=', 'produks.id')
                    ->where('produkbatches.status', '=', 'tersedia')
                    ->where('produkbatches.stok', '>', 0)
                    ->where(function ($q) {
                        $q->whereDate('produkbatches.tgl_kadaluarsa', '>', Carbon::now())
                            ->orWhereNull('produkbatches.tgl_kadaluarsa');
                    })
                    ->whereNotNull('produkbatches.tgl_datang');
            })
            ->join('satuans', 'produkbatches.satuans_id', '=', 'satuans.id')
            ->whereNotIn('produks.golongan', ['narkotika', 'psikotropika'])
            ->whereNull('produks.deleted_at')
            ->select(
                'produks.id',
                'produks.nama',
                'produks.sellingprice as markup_persen',
                'satuans.nama as satuan_nama',
                DB::raw('SUM(produkbatches.stok) as stok'),
                // Harga jual dihitung dari HPP avg (hpp_avg_per_unit) x (1 + markup%)
                DB::raw('
                    CASE
                        WHEN SUM(produkbatches.stok) > 0 AND SUM(produkbatches.hpp_avg_per_unit * produkbatches.stok) > 0
                        THEN (SUM(produkbatches.hpp_avg_per_unit * produkbatches.stok) / SUM(produkbatches.stok))
                             * (1 + (produks.sellingprice / 100))
                        ELSE (SUM(produkbatches.unitprice * produkbatches.stok) / NULLIF(SUM(produkbatches.stok), 0))
                             * (1 + (produks.sellingprice / 100))
                    END as sellingprice'),
                // FEFO: tampilkan tanggal kadaluarsa terdekat (nearest expired first)
                DB::raw('MIN(produkbatches.tgl_kadaluarsa) as tgl_kadaluarsa'),
                DB::raw('MIN(produkbatches.distributors_id) as distributors_id')
            )
            ->groupBy('produks.id', 'produks.nama', 'produks.sellingprice', 'satuan_nama')
            ->orderBy(DB::raw('MIN(produkbatches.tgl_kadaluarsa)'), 'asc'); // FEFO ordering

        if ($search) {
            $produksQuery->where('produks.nama', 'like', '%' . $search . '%');
        }

        $produks = $produksQuery->get();

        // Merge regular and racikan products
        // $merged = $produks->merge($racikans);

        $page = request()->get('page', 1);
        $perPage = 6;
        $offset = ($page - 1) * $perPage;
        $paginated = new LengthAwarePaginator(
            $produks->slice($offset, $perPage)->values(),  // use only $produks
            $produks->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // === NOTIFIKASI KADALUARSA & STOK KRITIS untuk kasir ===
        $batchExpired = Produkbatches::with('produks')
            ->whereHas('produks')
            ->where('status', 'tersedia')
            ->where('stok', '>', 0)
            ->whereNotNull('tgl_kadaluarsa')
            ->whereDate('tgl_kadaluarsa', '<=', now())
            ->get()
            ->map(fn($b) => "Batch #{$b->id} - " . optional($b->produks)->nama . " telah kadaluarsa!");

        $batchWillExpire = Produkbatches::with('produks')
            ->whereHas('produks')
            ->where('status', 'tersedia')
            ->where('stok', '>', 0)
            ->whereNotNull('tgl_kadaluarsa')
            ->whereDate('tgl_kadaluarsa', '>', now())
            ->whereDate('tgl_kadaluarsa', '<=', now()->addMonths(3))
            ->get()
            ->map(fn($b) => "Batch #{$b->id} - " . optional($b->produks)->nama . " akan kadaluarsa pada {$b->tgl_kadaluarsa}!");

        $lowStockProduk = Produk::withSum(
            ['produkbatches as total_stok' => fn($q) => $q->where('status', 'tersedia')
                ->where(fn($s) => $s->whereDate('tgl_kadaluarsa', '>', now())->orWhereNull('tgl_kadaluarsa'))],
            'stok'
        )->get()
        ->filter(fn($p) => ($p->total_stok ?? 0) < 10 && ($p->total_stok ?? 0) > 0)
        ->map(fn($p) => "{$p->nama} — sisa stok: {$p->total_stok}");

        return view('transaksi.jualProduk', [
            'prod'          => $paginated,
            'search'        => $search,
            'cart'          => $cart,
            'batchExpired'  => $batchExpired,
            'batchWillExpire' => $batchWillExpire,
            'lowStockProduk'  => $lowStockProduk,
        ]);
    }

    private function generateNomorNota(): string
    {
        $tanggal = now()->format('Ymd');

        $lastNota = Notajual::whereDate('created_at', now()->toDateString())
            ->whereNotNull('nomor_nota')
            ->where('nomor_nota', 'like', 'NJ-' . $tanggal . '-%')
            ->orderBy('nomor_nota', 'desc')
            ->first();

        if ($lastNota) {
            $lastNumber = (int) substr($lastNota->nomor_nota, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'NJ-' . $tanggal . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $pegawaiId = $request->input('pegawai_id') ?? Auth::id();
    
            /*
            |--------------------------------------------------------------------------
            | Ambil item dari request atau fallback dari session cart_jual
            |--------------------------------------------------------------------------
            */
            $items = [];
    
            $ids = $request->input('id', []);
            $quantities = $request->input('quantity', []);
            $isRacikanFlags = $request->input('is_racikan', []);
    
            if (!is_array($ids)) {
                $ids = [$ids];
            }
    
            if (!is_array($quantities)) {
                $quantities = [$quantities];
            }
    
            if (!is_array($isRacikanFlags)) {
                $isRacikanFlags = [$isRacikanFlags];
            }
    
            if (count($ids) > 0 && !empty(array_filter($ids))) {
                foreach ($ids as $i => $id) {
                    if (empty($id)) {
                        continue;
                    }
    
                    $qty = (int) ($quantities[$i] ?? 0);
    
                    if ($qty <= 0) {
                        continue;
                    }
    
                    $items[] = [
                        'id' => (int) $id,
                        'quantity' => $qty,
                        'is_racikan' => (($isRacikanFlags[$i] ?? '0') == '1'),
                    ];
                }
            } else {
                $cart = session('cart_jual', []);
    
                foreach ($cart as $cartItem) {
                    $id = $cartItem['id'] ?? null;
                    $qty = (int) ($cartItem['quantity'] ?? 0);
    
                    if (empty($id) || $qty <= 0) {
                        continue;
                    }
    
                    $items[] = [
                        'id' => (int) $id,
                        'quantity' => $qty,
                        'is_racikan' => ($cartItem['is_racikan'] ?? false) == true,
                    ];
                }
            }
    
            if (empty($items)) {
                throw new \Exception('Keranjang penjualan masih kosong atau quantity belum valid.');
            }
    
            /*
            |--------------------------------------------------------------------------
            | Buat nota jual sementara
            |--------------------------------------------------------------------------
            */
            $notajual = Notajual::create([
                'nomor_nota' => $this->generateNomorNota(),
                'pegawai_id' => $pegawaiId,
            ]);
    
            $totalTransaksi = 0;
    
            /*
            |--------------------------------------------------------------------------
            | Proses item penjualan
            |--------------------------------------------------------------------------
            */
            foreach ($items as $item) {
                $produkId = $item['id'];
                $jumlah = $item['quantity'];
                $isRacikan = $item['is_racikan'];
    
                if ($isRacikan) {
                    /*
                    |--------------------------------------------------------------------------
                    | RACIKAN
                    |--------------------------------------------------------------------------
                    */
                    $racikan = Racikan::with('produks')->findOrFail($produkId);
                    $totalHargaRacikan = 0;
    
                    foreach ($racikan->produks as $produk) {
                        $totalQty = (int) $produk->pivot->quantity * $jumlah;
                        $sisa = $totalQty;
    
                        $availableStock = Produkbatches::where('produks_id', $produk->id)
                            ->where('stok', '>', 0)
                            ->where('status', 'tersedia')
                            ->where(function ($q) {
                                $q->whereDate('tgl_kadaluarsa', '>', now())
                                    ->orWhereNull('tgl_kadaluarsa');
                            })
                            ->sum('stok');
    
                        if ($availableStock < $totalQty) {
                            throw new \Exception("Stok produk {$produk->nama} tidak mencukupi untuk meracik {$racikan->nama}.");
                        }
    
                        $hppTerkini = HppService::getHppTerkini($produk->id);
    
                        if (!$hppTerkini) {
                            throw new \Exception("HPP tidak tersedia untuk produk {$produk->nama}. Pastikan produk sudah memiliki riwayat pembelian.");
                        }
    
                        $finalPrice = round($hppTerkini * (1 + ((float) $produk->sellingprice / 100)), 0);
    
                        $batches = Produkbatches::where('produks_id', $produk->id)
                            ->where('stok', '>', 0)
                            ->where('status', 'tersedia')
                            ->where(function ($q) {
                                $q->whereDate('tgl_kadaluarsa', '>', now())
                                    ->orWhereNull('tgl_kadaluarsa');
                            })
                            ->orderByRaw('tgl_kadaluarsa IS NULL, tgl_kadaluarsa ASC')
                            ->orderBy('id', 'asc')
                            ->get();
    
                        foreach ($batches as $batch) {
                            if ($sisa <= 0) {
                                break;
                            }
    
                            $terjual = min($sisa, $batch->stok);
                            $subtotal = $terjual * $finalPrice;
    
                            $batch->decrement('stok', $terjual);
    
                            DB::table('notajuals_has_produks')->insert([
                                'notajuals_id' => $notajual->id,
                                'produkbatches_id' => $batch->id,
                                'quantity' => $terjual,
                                'subtotal' => $subtotal,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
    
                            $totalHargaRacikan += $subtotal;
                            $totalTransaksi += $subtotal;
                            $sisa -= $terjual;
                        }
    
                        if ($sisa > 0) {
                            throw new \Exception("Stok produk {$produk->nama} tidak mencukupi untuk meracik {$racikan->nama}.");
                        }
                    }
    
                    $biayaEmbalase = (float) ($racikan->biaya_embalase ?? 0);
                    $totalHargaRacikan += $biayaEmbalase;
                    $totalTransaksi += $biayaEmbalase;
    
                    DB::table('notajuals_has_racikans')->insert([
                        'notajuals_id' => $notajual->id,
                        'racikans_id' => $racikan->id,
                        'quantity' => $jumlah,
                        'subtotal' => $totalHargaRacikan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | PRODUK REGULER
                    |--------------------------------------------------------------------------
                    */
                    $produk = Produk::findOrFail($produkId);
    
                    if (in_array(strtolower($produk->golongan), ['narkotika', 'psikotropika'])) {
                        throw new \Exception("Produk Narkotika/Psikotropika ({$produk->nama}) tidak boleh dijual secara langsung di kasir! Wajib melalui Nota Penjualan Racikan (Resep).");
                    }
    
                    $availableStock = Produkbatches::where('produks_id', $produkId)
                        ->where('stok', '>', 0)
                        ->where('status', 'tersedia')
                        ->where(function ($q) {
                            $q->whereDate('tgl_kadaluarsa', '>', now())
                                ->orWhereNull('tgl_kadaluarsa');
                        })
                        ->sum('stok');
    
                    if ($availableStock < $jumlah) {
                        throw new \Exception("Stok produk {$produk->nama} tidak mencukupi. Stok tersedia: {$availableStock}.");
                    }
    
                    $hppTerkini = HppService::getHppTerkini($produkId);
    
                    if (!$hppTerkini) {
                        throw new \Exception("HPP tidak tersedia untuk produk {$produk->nama}. Pastikan produk sudah memiliki riwayat pembelian.");
                    }
    
                    $finalPrice = round($hppTerkini * (1 + ((float) $produk->sellingprice / 100)), 0);
                    $sisa = $jumlah;
    
                    $batches = Produkbatches::where('produks_id', $produkId)
                        ->where('stok', '>', 0)
                        ->where('status', 'tersedia')
                        ->where(function ($q) {
                            $q->whereDate('tgl_kadaluarsa', '>', now())
                                ->orWhereNull('tgl_kadaluarsa');
                        })
                        ->orderByRaw('tgl_kadaluarsa IS NULL, tgl_kadaluarsa ASC')
                        ->orderBy('id', 'asc')
                        ->get();
    
                    foreach ($batches as $batch) {
                        if ($sisa <= 0) {
                            break;
                        }
    
                        $terjual = min($sisa, $batch->stok);
                        $subtotal = $terjual * $finalPrice;
    
                        $batch->decrement('stok', $terjual);
    
                        DB::table('notajuals_has_produks')->insert([
                            'notajuals_id' => $notajual->id,
                            'produkbatches_id' => $batch->id,
                            'quantity' => $terjual,
                            'subtotal' => $subtotal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
    
                        $totalTransaksi += $subtotal;
                        $sisa -= $terjual;
                    }
    
                    if ($sisa > 0) {
                        throw new \Exception("Stok produk {$produk->nama} tidak mencukupi. Silakan kurangi jumlah penjualan.");
                    }
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | Validasi pembayaran kasir
            |--------------------------------------------------------------------------
            */
            $totalTransaksi = round($totalTransaksi, 0);
    
            $metodeBayar = strtolower($request->input('metode_bayar', 'tunai'));
    
            if (!in_array($metodeBayar, ['tunai', 'transfer'])) {
                $metodeBayar = 'tunai';
            }
    
            $nominalBayar = (float) $request->input('nominal_bayar', 0);
    
            if ($metodeBayar === 'transfer') {
                $nominalBayar = $totalTransaksi;
            }
    
            if ($nominalBayar < $totalTransaksi) {
                throw new \Exception('Nominal pembayaran kurang dari total belanja.');
            }
    
            $kembalian = $nominalBayar - $totalTransaksi;
    
            DB::table('notajuals')
                ->where('id', $notajual->id)
                ->update([
                    'total_bayar' => $totalTransaksi,
                    'nominal_bayar' => $nominalBayar,
                    'kembalian' => $kembalian,
                    'metode_bayar' => $metodeBayar,
                    'updated_at' => now(),
                ]);
    
            session()->forget(['cart_jual', 'racikan_cart']);
    
            DB::commit();

            // Catat aktivitas penjualan
            $namaPegawai = \App\Models\User::find($pegawaiId)?->nama ?? 'Unknown';
            \App\Models\LogActivity::catat(
                'penjualan_baru',
                'Transaksi Penjualan',
                'Nota jual #' . $notajual->id . ' (No. Nota: ' . $notajual->nomor_nota . ') berhasil disimpan oleh ' . $namaPegawai . '. Total: Rp ' . number_format($totalTransaksi, 0, ',', '.')
            );
    
            return redirect()
                ->route('notajuals.index')
                ->with('status', 'Nota jual berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
    
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
            $deletedData = Notajual::findOrFail($id);

            // Delete all related nota jual produks
            $deletedData->notaJualProduks()->delete();

            // Delete the notajual itself
            $deletedData->delete();
            return redirect('notajuals')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('notajuals')->with('status', $msg);
        }
    }

    public function addToCart(Request $request)
    {
        // Retrieve the current sales cart from session using the dedicated key
        $cart = session()->get('cart_jual', []);

        $id = $request->input('id');
        $isRacikan = $request->input('is_racikan') == true || $request->input('is_racikan') == 'true';

        if (!$isRacikan) {
            $produk = \App\Models\Produk::find($id);
            if ($produk && in_array(strtolower($produk->golongan), ['narkotika', 'psikotropika'])) {
                return redirect()->back()->with('error', 'Peringatan: Obat Narkotika / Psikotropika (' . $produk->nama . ') TIDAK DAPAT dijual langsung secara reguler! Anda WAJIB menjualnya melalui fitur Nota Penjualan Racikan (Resep) agar informasi Pasien dan Dokter dapat dicatat untuk keperluan Laporan SIPNAP.');
            }
        }

        // Use unique key for racikan or normal product
        $key = $isRacikan ? 'racikan_' . $id : 'produk_' . $id;

        $cart[$key] = [
            'id' => $id,
            'nama' => $request->input('nama'),
            'satuan' => $request->input('satuan'),
            'sellingprice' => $request->input('sellingprice'),
            'stok' => $request->input('stok'),
            'tgl_kadaluarsa' => $request->input('tgl_kadaluarsa') ?? 0,
            'distributors_id' => $request->input('distributors_id'),
            'quantity' => $request->input('quantity'),
            'is_racikan' => $isRacikan,
        ];

        // Save the updated cart back into the dedicated session key
        session()->put('cart_jual', $cart);

        return redirect()->route('notajuals.create')->with('success', 'Item added to cart.');
    }

    public function deleteFromCart($id)
    {
        // Retrieve the current sales cart
        $cart = session()->get('cart_jual', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            // Update the cart in session
            session()->put('cart_jual', $cart);
        }

        return redirect()->back()->with('status', 'Produk telah dibuang dari Cart');
    }

    public function print($id)
    {
        $nota = Notajual::with(['user', 'notajualproduks.produkbatches.produks', 'notajualracikans.racikan'])->findOrFail($id);

        // dd($nota);
        return view('transaksi.njPrint', compact('nota'));
    }
}
