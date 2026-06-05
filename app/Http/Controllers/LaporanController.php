<?php

namespace App\Http\Controllers;

use App\Models\HppRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Laporan Laba Rugi Kotor.
     *
     * Konsep:
     * Total Penjualan = Penjualan produk/bahan + biaya embalase racikan
     * Total HPP       = Qty produk/bahan keluar x HPP per unit
     * Laba Kotor      = Total Penjualan - Total HPP
     */
    public function labaRugi(Request $request)
    {
        $filter    = $request->get('filter', 'month');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        /*
         * HPP fallback:
         * 1. pakai hpp_avg_per_unit jika ada dan bukan 0
         * 2. kalau 0, pakai unitprice batch
         * 3. kalau masih kosong, 0
         */
        $hppExpression = "COALESCE(NULLIF(pb.hpp_avg_per_unit, 0), NULLIF(pb.unitprice, 0), 0)";

        /*
        |--------------------------------------------------------------------------
        | Detail transaksi produk/bahan
        |--------------------------------------------------------------------------
        */
        $query = DB::table('notajuals_has_produks as njp')
            ->join('notajuals as nj', 'nj.id', '=', 'njp.notajuals_id')
            ->join('produkbatches as pb', 'pb.id', '=', 'njp.produkbatches_id')
            ->join('produks as p', 'p.id', '=', 'pb.produks_id')
            ->leftJoin('distributors as d', 'd.id', '=', 'pb.distributors_id')
            ->whereNull('njp.deleted_at')
            ->whereNull('nj.deleted_at');

        $this->applyDateFilter($query, $filter, $startDate, $endDate);

        $items = $query->select(
            'p.id as produk_id',
            'p.nama as nama_produk',
            'p.sellingprice as markup_persen',
            DB::raw('SUM(njp.quantity) as total_qty'),
            DB::raw('SUM(njp.subtotal) as total_penjualan'),
            DB::raw("MAX($hppExpression) as hpp_per_unit"),
            DB::raw("SUM(njp.quantity * $hppExpression) as total_hpp"),
            DB::raw("SUM(njp.subtotal) - SUM(njp.quantity * $hppExpression) as laba_kotor"),
            DB::raw('DATE(nj.created_at) as tanggal')
        )
        ->groupBy(
            'p.id',
            'p.nama',
            'p.sellingprice',
            DB::raw('DATE(nj.created_at)')
        )
        ->orderBy('tanggal', 'desc')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan per produk
        |--------------------------------------------------------------------------
        */
        $perProduk = DB::table('notajuals_has_produks as njp')
            ->join('notajuals as nj', 'nj.id', '=', 'njp.notajuals_id')
            ->join('produkbatches as pb', 'pb.id', '=', 'njp.produkbatches_id')
            ->join('produks as p', 'p.id', '=', 'pb.produks_id')
            ->whereNull('njp.deleted_at')
            ->whereNull('nj.deleted_at');

        $this->applyDateFilter($perProduk, $filter, $startDate, $endDate);

        $summaryProduk = $perProduk->select(
            'p.id as produk_id',
            'p.nama as nama_produk',
            DB::raw('SUM(njp.quantity) as total_qty'),
            DB::raw('SUM(njp.subtotal) as total_penjualan'),
            DB::raw("SUM(njp.quantity * $hppExpression) as total_hpp"),
            DB::raw("SUM(njp.subtotal) - SUM(njp.quantity * $hppExpression) as laba_kotor")
        )
        ->groupBy('p.id', 'p.nama')
        ->orderByDesc('laba_kotor')
        ->get()
        ->map(function ($row) {
            $row->total_qty = (float) $row->total_qty;
            $row->total_penjualan = (float) $row->total_penjualan;
            $row->total_hpp = (float) $row->total_hpp;
            $row->laba_kotor = (float) $row->laba_kotor;

            $row->margin = $row->total_penjualan > 0
                ? ($row->laba_kotor / $row->total_penjualan) * 100
                : 0;

            return $row;
        });

        /*
        |--------------------------------------------------------------------------
        | Embalase racikan
        |--------------------------------------------------------------------------
        | Setelah revisi jual racikan:
        | notajuals_has_racikans.subtotal = biaya embalase saja.
        | Bahan racikan sudah masuk ke notajuals_has_produks.
        |--------------------------------------------------------------------------
        */
        $embalaseQuery = DB::table('notajuals_has_racikans as njr')
            ->join('notajuals as nj', 'nj.id', '=', 'njr.notajuals_id')
            ->whereNull('nj.deleted_at');

        $this->applyDateFilter($embalaseQuery, $filter, $startDate, $endDate);

        $totalEmbalaseRacikan = (float) $embalaseQuery->sum('njr.subtotal');

        /*
        |--------------------------------------------------------------------------
        | Total laporan
        |--------------------------------------------------------------------------
        */
        $totalPenjualanProduk = (float) $summaryProduk->sum('total_penjualan');
        $totalHpp             = (float) $summaryProduk->sum('total_hpp');

        $totalPenjualan = $totalPenjualanProduk + $totalEmbalaseRacikan;
        $totalLaba      = $totalPenjualan - $totalHpp;

        $marginTotal = $totalPenjualan > 0
            ? ($totalLaba / $totalPenjualan) * 100
            : 0;

        $hppHistory = HppRecord::with('produk')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('laporan.labarugi', compact(
            'items',
            'summaryProduk',
            'totalPenjualan',
            'totalPenjualanProduk',
            'totalEmbalaseRacikan',
            'totalHpp',
            'totalLaba',
            'marginTotal',
            'hppHistory',
            'filter',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export Laba Rugi ke CSV.
     */
    public function labaRugiCsv(Request $request)
    {
        $filter    = $request->get('filter', 'month');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $hppExpression = "COALESCE(NULLIF(pb.hpp_avg_per_unit, 0), NULLIF(pb.unitprice, 0), 0)";

        $query = DB::table('notajuals_has_produks as njp')
            ->join('notajuals as nj', 'nj.id', '=', 'njp.notajuals_id')
            ->join('produkbatches as pb', 'pb.id', '=', 'njp.produkbatches_id')
            ->join('produks as p', 'p.id', '=', 'pb.produks_id')
            ->whereNull('njp.deleted_at')
            ->whereNull('nj.deleted_at');

        $this->applyDateFilter($query, $filter, $startDate, $endDate);

        $data = $query->select(
            'p.nama as nama_produk',
            DB::raw('SUM(njp.quantity) as total_qty'),
            DB::raw('SUM(njp.subtotal) as total_penjualan'),
            DB::raw("SUM(njp.quantity * $hppExpression) as total_hpp"),
            DB::raw("SUM(njp.subtotal) - SUM(njp.quantity * $hppExpression) as laba_kotor")
        )
        ->groupBy('p.id', 'p.nama')
        ->orderBy('p.nama')
        ->get();

        $embalaseQuery = DB::table('notajuals_has_racikans as njr')
            ->join('notajuals as nj', 'nj.id', '=', 'njr.notajuals_id')
            ->whereNull('nj.deleted_at');

        $this->applyDateFilter($embalaseQuery, $filter, $startDate, $endDate);

        $totalEmbalaseRacikan = (float) $embalaseQuery->sum('njr.subtotal');

        $filename = 'laporan_labarugi_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data, $totalEmbalaseRacikan) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Nama Produk',
                'Total Qty Terjual',
                'Total Penjualan (Rp)',
                'Total HPP (Rp)',
                'Laba Kotor (Rp)',
                'Margin (%)',
            ], ',');

            $grandTotalQty = 0;
            $grandTotalPenjualan = $totalEmbalaseRacikan;
            $grandTotalHpp = 0;
            $grandTotalLaba = $totalEmbalaseRacikan;

            foreach ($data as $row) {
                $margin = $row->total_penjualan > 0
                    ? (($row->laba_kotor / $row->total_penjualan) * 100)
                    : 0;

                fputcsv($file, [
                    $row->nama_produk,
                    $row->total_qty,
                    number_format($row->total_penjualan, 0, '.', ','),
                    number_format($row->total_hpp, 0, '.', ','),
                    number_format($row->laba_kotor, 0, '.', ','),
                    number_format($margin, 0, '.', ','),
                ], ',');

                $grandTotalQty += $row->total_qty;
                $grandTotalPenjualan += $row->total_penjualan;
                $grandTotalHpp += $row->total_hpp;
                $grandTotalLaba += $row->laba_kotor;
            }

            if ($totalEmbalaseRacikan > 0) {
                fputcsv($file, [
                    'Biaya Embalase Racikan',
                    '-',
                    number_format($totalEmbalaseRacikan, 0, '.', ','),
                    number_format(0, 0, '.', ','),
                    number_format($totalEmbalaseRacikan, 0, '.', ','),
                    number_format(100, 0, '.', ','),
                ], ',');
            }

            $grandMargin = $grandTotalPenjualan > 0 
                ? (($grandTotalLaba / $grandTotalPenjualan) * 100) 
                : 0;

            fputcsv($file, [
                'TOTAL',
                $grandTotalQty,
                number_format($grandTotalPenjualan, 0, '.', ','),
                number_format($grandTotalHpp, 0, '.', ','),
                number_format($grandTotalLaba, 0, '.', ','),
                number_format($grandMargin, 0, '.', ','),
            ], ',');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Filter tanggal laporan.
     */
    private function applyDateFilter($query, $filter, $startDate = null, $endDate = null): void
    {
        if ($startDate && $endDate) {
            $query->whereBetween('nj.created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);

            return;
        }

        switch ($filter) {
            case 'day':
                $query->whereDate('nj.created_at', now()->toDateString());
                break;

            case 'week':
                $query->whereBetween('nj.created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'year':
                $query->whereYear('nj.created_at', now()->year);
                break;

            case 'month':
            default:
                $query->whereYear('nj.created_at', now()->year)
                    ->whereMonth('nj.created_at', now()->month);
                break;
        }
    }
}