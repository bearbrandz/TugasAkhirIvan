<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Pasien::query();
        
        if ($search) {
            $query->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('no_telp', 'LIKE', "%{$search}%");
        }
        
        $datas = $query->orderBy('id', 'desc')->paginate(10)->appends(['search' => $search]);
        return view('pasien.index', compact('datas', 'search'));
    }

    public function create()
    {
        return view('pasien.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:150',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'no_telp' => 'required|max:20|unique:pasiens,no_telp',
            'alamat' => 'nullable'
        ]);

        Pasien::create($request->all());
        return redirect()->route('pasiens.index')->with('status', 'Data Pasien berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $data = Pasien::findOrFail($id);
        return view('pasien.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:150',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'no_telp' => 'required|max:20|unique:pasiens,no_telp,'.$id.',id,deleted_at,NULL',
            'alamat' => 'nullable'
        ]);

        $data = Pasien::findOrFail($id);
        $data->update($request->all());
        return redirect()->route('pasiens.index')->with('status', 'Data Pasien ' . $request->nama. ' berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $data = Pasien::findOrFail($id);
        $data->delete();
        return redirect()->route('pasiens.index')->with('status', 'Data Pasien berhasil dihapus!');
    }
    public function quickStore(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:45',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
        ]);
        $pasien = \App\Models\Pasien::create($request->all());
        
        return response()->json([
            'success' => true, 
            'data' => $pasien
        ]);
    }
}
