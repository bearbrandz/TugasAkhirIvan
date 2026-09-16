<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Dokter::query();
        
        
        if ($search) {
            $query->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('sip', 'LIKE', "%{$search}%")
                  ->orWhere('no_telp', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%");
        }
        
        $datas = $query->orderBy('id', 'desc')->paginate(10)->appends(['search' => $search]);
        return view('dokter.index', compact('datas', 'search'));
    }

    public function create()
    {
        return view('dokter.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'sip' => 'nullable|max:100',
            'no_telp' => 'nullable|max:20',
            'alamat' => 'nullable'],
        [
            'nama.required' => 'NAMA DOKTER WAJIB DIISI',
        ]);

        Dokter::create([
            'nama'    => 'dr. '.$request->nama,
            'sip'     => $request->sip,
            'no_telp' => $request->no_telp,
            'alamat'  => $request->alamat   ]);

        return redirect()->route('dokters.index')->with('status', 'Data Dokter berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $data = Dokter::findOrFail($id);
        return view('dokter.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'sip' => 'nullable|max:100',
            'no_telp' => 'nullable|max:20',
            'alamat' => 'nullable'
        ]);

        $data = Dokter::findOrFail($id);
        $data->update($request->all());
        return redirect()->route('dokters.index')->with('status', 'Data Dokter berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $data = Dokter::findOrFail($id);
        $data->delete();
        return redirect()->route('dokters.index')->with('status', 'Data Dokter berhasil dihapus!');
    }
    public function quickStore(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'sip' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:45',
            'alamat' => 'nullable|string',  
        ]);
        $dokter = \App\Models\Dokter::create($request->all());
        
        return response()->json([
            'success' => true, 
            'data' => $dokter
        ]);
    }
}
