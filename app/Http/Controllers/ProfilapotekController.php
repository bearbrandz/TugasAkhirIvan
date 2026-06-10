<?php
namespace App\Http\Controllers;
use App\Models\Profilapotek;
use App\Models\User;
use Illuminate\Http\Request;
class ProfilapotekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profils = Profilapotek::first();
        return view('profil.index', ['profil' => $profils]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $profils = Profilapotek::first();
        $users = User::all();
        return view('profil.create', ['profils' => $profils, 'user' => $users]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'deskripsi' => 'required',
            'jam_operasional' => 'required',
            'pemilik_id' => 'required',
        ]); 
        $data = new Profilapotek();
        $data->nama = $request->get('nama');
        $data->alamat = $request->get('alamat');
        $data->no_hp = $request->get('no_hp');
        $data->email = $request->get('email');
        $data->deskripsi = $request->get('deskripsi');
        $data->jam_operasional = $request->get('jam_operasional');
        $data->pemilik_id = $request->pemilik_id;
        $data->save();
        return redirect('profilapoteks')->with('status', 'The new data has been inserted');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $profils = Profilapotek::find($id);
        $users = User::all();
        return view('profil.edit', ['profils' => $profils, 'user' => $users]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = Profilapotek::find($id);
        $data->nama = $request->get('nama');
        $data->alamat = $request->get('alamat');
        $data->no_hp = $request->get('no_hp');
        $data->email = $request->get('email');
        $data->deskripsi = $request->get('deskripsi');
        $data->jam_operasional = $request->get('jam_operasional');
        $data->pemilik_id = $request->pemilik_id;
        $data->save();
        return redirect('profilapoteks')->with('status', 'Profil telah diperbarui');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deletedData = Profilapotek::find($id);
            $deletedData->delete();
            return redirect('profilapoteks')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('profilapoteks')->with('status', $msg);
        }
    }
}
