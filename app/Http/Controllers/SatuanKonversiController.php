<?php
namespace App\Http\Controllers;
use App\Models\Satuan;
use App\Models\SatuanKonversi;
use Illuminate\Http\Request;
class SatuanKonversiController extends Controller
{
    public function index()
    {
        $datas = SatuanKonversi::with(['satuanBesar', 'satuanKecil'])
            ->paginate(10);
        return view('satuankonversi.index', compact('datas'));
    }
    public function create()
    {
        $satuans = Satuan::orderBy('nama')->get();
        return view('satuankonversi.create', compact('satuans'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'satuan_besar_id' => 'required|exists:satuans,id',
            'satuan_kecil_id' => 'required|exists:satuans,id|different:satuan_besar_id',
            'nilai_konversi'  => 'required|integer|min:1',
        ]);
        SatuanKonversi::create([
            'satuan_besar_id' => $request->satuan_besar_id,
            'satuan_kecil_id' => $request->satuan_kecil_id,
            'nilai_konversi'  => $request->nilai_konversi,
        ]);
        return redirect()
            ->route('satuankonversi.index')
            ->with('status', 'Konversi satuan berhasil ditambahkan.');
    }
    public function edit($id)
    {
        $data = SatuanKonversi::findOrFail($id);
        $satuans = Satuan::orderBy('nama')->get();
        return view('satuankonversi.edit', compact('data', 'satuans'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'satuan_besar_id' => 'required|exists:satuans,id',
            'satuan_kecil_id' => 'required|exists:satuans,id|different:satuan_besar_id',
            'nilai_konversi'  => 'required|integer|min:1',
        ]);
        $data = SatuanKonversi::findOrFail($id);
        $data->update([
            'satuan_besar_id' => $request->satuan_besar_id,
            'satuan_kecil_id' => $request->satuan_kecil_id,
            'nilai_konversi'  => $request->nilai_konversi,
        ]);
        return redirect()
            ->route('satuankonversi.index')
            ->with('status', 'Konversi satuan berhasil diperbarui.');
    }
    public function destroy($id)
    {
        SatuanKonversi::findOrFail($id)->delete();
        return redirect()
            ->route('satuankonversi.index')
            ->with('status', 'Konversi satuan berhasil dihapus.');
    }
}