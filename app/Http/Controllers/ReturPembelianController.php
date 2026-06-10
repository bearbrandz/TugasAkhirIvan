<?php
namespace App\Http\Controllers;
use App\Models\LogActivity;
use App\Models\Notabeli;
use App\Models\Notabeliproduk;
use App\Models\Produkbatches;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianItem;
use App\Services\HppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class ReturPembelianController extends Controller
{
    /**
     * Daftar semua retur pembelian.
     */
    public function index(Request $request)
    {
        $search    = $request->get('search', '');
        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $datas = ReturPembelian::with(['notabeli', 'items.produk'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('notabeli', function ($q2) use ($search) {
                    $q2->where('id', 'like', "%$search%");
                })
                ->orWhere('keterangan', 'like', "%$search%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate(15);
        return view('retur.index', compact('datas', 'search', 'sortBy', 'sortOrder'));
    }
    /**
     * Form step 1: Input nomor nota pembelian.
     */
    public function create()
    {
        return view('retur.create');
    }
    /**
     * Step 2: Cari nota pembelian dan tampilkan item yang bisa diretur.
     */
    public function cariNota(Request $request)
    {
        $request->validate([
            'notabelis_id' => 'required|integer',
        ]);
        $notabeliId = $request->notabelis_id;
        $notabeli   = Notabeli::with(['user'])->find($notabeliId);
        if (!$notabeli) {
            return redirect()->route('retur.create')
                ->withErrors(['notabelis_id' => 'Nota pembelian dengan ID ' . $notabeliId . ' tidak ditemukan.']);
        }
        $items = Notabeliproduk::with(['batch.produks', 'batch.satuan', 'batch.distributor'])
            ->where('notabelis_id', $notabeliId)
            ->whereNull('deleted_at')
            ->get()
            ->filter(function ($item) {
                return $item->batch && $item->batch->status === 'tersedia';
            });
        if ($items->isEmpty()) {
            return redirect()->route('retur.create')
                ->withErrors(['notabelis_id' => 'Nota ini tidak memiliki item yang bisa diretur (status harus tersedia).']);
        }
        return view('retur.form', compact('notabeli', 'items'));
    }
    /**
     * Simpan retur pembelian.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'notabelis_id' => 'required|integer',
                'items' => 'required|array',
                'keterangan' => 'nullable|string',
            ]);
            $nota = Notabeli::findOrFail($request->notabelis_id);
            $items = collect($request->items)
                ->filter(function ($item) {
                    return isset($item['qty_retur']) && (int) $item['qty_retur'] > 0;
                })
                ->values();
            if ($items->isEmpty()) {
                throw new \Exception('Minimal isi 1 item dengan qty retur lebih dari 0.');
            }
            $totalRetur = 0;
            $alasanHeader = $request->input('keterangan') ?: 'rusak';
            $allowedAlasan = ['rusak', 'expired', 'salah_kirim', 'lainnya'];
            if (!in_array($alasanHeader, $allowedAlasan)) {
                $alasanHeader = 'lainnya';
            }
            $datePrefix = now()->format('ymd');
            $countToday = DB::table('retur_pembelians')
                            ->whereDate('created_at', now()->toDateString())
                            ->count();
            $sequence = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
            $noRetur = 'RET-' . $datePrefix . '-' . $sequence;
            $returId = DB::table('retur_pembelians')->insertGetId([
                'no_retur' => $noRetur,
                'notabelis_id' => $nota->id,
                'pegawai_id' => Auth::id() ?? $nota->pegawai_id,
                'tanggal_retur' => now()->toDateString(),
                'tgl_retur' => now()->toDateString(),
                'total' => 0,
                'total_retur' => 0,
                'alasan' => $alasanHeader,
                'keterangan' => $request->keterangan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($items as $item) {
                $qtyRetur = (int) $item['qty_retur'];
                $batchId = (int) $item['produkbatches_id'];
                $produkId = (int) $item['produks_id'];
                $hargaBeli = (float) ($item['harga_beli'] ?? 0);
                $batch = Produkbatches::findOrFail($batchId);
                if ($batch->stok < $qtyRetur) {
                    throw new \Exception("Stok batch #{$batch->id} tidak cukup untuk retur.");
                }
                $detailBeli = DB::table('notabelis_has_produks')
                ->where('notabelis_id', $nota->id)
                ->where('produkbatches_id', $batchId)
                ->whereNull('deleted_at')
                ->first();
                if (!$detailBeli) {
                    throw new \Exception("Item retur tidak valid untuk nota pembelian #{$nota->id}.");
                }
                if ($qtyRetur > $detailBeli->quantity) {
                    throw new \Exception("Qty retur tidak boleh melebihi qty pembelian.");
                }
                $subtotal = $qtyRetur * $hargaBeli;
                $totalRetur += $subtotal;
                $hppBaru = HppService::hitungUlang(
                    $produkId,
                    $qtyRetur,
                    $hargaBeli,
                    'retur',
                    $nota->id,
                    $batchId
                );
                $batch->decrement('stok', $qtyRetur);
                HppService::updateBatchHpp($produkId, $hppBaru);
                DB::table('retur_pembelian_details')->insert([
                    'retur_pembelian_id' => $returId,
                    'notabelis_has_produks_id' => null,
                    'produkbatches_id' => $batchId,
                    'produks_id' => $produkId,
                    'qty' => $qtyRetur,
                    'harga_satuan' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'alasan' => $item['alasan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('retur_pembelians')
            ->where('id', $returId)
            ->update([
                'total' => $totalRetur,
                'total_retur' => $totalRetur,
                'updated_at' => now(),
            ]);
            DB::commit();
            return redirect()
                ->route('retur.index')
                ->with('status', 'Retur pembelian berhasil disimpan dan HPP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan retur: ' . $e->getMessage());
        }
    }
    public function createFromNota($id)
    {
        $nota = Notabeli::with('user')->findOrFail($id);
        $items = DB::table('notabelis_has_produks as nbp')
            ->join('produkbatches as pb', 'nbp.produkbatches_id', '=', 'pb.id')
            ->join('produks as p', 'pb.produks_id', '=', 'p.id')
            ->leftJoin('satuans as s', 'pb.satuans_id', '=', 's.id')
            ->leftJoin('distributors as d', 'pb.distributors_id', '=', 'd.id')
            ->where('nbp.notabelis_id', $nota->id)
            ->whereNull('nbp.deleted_at')
            ->select(
                'nbp.notabelis_id',
                'nbp.produkbatches_id',
                'nbp.quantity as qty_beli',
                'nbp.subtotal',
                'pb.id as batch_id',
                'pb.produks_id',
                'pb.stok as stok_batch',
                'pb.unitprice',
                'pb.hpp_avg_per_unit',
                'pb.tgl_kadaluarsa',
                'p.nama as nama_produk',
                's.nama as nama_satuan',
                'd.nama as nama_distributor'
            )
            ->get();
        return view('retur.createFromNota', [
            'nota' => $nota,
            'items' => $items,
        ]);
    }
    /**
     * Detail retur pembelian.
     */
    public function show($id)
    {
        $retur = DB::table('retur_pembelians as rp')
            ->leftJoin('notabelis as nb', 'rp.notabelis_id', '=', 'nb.id')
            ->leftJoin('users as u', 'rp.pegawai_id', '=', 'u.id')
            ->where('rp.id', $id)
            ->select(
                'rp.*',
                'nb.id as nota_pembelian_id',
                'u.nama as nama_pegawai'
            )
            ->first();
        if (!$retur) {
            abort(404);
        }
        $items = DB::table('retur_pembelian_details as rpd')
            ->leftJoin('produkbatches as pb', 'rpd.produkbatches_id', '=', 'pb.id')
            ->leftJoin('produks as p', 'rpd.produks_id', '=', 'p.id')
            ->leftJoin('satuans as s', 'pb.satuans_id', '=', 's.id')
            ->leftJoin('distributors as d', 'pb.distributors_id', '=', 'd.id')
            ->where('rpd.retur_pembelian_id', $id)
            ->select(
                'rpd.*',
                'rpd.qty as qty_diretur',
                'pb.id as batch_id',
                'pb.tgl_kadaluarsa',
                'p.nama as nama_produk',
                's.nama as nama_satuan',
                'd.nama as nama_distributor'
            )
            ->get();
        $retur->items = $items;
        return view('retur.show', [
            'retur' => $retur,
            'items' => $items,
        ]);
    }
    /**
     * Print nota retur.
     */
    public function print($id)
    {
        $retur = ReturPembelian::with([
            'notabeli',
            'pegawai',
            'items.produk',
            'items.batch',
        ])->findOrFail($id);
        return view('retur.print', compact('retur'));
    }
}
