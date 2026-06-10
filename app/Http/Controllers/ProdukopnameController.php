<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Produkopnames;
use App\Models\Produkbatches;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukopnameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'tanggal');
        $sortOrder = $request->get('sort_order', 'desc');
        $search = $request->get('search');
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSampai = $request->get('tanggal_sampai');

        $query = DB::table('produkopnames')
            ->leftJoin('produkbatches', 'produkopnames.produkbatches_id', '=', 'produkbatches.id')
            ->leftJoin('produks', 'produks.id', '=', 'produkbatches.produks_id')
            ->leftJoin('satuans', 'satuans.id', '=', 'produkbatches.satuans_id')
            ->leftJoin('gudangs', 'gudangs.id', '=', 'produkbatches.gudangs_id')
            ->leftJoin('users', 'users.id', '=', 'produkopnames.users_id')
            ->whereNull('produkopnames.deleted_at')
            ->select(
                'produkopnames.id',
                'produkopnames.produkbatches_id',
                'produkopnames.tanggal',
                'produkopnames.stok_sistem',
                'produkopnames.stok_fisik',
                'produkopnames.selisih',
                'produkopnames.keterangan',
                'produkopnames.created_at',
                'produkopnames.updated_at',

                'produkbatches.id as batch_id',
                'produks.nama as nama_produk',
                'satuans.nama as nama_satuan',
                'gudangs.lokasi as lokasi_gudang',
                'users.nama as nama_user',

                DB::raw("CONCAT('BATCH-', LPAD(produkbatches.id, 5, '0')) as kode_barang"),

                DB::raw('
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as harga_pokok
                '),

                DB::raw('
                    produkopnames.stok_sistem *
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as nilai_stok_sistem
                '),

                DB::raw('
                    produkopnames.stok_fisik *
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as nilai_stok_fisik
                '),

                DB::raw('
                    produkopnames.selisih *
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as nilai_selisih
                ')
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('produks.nama', 'LIKE', "%$search%")
                    ->orWhere('produkbatches.id', 'LIKE', "%$search%")
                    ->orWhere('satuans.nama', 'LIKE', "%$search%")
                    ->orWhere('gudangs.lokasi', 'LIKE', "%$search%")
                    ->orWhere('users.nama', 'LIKE', "%$search%")
                    ->orWhere('produkopnames.keterangan', 'LIKE', "%$search%");
            });
        }

        if (!empty($tanggalMulai)) {
            $query->whereDate('produkopnames.tanggal', '>=', $tanggalMulai);
        }

        if (!empty($tanggalSampai)) {
            $query->whereDate('produkopnames.tanggal', '<=', $tanggalSampai);
        }

        switch ($sortBy) {
            case 'kode_barang':
                $query->orderBy('produkbatches.id', $sortOrder);
                break;

            case 'nama_produk':
                $query->orderBy('produks.nama', $sortOrder);
                break;

            case 'satuan':
                $query->orderBy('satuans.nama', $sortOrder);
                break;

            case 'harga_pokok':
                $query->orderBy('harga_pokok', $sortOrder);
                break;

            case 'lokasi_gudang':
                $query->orderBy('gudangs.lokasi', $sortOrder);
                break;

            case 'stok_sistem':
                $query->orderBy('produkopnames.stok_sistem', $sortOrder);
                break;

            case 'stok_fisik':
                $query->orderBy('produkopnames.stok_fisik', $sortOrder);
                break;

            case 'selisih':
                $query->orderBy('produkopnames.selisih', $sortOrder);
                break;

            case 'tanggal':
                $query->orderBy('produkopnames.tanggal', $sortOrder);
                break;

            default:
                $query->orderBy('produkopnames.tanggal', 'desc');
                break;
        }

        $datas = $query->paginate(10)->appends($request->query());

        return view('opname.index', [
            'datas' => $datas,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSampai' => $tanggalSampai,
        ]);
    }

    public function reportCsv(Request $request)
    {
        $search = $request->get('search');
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSampai = $request->get('tanggal_sampai');

        $query = DB::table('produkopnames')
            ->leftJoin('produkbatches', 'produkopnames.produkbatches_id', '=', 'produkbatches.id')
            ->leftJoin('produks', 'produks.id', '=', 'produkbatches.produks_id')
            ->leftJoin('satuans', 'satuans.id', '=', 'produkbatches.satuans_id')
            ->leftJoin('gudangs', 'gudangs.id', '=', 'produkbatches.gudangs_id')
            ->leftJoin('users', 'users.id', '=', 'produkopnames.users_id')
            ->whereNull('produkopnames.deleted_at')
            ->select(
                'produkopnames.id',
                'produkopnames.tanggal',
                'produkopnames.stok_sistem',
                'produkopnames.stok_fisik',
                'produkopnames.selisih',
                'produkopnames.keterangan',

                'produkbatches.id as batch_id',
                'produks.nama as nama_produk',
                'satuans.nama as nama_satuan',
                'gudangs.lokasi as lokasi_gudang',
                'users.nama as nama_user',

                DB::raw("CONCAT('BATCH-', LPAD(produkbatches.id, 5, '0')) as kode_barang"),

                DB::raw('
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as harga_pokok
                '),

                DB::raw('
                    produkopnames.stok_sistem *
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as nilai_stok_sistem
                '),

                DB::raw('
                    produkopnames.stok_fisik *
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as nilai_stok_fisik
                '),

                DB::raw('
                    produkopnames.selisih *
                    COALESCE(NULLIF(produkbatches.hpp_avg_per_unit, 0), produkbatches.unitprice, 0)
                    as nilai_selisih
                ')
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('produks.nama', 'LIKE', "%$search%")
                    ->orWhere('produkbatches.id', 'LIKE', "%$search%")
                    ->orWhere('satuans.nama', 'LIKE', "%$search%")
                    ->orWhere('gudangs.lokasi', 'LIKE', "%$search%")
                    ->orWhere('users.nama', 'LIKE', "%$search%")
                    ->orWhere('produkopnames.keterangan', 'LIKE', "%$search%");
            });
        }

        if (!empty($tanggalMulai)) {
            $query->whereDate('produkopnames.tanggal', '>=', $tanggalMulai);
        }

        if (!empty($tanggalSampai)) {
            $query->whereDate('produkopnames.tanggal', '<=', $tanggalSampai);
        }

        $datas = $query
            ->orderBy('produkopnames.tanggal', 'desc')
            ->orderBy('produkopnames.id', 'desc')
            ->get();

        $filename = 'laporan-stok-opname-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($datas) {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel Windows membaca UTF-8 dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No',
                'Tanggal',
                'Kode Barang',
                'Batch ID',
                'Nama Barang',
                'Satuan',
                'Harga Pokok',
                'Lokasi Gudang',
                'Persediaan Sistem - Jumlah',
                'Persediaan Sistem - Nilai',
                'Stok Fisik - Jumlah',
                'Stok Fisik - Nilai',
                'Selisih - Jumlah',
                'Selisih - Nilai',
                'Petugas',
                'Keterangan',
            ]);

            foreach ($datas as $index => $d) {
                fputcsv($handle, [
                    $index + 1,
                    $d->tanggal,
                    $d->kode_barang,
                    $d->batch_id,
                    $d->nama_produk,
                    $d->nama_satuan,
                    number_format($d->harga_pokok, 0, '.', ','),
                    $d->lokasi_gudang,
                    $d->stok_sistem,
                    number_format($d->nilai_stok_sistem, 0, '.', ','),
                    $d->stok_fisik,
                    number_format($d->nilai_stok_fisik, 0, '.', ','),
                    $d->selisih,
                    number_format($d->nilai_selisih, 0, '.', ','),
                    $d->nama_user,
                    $d->keterangan,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $batchs = Produkbatches::with(['produks', 'satuan', 'gudang', 'distributor'])
            ->whereHas('produks', function ($q) {
                if (\Schema::hasColumn('produks', 'deleted_at')) {
                    $q->whereNull('produks.deleted_at');
                }
    
                if (\Schema::hasColumn('produks', 'is_active')) {
                    $q->where('produks.is_active', 1);
                }
            })
            ->whereNotNull('produks_id')
            ->where('status', 'tersedia');
    
        if (\Schema::hasColumn('produkbatches', 'deleted_at')) {
            $batchs->whereNull('deleted_at');
        }
    
        $batchs = $batchs
            ->orderBy('produks_id')
            ->orderBy('tgl_kadaluarsa')
            ->orderBy('id')
            ->get();
    
        return view('opname.create', [
            'batchs' => $batchs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'batch_id'   => 'required|integer',
            'stok_fisik' => 'required|numeric',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        // Ambil batch yang dipilih
        $batch = Produkbatches::findOrFail($request->input('batch_id'));
        // Stok sistem adalah stok saat ini dari batch tersebut
        $stokSistem = $batch->stok;

        $opname = new Produkopnames();
        $opname->produkbatches_id = $batch->id;
        $opname->produks_id       = $batch->produks_id;
        $opname->stok_sistem      = $stokSistem;
        $opname->stok_fisik       = $request->input('stok_fisik');
        $opname->selisih          = $opname->stok_fisik - $opname->stok_sistem;
        $opname->tanggal          = $request->input('tanggal');
        $opname->keterangan       = $request->input('keterangan');
        if ($request->user()) {
            $opname->users_id = $request->user()->id;
        }
        $opname->save();

        // Update stok batch sesuai stok fisik
        $batch->stok = $opname->stok_fisik;
        $batch->save();

        return redirect()->route('opnames.index')->with('status', 'Data stok opname berhasil ditambahkan');
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
        $data = Produkopnames::findOrFail($id);
    
        $batchs = Produkbatches::with(['produks', 'satuan', 'gudang', 'distributor'])
            ->whereHas('produks', function ($q) {
                if (\Schema::hasColumn('produks', 'deleted_at')) {
                    $q->whereNull('produks.deleted_at');
                }
    
                if (\Schema::hasColumn('produks', 'is_active')) {
                    $q->where('produks.is_active', 1);
                }
            })
            ->whereNotNull('produks_id')
            ->where(function ($q) use ($data) {
                $q->where('status', 'tersedia')
                    ->orWhere('id', $data->produkbatches_id);
            });
    
        if (\Schema::hasColumn('produkbatches', 'deleted_at')) {
            $batchs->whereNull('deleted_at');
        }
    
        $batchs = $batchs
            ->orderBy('produks_id')
            ->orderBy('tgl_kadaluarsa')
            ->orderBy('id')
            ->get();
    
        return view('opname.edit', [
            'datas' => $data,
            'batchs' => $batchs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $opname = Produkopnames::findOrFail($id);
        $request->validate([
            'batch_id'   => 'required|integer',
            'stok_fisik' => 'required|numeric',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string',
        ]);
        $batch = Produkbatches::findOrFail($request->input('batch_id'));
        $stokSistem = $batch->stok;
        $opname->produkbatches_id = $batch->id;
        $opname->produks_id       = $batch->produks_id;
        $opname->stok_sistem      = $stokSistem;
        $opname->stok_fisik       = $request->input('stok_fisik');
        $opname->selisih          = $opname->stok_fisik - $opname->stok_sistem;
        $opname->tanggal          = $request->input('tanggal');
        $opname->keterangan       = $request->input('keterangan');
        if ($request->user()) {
            $opname->users_id = $request->user()->id;
        }
        $opname->save();
        // Update stok batch sesuai stok fisik
        $batch->stok = $opname->stok_fisik;
        $batch->save();
        return redirect()->route('opnames.index')->with('status', 'Data stok opname berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = Produkopnames::find($id);
            $deletedData->delete();
            return redirect('opnames')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('opnames')->with('status', $msg);
        }
    }
}
