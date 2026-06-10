<?php

namespace App\Http\Controllers;

use App\Models\Notajual;
use App\Models\Notajualracikan;
use App\Models\Notajualproduk;
use App\Models\Produk;
use App\Models\Produkbatches;
use App\Models\Racikan;
use App\Models\Racikanproduk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RacikanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');

        $allowedSorts = [
            'id',
            'nama',
            'nama_pasien',
            'nama_dokter',
            'tgl_ambil',
            'biaya_embalase',
            'created_at',
        ];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $query = Racikan::with(['racikanproduks.produk', 'notajualracikans']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('aturan_pakai', 'like', "%{$search}%")
                    ->orWhere('nama_pasien', 'like', "%{$search}%")
                    ->orWhere('nama_dokter', 'like', "%{$search}%");
            });
        }

        $datas = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate(10)
            ->appends($request->query());

        return view('racikan.index', compact('datas', 'search', 'sortBy', 'sortOrder'));
    }

    public function notaRacikan(Request $request)
    {
        $query = Notajualracikan::query()
            ->select(
                'notajuals_has_racikans.*',
                'racikans.id as racikans_id',
                'racikans.nama as nama_racikan',
                'racikans.nama_pasien',
                'racikans.nama_dokter',
                'users.nama as nama_pegawai',
                'notajuals.total_bayar',
                'notajuals.nominal_bayar',
                'notajuals.kembalian',
                'notajuals.metode_bayar',
                'notajuals.created_at as tanggal_transaksi'
            )
            ->join('notajuals', 'notajuals_has_racikans.notajuals_id', '=', 'notajuals.id')
            ->join('racikans', 'notajuals_has_racikans.racikans_id', '=', 'racikans.id')
            ->join('users', 'notajuals.pegawai_id', '=', 'users.id')
            ->whereNull('notajuals.deleted_at');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('racikans.nama', 'LIKE', "%$search%")
                    ->orWhere('users.nama', 'LIKE', "%$search%")
                    ->orWhere('racikans.nama_pasien', 'LIKE', "%$search%")
                    ->orWhere('racikans.nama_dokter', 'LIKE', "%$search%")
                    ->orWhere('notajuals_has_racikans.quantity', 'LIKE', "%$search%")
                    ->orWhere('notajuals.total_bayar', 'LIKE', "%$search%")
                    ->orWhere('notajuals.metode_bayar', 'LIKE', "%$search%")
                    ->orWhere('notajuals.created_at', 'LIKE', "%$search%");
            });
        }

        $sortBy = $request->get('sort_by', 'notajuals_id');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'nama_racikan':
                $query->orderBy('racikans.nama', $sortOrder);
                break;
            case 'nama_pegawai':
                $query->orderBy('users.nama', $sortOrder);
                break;
            case 'total_bayar':
                $query->orderBy('notajuals.total_bayar', $sortOrder);
                break;
            case 'metode_bayar':
                $query->orderBy('notajuals.metode_bayar', $sortOrder);
                break;
            case 'tanggal_transaksi':
                $query->orderBy('notajuals.created_at', $sortOrder);
                break;
            default:
                if (in_array($sortBy, ['notajuals_id', 'quantity', 'subtotal', 'created_at'])) {
                    $query->orderBy('notajuals_has_racikans.' . $sortBy, $sortOrder);
                } else {
                    $query->orderBy('notajuals.id', 'desc');
                }
                break;
        }

        $datas = $query->paginate(15)->appends($request->query());

        return view('transaksi.daftarPeracikan', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search
        ]);
    }

    public function komposisi(Request $request)
    {
        $id = $request->id;
        $data = Racikan::findOrFail($id);
        $query = Racikanproduk::query()
            ->select(
                'racikanproduks.*',
                'produks.id as produks_id',
                'produks.nama as nama_produk',
                'racikans.id as racikans_id',
                'racikans.nama as nama_racikan',
            )
            ->join('produks', 'racikanproduks.produks_id', '=', 'produks.id')
            ->join('racikans', 'racikanproduks.racikans_id', '=', 'racikans.id')
            ->where('racikanproduks.racikans_id', $id);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('racikanproduks.racikans_id', 'LIKE', "%$search%")
                    ->orWhere('racikanproduks.produks_id', 'LIKE', "%$search%")
                    ->orWhere('produks.nama', 'LIKE', "%$search%")
                    ->orWhere('racikans.nama', 'LIKE', "%$search%")
                    ->orWhere('racikanproduks.quantity', 'LIKE', "%$search%")
                    ->orWhere('racikanproduks.created_at', 'LIKE', "%$search%")
                    ->orWhere('racikanproduks.updated_at', 'LIKE', "%$search%");
            });
        }

        $sortBy = $request->get('sort_by', 'racikanproduks.racikans_id');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'id_racikan':
                $query->orderBy('racikanproduks.racikans_id', $sortOrder);
                break;
            case 'id_produk':
                $query->orderBy('racikanproduks.produks_id', $sortOrder);
                break;
            case 'nama_produk':
                $query->orderBy('produks.nama', $sortOrder);
                break;
            case 'nama_racikan':
                $query->orderBy('racikans.nama', $sortOrder); //bisa ga kepakai di view
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        $datas = $query->paginate(8);
        // $a = GeneralModel::generateIDBatch(1);


        return view('racikan.komposisi', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'komposisi' => $data,
            'search' => $search
        ]);
    }

    private function getProdukTersedia()
    {
        return Produk::query()
            ->select(
                'produks.id',
                'produks.nama',
                'produks.golongan',
                DB::raw('COALESCE(SUM(produkbatches.stok), 0) as total_stok')
            )
            ->leftJoin('produkbatches', function ($join) {
                $join->on('produks.id', '=', 'produkbatches.produks_id')
                    ->where('produkbatches.status', 'tersedia')
                    ->where('produkbatches.stok', '>', 0)
                    ->where(function ($q) {
                        $q->whereDate('produkbatches.tgl_kadaluarsa', '>', now())
                            ->orWhereNull('produkbatches.tgl_kadaluarsa');
                    });
            })
            ->groupBy('produks.id', 'produks.nama', 'produks.golongan')
            ->having('total_stok', '>', 0)
            ->orderBy('produks.nama')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $racikans = Racikan::all();
        $produks = $this->getProdukTersedia();
    
        return view('racikan.create', [
            'racikans' => $racikans,
            'produks' => $produks,
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nama_dokter' => 'nullable|string|max:255',
            'alamat_dokter' => 'nullable|string',
            'nama_pasien' => 'nullable|string|max:255',
            'alamat_pasien' => 'nullable|string',
            'aturan_pakai' => 'required|string',
            'tgl_ambil' => 'nullable|date',
            'biaya_embalase' => 'required|numeric|min:0',
            'bukti_resep' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    
            'produks_id' => 'required|array|min:1',
            'produks_id.*' => 'required|exists:produks,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:1',
        ]);
    
        DB::beginTransaction();
    
        try {
            $buktiResepPath = null;
    
            if ($request->hasFile('bukti_resep')) {
                $buktiResepPath = $request->file('bukti_resep')->store('resep', 'public');
            }
    
            $racikan = Racikan::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'nama_dokter' => $request->nama_dokter,
                'alamat_dokter' => $request->alamat_dokter,
                'nama_pasien' => $request->nama_pasien,
                'alamat_pasien' => $request->alamat_pasien,
                'aturan_pakai' => $request->aturan_pakai,
                'tgl_ambil' => $request->tgl_ambil,
                'biaya_embalase' => $request->biaya_embalase,
                'bukti_resep' => $buktiResepPath,
            ]);
    
            foreach ($request->produks_id as $index => $produkId) {
                $qty = (float) ($request->quantity[$index] ?? 0);
    
                if ($produkId && $qty > 0) {
                    Racikanproduk::create([
                        'racikans_id' => $racikan->id,
                        'produks_id' => $produkId,
                        'quantity' => $qty,
                    ]);
                }
            }
    
            DB::commit();
    
            return redirect()
                ->route('racikans.index')
                ->with('status', 'Racikan berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
    
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat racikan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman checkout/pembayaran racikan.
     * Racikan tetap dibuat di menu racikan, tetapi pembayaran dibuat seperti kasir penjualan produk.
     */
    public function checkoutRacikan($id)
    {
        try {
            $racikan = Racikan::with('racikanproduks.produk')->findOrFail($id);
            $detail = $this->hitungDetailRacikan($racikan, false);

            return view('racikan.checkout', [
                'racikan' => $racikan,
                'detail' => $detail,
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->route('racikans.index')
                ->with('error', 'Racikan belum bisa dijual: ' . $e->getMessage());
        }
    }

    /**
     * Route lama tetap dipertahankan agar tombol/form lama tidak error.
     * Jika belum membawa data pembayaran, arahkan ke halaman checkout racikan.
     */
    public function jualRacikan($id, Request $request)
    {
        if (!$request->has('metode_bayar') && !$request->has('nominal_bayar')) {
            return redirect()->route('racikans.checkout', $id);
        }

        return $this->bayarRacikan($id, $request);
    }

    /**
     * Proses pembayaran dan penjualan racikan.
     */
    public function bayarRacikan($id, Request $request)
    {
        $request->validate([
            'metode_bayar' => 'required|in:tunai,transfer',
            'nominal_bayar' => 'nullable|numeric|min:0',
        ]);

        $pegawaiId = $request->input('pegawai_id') ?? auth()->id();

        if (!$pegawaiId) {
            return back()->withInput()->with('error', 'Pegawai ID wajib ada.');
        }

        DB::beginTransaction();

        try {
            $racikan = Racikan::with('racikanproduks.produk')->findOrFail($id);

            $this->pastikanRacikanBisaDijual($racikan);

            // Hitung ulang saat pembayaran agar harga mengikuti HPP dan stok terbaru.
            $detail = $this->hitungDetailRacikan($racikan, true);
            $totalRacikan = round((float) $detail['total_racikan'], 0);

            $metodeBayar = strtolower($request->input('metode_bayar', 'tunai'));
            if (!in_array($metodeBayar, ['tunai', 'transfer'])) {
                $metodeBayar = 'tunai';
            }

            $nominalBayar = (float) $request->input('nominal_bayar', 0);

            if ($metodeBayar === 'transfer') {
                $nominalBayar = $totalRacikan;
            }

            if ($nominalBayar < $totalRacikan) {
                throw new \Exception('Nominal pembayaran kurang dari total racikan.');
            }

            $kembalian = round($nominalBayar - $totalRacikan, 0);

            $notajual = Notajual::create([
                'pegawai_id' => $pegawaiId,
                'total_bayar' => $totalRacikan,
                'nominal_bayar' => $nominalBayar,
                'kembalian' => $kembalian,
                'metode_bayar' => $metodeBayar,
            ]);

            foreach ($detail['items'] as $item) {
                $batch = Produkbatches::where('id', $item['batch_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$batch || (float) $batch->stok < (float) $item['qty']) {
                    throw new \Exception('Stok bahan racikan berubah atau tidak mencukupi. Silakan ulangi pembayaran.');
                }

                $batch->decrement('stok', (float) $item['qty']);

                DB::table('notajuals_has_produks')->insert([
                    'notajuals_id' => $notajual->id,
                    'produkbatches_id' => $item['batch_id'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['subtotal_jual'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('notajuals_has_racikans')->insert([
                'notajuals_id' => $notajual->id,
                'racikans_id' => $racikan->id,
                'quantity' => 1,
                // Embalase saja agar bahan tidak double count di laporan laba rugi.
                'subtotal' => $detail['biaya_embalase'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Catat log aktivitas sesuai dengan Activity Diagram
            $namaPegawai = \App\Models\User::find($pegawaiId)?->nama ?? 'Unknown';
            \App\Models\LogActivity::catat(
                'penjualan_racikan',
                'Transaksi Penjualan Racikan',
                'Nota jual racikan #' . $notajual->id . ' berhasil dibayar oleh ' . $namaPegawai . '. Total: Rp ' . number_format($totalRacikan, 0, ',', '.')
            );

            DB::commit();

            return redirect()
                ->route('notajuals.index')
                ->with('status', 'Pembayaran racikan berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menjual racikan: ' . $e->getMessage());
        }
    }

    /**
     * Validasi racikan yang memakai obat keras/narkotika/psikotropika harus punya bukti resep.
     */
    private function pastikanRacikanBisaDijual(Racikan $racikan): void
    {
        $butuhResep = $racikan->racikanproduks()
            ->join('produks', 'racikanproduks.produks_id', '=', 'produks.id')
            ->whereNull('racikanproduks.deleted_at')
            ->whereIn(DB::raw('LOWER(produks.golongan)'), ['keras', 'narkotika', 'psikotropika'])
            ->exists();

        if ($butuhResep && empty($racikan->bukti_resep)) {
            throw new \Exception('Racikan ini membutuhkan bukti resep sebelum bisa dijual.');
        }
    }

    /**
     * Menghitung total racikan dari komposisi bahan menggunakan batch FEFO.
     * sellingprice pada produk dipakai sebagai markup persen.
     */
    private function hitungDetailRacikan(Racikan $racikan, bool $lockBatch = false): array
    {
        $komposisi = $racikan->racikanproduks()
            ->with('produk')
            ->whereNull('deleted_at')
            ->get();

        if ($komposisi->isEmpty()) {
            throw new \Exception('Komposisi racikan masih kosong.');
        }

        $items = [];
        $ringkasanProduk = [];
        $totalHargaBahanJual = 0;
        $totalModalBahan = 0;

        foreach ($komposisi as $komponen) {
            $produk = $komponen->produk;

            if (!$produk) {
                throw new \Exception('Ada komposisi racikan yang produknya tidak ditemukan.');
            }

            $requiredQty = (float) $komponen->quantity;
            if ($requiredQty <= 0) {
                continue;
            }

            $availableStock = Produkbatches::where('produks_id', $produk->id)
                ->where('stok', '>', 0)
                ->where('status', 'tersedia')
                ->where(function ($q) {
                    $q->whereDate('tgl_kadaluarsa', '>', now())
                        ->orWhereNull('tgl_kadaluarsa');
                })
                ->sum('stok');

            if ($availableStock < $requiredQty) {
                throw new \Exception("Stok tidak mencukupi untuk produk: {$produk->nama}. Dibutuhkan: {$requiredQty}, tersedia: {$availableStock}.");
            }

            $sisa = $requiredQty;
            $markupPersen = (float) ($produk->sellingprice ?? 0);
            $totalProdukJual = 0;
            $totalProdukModal = 0;

            $batchQuery = Produkbatches::where('produks_id', $produk->id)
                ->where('stok', '>', 0)
                ->where('status', 'tersedia')
                ->where(function ($q) {
                    $q->whereDate('tgl_kadaluarsa', '>', now())
                        ->orWhereNull('tgl_kadaluarsa');
                })
                ->orderByRaw('tgl_kadaluarsa IS NULL, tgl_kadaluarsa ASC')
                ->orderBy('id', 'asc');

            if ($lockBatch) {
                $batchQuery->lockForUpdate();
            }

            $batches = $batchQuery->get();

            foreach ($batches as $batch) {
                if ($sisa <= 0) {
                    break;
                }

                $qtyKeluar = min($sisa, (float) $batch->stok);
                $hppBahan = (float) ($batch->hpp_avg_per_unit ?: $batch->unitprice ?: 0);

                if ($hppBahan <= 0) {
                    throw new \Exception("HPP/modal untuk produk {$produk->nama} belum valid.");
                }

                $hargaJualBahan = round($hppBahan + ($hppBahan * $markupPersen / 100), 0);
                $subtotalJualBahan = $qtyKeluar * $hargaJualBahan;
                $subtotalModalBahan = $qtyKeluar * $hppBahan;

                $items[] = [
                    'produk_id' => $produk->id,
                    'produk_nama' => $produk->nama,
                    'golongan' => $produk->golongan,
                    'batch_id' => $batch->id,
                    'qty' => $qtyKeluar,
                    'hpp' => $hppBahan,
                    'markup_persen' => $markupPersen,
                    'harga_jual' => $hargaJualBahan,
                    'subtotal_jual' => $subtotalJualBahan,
                    'subtotal_modal' => $subtotalModalBahan,
                ];

                $totalProdukJual += $subtotalJualBahan;
                $totalProdukModal += $subtotalModalBahan;
                $totalHargaBahanJual += $subtotalJualBahan;
                $totalModalBahan += $subtotalModalBahan;
                $sisa -= $qtyKeluar;
            }

            if ($sisa > 0) {
                throw new \Exception("Stok tidak mencukupi saat memproses produk: {$produk->nama}.");
            }

            $ringkasanProduk[] = [
                'produk_id' => $produk->id,
                'produk_nama' => $produk->nama,
                'golongan' => $produk->golongan,
                'qty' => $requiredQty,
                'markup_persen' => $markupPersen,
                'subtotal_jual' => $totalProdukJual,
                'subtotal_modal' => $totalProdukModal,
            ];
        }

        $biayaEmbalase = (float) ($racikan->biaya_embalase ?? 0);
        $totalRacikan = round($totalHargaBahanJual + $biayaEmbalase, 0);

        return [
            'items' => $items,
            'ringkasan_produk' => $ringkasanProduk,
            'total_bahan_jual' => round($totalHargaBahanJual, 0),
            'total_modal_bahan' => round($totalModalBahan, 0),
            'biaya_embalase' => round($biayaEmbalase, 0),
            'total_racikan' => $totalRacikan,
        ];
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
    public function edit($id)
    {
        $data = Racikan::findOrFail($id);
        $produks = $this->getProdukTersedia();
    
        $komposisi = $data->racikanproduks()
            ->with('produk')
            ->whereNull('deleted_at')
            ->get();
    
        return view('racikan.edit', [
            'datas' => $data,
            'produks' => $produks,
            'komposisi' => $komposisi,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     $racikan = Racikan::findOrFail($id);
    //     $racikan->nama = $request->get('nama');
    //     $racikan->deskripsi = $request->get('deskripsi');
    //     $racikan->aturan_pakai = $request->get('aturan_pakai');
    //     $racikan->save();

    //     $produks_ids = $request->input('produks_id', []);
    //     $quantities = $request->input('quantity', []);
    //     $processed_ids = [];

    //     for ($i = 0; $i < count($produks_ids); $i++) {
    //         $produks_id = $produks_ids[$i];
    //         $quantity = $quantities[$i];

    //         // Avoid duplicate entries in form input
    //         if (in_array($produks_id, $processed_ids)) {
    //             continue;
    //         }
    //         $processed_ids[] = $produks_id;

    //         $existing = Racikanproduk::where('racikans_id', $racikan->id)
    //             ->where('produks_id', $produks_id)
    //             ->first();

    //         if ($existing) {
    //             $existing->quantity = $quantity;
    //             $existing->save();
    //         } else {
    //             Racikanproduk::create([
    //                 'racikans_id' => $racikan->id,
    //                 'produks_id' => $produks_id,
    //                 'quantity' => $quantity,
    //             ]);
    //         }
    //     }

    //     // HARD DELETE entries that are no longer in the form
    //     Racikanproduk::where('racikans_id', $racikan->id)
    //         ->whereNotIn('produks_id', $processed_ids)
    //         ->delete();

    //     return redirect('racikans')->with('status', 'Racikan dan komposisi berhasil diperbarui');
    // }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nama_dokter' => 'nullable|string|max:255',
            'alamat_dokter' => 'nullable|string',
            'nama_pasien' => 'nullable|string|max:255',
            'alamat_pasien' => 'nullable|string',
            'aturan_pakai' => 'required|string',
            'tgl_ambil' => 'nullable|date',
            'biaya_embalase' => 'required|numeric|min:0',
            'bukti_resep' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    
            'produks_id' => 'required|array|min:1',
            'produks_id.*' => 'required|exists:produks,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:1',
        ]);
    
        DB::beginTransaction();
    
        try {
            $racikan = Racikan::findOrFail($id);
    
            $buktiResepPath = $racikan->bukti_resep;
    
            if ($request->hasFile('bukti_resep')) {
                if (!empty($racikan->bukti_resep)) {
                    Storage::disk('public')->delete($racikan->bukti_resep);
                }
    
                $buktiResepPath = $request->file('bukti_resep')->store('resep', 'public');
            }
    
            $racikan->update([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'nama_dokter' => $request->nama_dokter,
                'alamat_dokter' => $request->alamat_dokter,
                'nama_pasien' => $request->nama_pasien,
                'alamat_pasien' => $request->alamat_pasien,
                'aturan_pakai' => $request->aturan_pakai,
                'tgl_ambil' => $request->tgl_ambil,
                'biaya_embalase' => $request->biaya_embalase,
                'bukti_resep' => $buktiResepPath,
            ]);
    
            $processedProduksIds = [];
    
            foreach ($request->produks_id as $index => $produkId) {
                $qty = (float) ($request->quantity[$index] ?? 0);
    
                if (!$produkId || $qty <= 0) {
                    continue;
                }
    
                if (in_array($produkId, $processedProduksIds)) {
                    continue;
                }
    
                $processedProduksIds[] = $produkId;
    
                $existing = DB::table('racikanproduks')
                    ->where('racikans_id', $racikan->id)
                    ->where('produks_id', $produkId)
                    ->first();
    
                if ($existing) {
                    DB::table('racikanproduks')
                        ->where('racikans_id', $racikan->id)
                        ->where('produks_id', $produkId)
                        ->update([
                            'deleted_at' => null,
                            'quantity' => $qty,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('racikanproduks')->insert([
                        'racikans_id' => $racikan->id,
                        'produks_id' => $produkId,
                        'quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
    
            DB::table('racikanproduks')
                ->where('racikans_id', $racikan->id)
                ->whereNotIn('produks_id', $processedProduksIds)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
    
            DB::commit();
    
            return redirect()
                ->route('racikans.index')
                ->with('status', 'Racikan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
    
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui racikan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = Racikan::find($id);
            $deletedData->delete();
            return redirect('racikans')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('racikans')->with('status', $msg);
        }
    }

    public function destroyKomposisi($racikans_id, $produks_id)
    {
        // First, check if the entry exists
        $komposisi = DB::table('racikanproduks')
            ->where('racikans_id', $racikans_id)
            ->where('produks_id', $produks_id)
            ->first();

        if (!$komposisi) {
            return redirect()->route('racikans.komposisi', ['id' => $racikans_id])
                ->with('status', 'Composition not found.');
        }

        try {
            // Soft delete manually (since Eloquent can't handle composite keys well)
            DB::table('racikanproduks')
                ->where('racikans_id', $racikans_id)
                ->where('produks_id', $produks_id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            return redirect()->route('racikans.komposisi', ['id' => $racikans_id])
                ->with('status', 'Composition successfully deleted!');
        } catch (\Throwable $ex) {
            return redirect()->route('racikans.komposisi', ['id' => $racikans_id])
                ->with('status', 'Failed to delete! Make sure there are no related records.');
        }
    }

    // untuk obat narkotika

    private function narkotikaQuery()
    {
        /*
        |--------------------------------------------------------------------------
        | Sumber data utama: notajuals_has_produks
        |--------------------------------------------------------------------------
        | Karena stok narkotika/psikotropika yang benar-benar keluar dicatat sebagai
        | produk/bahan di notajuals_has_produks, bukan dari notajuals_has_racikans.
        */
        $sql = <<<SQL
            SELECT
                nj.id AS notajual_id,
                COALESCE(r.id, 0) AS racikan_id,
                pb.id AS batch_id,
                p.id AS produk_id,
                p.kode_produk,
                p.nama AS nama_produk,
                s.nama AS nama_satuan,
                d.nama AS nama_distributor,
    
                (
                    pb.stok
                    + (
                        SELECT COALESCE(SUM(njp2.quantity), 0)
                        FROM notajuals_has_produks njp2
                        INNER JOIN notajuals nj2 ON nj2.id = njp2.notajuals_id
                        WHERE njp2.produkbatches_id = pb.id
                          AND nj2.created_at >= DATE_FORMAT(nj.created_at, '%Y-%m-01')
                          AND nj2.deleted_at IS NULL
                    )
                    - (
                        SELECT COALESCE(SUM(nbp.quantity), 0)
                        FROM notabelis_has_produks nbp
                        INNER JOIN notabelis nb ON nb.id = nbp.notabelis_id
                        WHERE nbp.produkbatches_id = pb.id
                          AND nb.created_at >= DATE_FORMAT(nj.created_at, '%Y-%m-01')
                    )
                ) AS stok_awalbulan,
    
                (
                    SELECT COALESCE(SUM(nbp.quantity), 0)
                    FROM notabelis_has_produks nbp
                    INNER JOIN notabelis nb ON nb.id = nbp.notabelis_id
                    WHERE nbp.produkbatches_id = pb.id
                      AND nb.created_at >= DATE_FORMAT(nj.created_at, '%Y-%m-01')
                      AND nb.created_at < DATE_ADD(LAST_DAY(nj.created_at), INTERVAL 1 DAY)
                ) AS stok_diterima,
    
                njp.quantity AS stok_keluar,
    
                (
                    pb.stok
                    + (
                        SELECT COALESCE(SUM(njp2.quantity), 0)
                        FROM notajuals_has_produks njp2
                        INNER JOIN notajuals nj2 ON nj2.id = njp2.notajuals_id
                        WHERE njp2.produkbatches_id = pb.id
                          AND nj2.created_at > nj.created_at
                          AND nj2.deleted_at IS NULL
                    )
                    - (
                        SELECT COALESCE(SUM(nbp.quantity), 0)
                        FROM notabelis_has_produks nbp
                        INNER JOIN notabelis nb ON nb.id = nbp.notabelis_id
                        WHERE nbp.produkbatches_id = pb.id
                          AND nb.created_at > nj.created_at
                    )
                ) AS stok_setelah_transaksi,
    
                COALESCE(r.nama_pasien, nj.nama_pasien, '-') AS nama_pasien,
                COALESCE(r.alamat_pasien, nj.alamat_pasien, '-') AS alamat_pasien,
                COALESCE(r.nama_dokter, nj.nama_dokter, '-') AS nama_dokter,
                COALESCE(r.alamat_dokter, nj.alamat_dokter, '-') AS alamat_dokter,
                DATE(nj.created_at) AS tgl_ambil,
                u.nama AS nama_pegawai
    
            FROM notajuals_has_produks njp
            INNER JOIN notajuals nj ON nj.id = njp.notajuals_id
            INNER JOIN produkbatches pb ON pb.id = njp.produkbatches_id
            INNER JOIN produks p ON p.id = pb.produks_id
            LEFT JOIN satuans s ON s.id = pb.satuans_id
            LEFT JOIN distributors d ON d.id = pb.distributors_id
            LEFT JOIN users u ON u.id = nj.pegawai_id
            LEFT JOIN (
                SELECT notajuals_id, MAX(racikans_id) as racikans_id 
                FROM notajuals_has_racikans 
                GROUP BY notajuals_id
            ) njr ON njr.notajuals_id = nj.id
            LEFT JOIN racikans r ON r.id = njr.racikans_id
    
            WHERE p.golongan IN ('narkotika', 'psikotropika')
              AND nj.deleted_at IS NULL
              AND njp.deleted_at IS NULL
        SQL;
    
        return DB::table(DB::raw("({$sql}) as narkotika_data"));
    }
    public function daftarNarkotika(Request $request)
    {
        $query = $this->narkotikaQuery();

        $search = $request->get('search');

        // Gunakan alias kolom karena query adalah derived table
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'LIKE', "%$search%")
                    ->orWhere('nama_pasien', 'LIKE', "%$search%")
                    ->orWhere('nama_dokter', 'LIKE', "%$search%")
                    ->orWhere('nama_pegawai', 'LIKE', "%$search%");
            });
        }

        $sortBy = $request->get('sort_by', 'batch_id');
        $sortOrder = $request->get('sort_order', 'desc');

        // Kolom menggunakan alias dari derived table narkotikaQuery
        switch ($sortBy) {
            case 'racikan_id':
                $query->orderBy('racikan_id', $sortOrder);
                break;

            case 'batch_id':
                $query->orderBy('batch_id', $sortOrder);
                break;

            case 'notajual_id':
                $query->orderBy('notajual_id', $sortOrder);
                break;    


            case 'nama_produk':
                $query->orderBy('nama_produk', $sortOrder);
                break;

            case 'nama_satuan':
                $query->orderBy('nama_satuan', $sortOrder);
                break;

            case 'nama_distributor':
                $query->orderBy('nama_distributor', $sortOrder);
                break;

            case 'stok_awalbulan':
                $query->orderBy('stok_awalbulan', $sortOrder);
                break;

            case 'stok_diterima':
                $query->orderBy('stok_diterima', $sortOrder);
                break;

            case 'stok_keluar':
                $query->orderBy('stok_keluar', $sortOrder);
                break;

            case 'stok_akhirbulan':
                $query->orderBy('stok_akhirbulan', $sortOrder);
                break;

            case 'nama_pasien':
                $query->orderBy('nama_pasien', $sortOrder);
                break;

            case 'alamat_pasien':
                $query->orderBy('alamat_pasien', $sortOrder);
                break;

            case 'nama_dokter':
                $query->orderBy('nama_dokter', $sortOrder);
                break;

            case 'alamat_dokter':
                $query->orderBy('alamat_dokter', $sortOrder);
                break;

            case 'tgl_ambil':
                $query->orderBy('tgl_ambil', $sortOrder);
                break;

            default:
                $query->orderBy('tgl_ambil', 'desc')
                    ->orderBy('notajual_id', 'desc');
        }

        $datas = $query->paginate(10);

        return view('transaksi.daftarNarkotika', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search
        ]);
    }

    public function reportNarkotika(Request $request)
    {
        $query = $this->narkotikaQuery();

        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if ($bulan || $tahun) {
            $tahun = $tahun ?: now()->year;
            $bulan = $bulan ?: now()->month;
            
            $query->whereYear('tgl_ambil', $tahun);
            if ($bulan !== 'all') {
                $query->whereMonth('tgl_ambil', $bulan);
                $filterLabel = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY');
            } else {
                $filterLabel = 'Tahun ' . $tahun;
            }
            $filter = 'custom';
        } else {
            $filter = $request->get('filter', 'month');
            switch ($filter) {
                case 'week':
                    $query->whereBetween('tgl_ambil', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    $filterLabel = 'Minggu Ini';
                    break;
                case 'month':
                    $query->whereYear('tgl_ambil', now()->year)
                        ->whereMonth('tgl_ambil', now()->month);
                    $filterLabel = 'Bulan Ini';
                    break;
                case 'year':
                    $query->whereYear('tgl_ambil', now()->year);
                    $filterLabel = 'Tahun Ini';
                    break;
                case 'day':
                default:
                    $query->whereDate('tgl_ambil', now());
                    $filterLabel = 'Hari Ini';
            }
        }

        $datas = $query->get();

        $total = $datas->sum('stok_keluar');

        return view('transaksi.reportNarkotika', compact('datas', 'total', 'filter', 'filterLabel'));
    }

    public function reportCsvNarkotika(Request $request)
    {
        $query = $this->narkotikaQuery();

        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if ($bulan || $tahun) {
            $tahun = $tahun ?: now()->year;
            $bulan = $bulan ?: now()->month;
            
            $query->whereYear('tgl_ambil', $tahun);
            if ($bulan !== 'all') {
                $query->whereMonth('tgl_ambil', $bulan);
            }
        } else {
            $filter = $request->get('filter', 'month');
            switch ($filter) {
                case 'week':
                    $query->whereBetween('tgl_ambil', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereYear('tgl_ambil', now()->year)
                        ->whereMonth('tgl_ambil', now()->month);
                    break;
                case 'year':
                    $query->whereYear('tgl_ambil', now()->year);
                    break;
                case 'day':
                default:
                    if ($filter !== 'all') {
                        $query->whereDate('tgl_ambil', now());
                    }
            }
        }

        $datas = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan_narkotika.csv"',
        ];

        $callback = function () use ($datas) {

            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($file, "sep=,\n"); // Petunjuk separator untuk Excel

            // Header
            fputcsv($file, [
                'Racikan ID',
                'Batch ID',
                'Nama Obat',
                'Satuan',
                'Stok Awal Bulan',
                'Distributor',
                'Jumlah Diterima',
                'Jumlah Dipakai',
                'Stok Akhir Bulan',
                'Nama Pasien',
                'Alamat Pasien',
                'Nama Dokter',
                'Alamat Dokter',
                'Tanggal Ambil'
            ]);

            foreach ($datas as $d) {

                fputcsv($file, [
                    $d->racikan_id,
                    $d->batch_id,
                    $d->nama_produk,
                    $d->nama_satuan,
                    $d->stok_awalbulan,
                    $d->nama_distributor,
                    $d->stok_diterima,
                    $d->stok_keluar,
                    $d->stok_setelah_transaksi,
                    $d->nama_pasien,
                    $d->alamat_pasien,
                    $d->nama_dokter,
                    $d->alamat_dokter,
                    $d->tgl_ambil
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printNarkotika($id)
    {
        $datas = $this->narkotikaQuery()
            ->where('notajual_id', $id)
            ->get();

        if ($datas->isEmpty()) {
            abort(404, 'Data nota narkotika tidak ditemukan.');
        }

        return view('transaksi.nnPrint', compact('datas'));
    }

    /*
    |--------------------------------------------------------------------------
    | EKSPOR SIPNAP (Sistem Pelaporan Narkotika dan Psikotropika - BPOM)
    |--------------------------------------------------------------------------
    */
    public function exportSipnap(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = $this->narkotikaQuery()->whereYear('tgl_ambil', $tahun);
        
        if ($bulan !== 'all') {
            $query->whereMonth('tgl_ambil', $bulan);
            $namaBulan = \Carbon\Carbon::createFromDate((int)$tahun, (int)$bulan, 1)->locale('id')->isoFormat('MMMM');
            $periodeTitle = strtoupper("PELAPORAN NARKOTIKA PERIODE {$namaBulan} {$tahun}");
            $filename  = "SIPNAP_{$namaBulan}_{$tahun}.csv";
        } else {
            $namaBulan = "Setahun";
            $periodeTitle = strtoupper("PELAPORAN NARKOTIKA TAHUN {$tahun}");
            $filename  = "SIPNAP_TAHUN_{$tahun}.csv";
        }

        $datas = $query->get();

        // Kelompokkan per produk untuk rekap bulanan SIPNAP
        $grouped = $datas->groupBy('nama_produk')->map(function ($rows) {
            $first = $rows->first();
            return [
                'kode_obat'   => $first->kode_produk ?? '-',
                'nama_obat'   => $first->nama_produk ?? '-',
                'satuan'      => $first->nama_satuan ?? '-',
                'stok_awal'   => $first->stok_awalbulan ?? 0,
                'penerimaan'  => $first->stok_diterima ?? 0,
                'pengeluaran' => $rows->sum('stok_keluar'),
                'stok_akhir'  => ($first->stok_awalbulan ?? 0) + ($first->stok_diterima ?? 0) - $rows->sum('stok_keluar'),
                'keterangan'  => 'Apotek Medico',
            ];
        })->values();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($grouped, $namaBulan, $tahun, $periodeTitle) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fwrite($file, "sep=,\n"); // Petunjuk separator untuk Excel

            // Spasi atas seperti di gambar
            fputcsv($file, []);

            // Judul Utama (Diletakkan di kolom C-E agar terlihat di tengah jika dibuka di Excel tanpa merge)
            fputcsv($file, ['', '', '', "{$periodeTitle} - APOTEK MEDICO"]);
            fputcsv($file, []); // Baris kosong

            // Header Baris 1 (Grup diletakkan di kolom pertama dari span)
            fputcsv($file, [
                'Produk', '', '',
                'Stok Awal',
                'Jumlah Pemasukan', '',
                'Jumlah Pengeluaran', '',
                'Pemusnahan', '', '',
                'Stok Akhir'
            ]);

            // Header Baris 2 (Sub-kolom)
            fputcsv($file, [
                'Kode', 'Nama', 'Satuan',
                '', // Bawah stok awal
                'Dari PBF', 'Dari Sarana',
                'Untuk Resep', 'Untuk Sarana',
                'Jumlah', 'Nomor BAP', 'Tanggal BAP',
                '' // Bawah stok akhir
            ]);

            // Data
            foreach ($grouped as $row) {
                fputcsv($file, [
                    $row['kode_obat'], // Kode
                    $row['nama_obat'],
                    $row['satuan'],
                    $row['stok_awal'],
                    $row['penerimaan'], // Dari PBF
                    '0', // Dari Sarana
                    $row['pengeluaran'], // Untuk Resep
                    '0', // Untuk Sarana
                    '0', // Pemusnahan Jumlah
                    '-', // Nomor BAP
                    '-', // Tanggal BAP
                    $row['stok_akhir']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | EKSPOR SIMONA (Sistem Monitoring Narkotika - Dinkes)
    |--------------------------------------------------------------------------
    */
    public function exportSimona(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = $this->narkotikaQuery()
            ->whereYear('tgl_ambil', $tahun)
            ->orderBy('tgl_ambil');
            
        if ($bulan !== 'all') {
            $query->whereMonth('tgl_ambil', $bulan);
            $namaBulan = \Carbon\Carbon::createFromDate((int)$tahun, (int)$bulan, 1)->locale('id')->isoFormat('MMMM');
            $periode = "{$namaBulan} {$tahun}";
            $filename  = "SIMONA_{$namaBulan}_{$tahun}.csv";
        } else {
            $namaBulan = "Setahun";
            $periode = "Tahun {$tahun}";
            $filename  = "SIMONA_TAHUN_{$tahun}.csv";
        }

        $datas = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($datas, $namaBulan, $tahun, $periode) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fwrite($file, "sep=,\n"); // Petunjuk separator untuk Excel

            fputcsv($file, ['LAPORAN SIMONA - MONITORING NARKOTIKA DAN PSIKOTROPIKA']);
            fputcsv($file, ['Nama Apotek', 'Apotek Medico']);
            fputcsv($file, ['Periode Pelaporan', $periode]);
            fputcsv($file, ['Tanggal Ekspor', now()->format('d/m/Y H:i')]);
            fputcsv($file, ['']);

            fputcsv($file, [
                'No', 'Tanggal Pemakaian', 'No Nota',
                'Nama Obat', 'Satuan', 'Distributor',
                'Jumlah Dipakai', 'Stok Akhir',
                'Nama Pasien', 'Alamat Pasien',
                'Nama Dokter', 'Alamat Dokter',
            ]);

            foreach ($datas as $i => $d) {
                fputcsv($file, [
                    $i + 1,
                    !empty($d->tgl_ambil) ? \Carbon\Carbon::parse($d->tgl_ambil)->format('d/m/Y') : '-',
                    'NJ#' . ($d->notajual_id ?? '-'),
                    $d->nama_produk ?? '-',
                    $d->nama_satuan ?? '-',
                    $d->nama_distributor ?? '-',
                    $d->stok_keluar ?? 0,
                    $d->stok_setelah_transaksi ?? 0,
                    $d->nama_pasien ?? '-',
                    $d->alamat_pasien ?? '-',
                    $d->nama_dokter ?? '-',
                    $d->alamat_dokter ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}