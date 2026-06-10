<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GudangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');

        $datas = Gudang::when($search, function ($query, $search) {
            return $query->where('lokasi', 'like', "%$search%");
        })
            ->orderBy($sortBy, $sortOrder)
            ->paginate(10)
            ->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]);

        return view('gudang.index', compact('datas', 'search', 'sortBy', 'sortOrder'));
    }

    public function produk($id)
    {
        $gudang = Gudang::findOrFail($id);

        $datas = DB::table('produkbatches as pb')
            ->join('produks as p', 'pb.produks_id', '=', 'p.id')
            ->leftJoin('satuans as s', 'pb.satuans_id', '=', 's.id')
            ->leftJoin('distributors as d', 'pb.distributors_id', '=', 'd.id')
            ->where('pb.gudangs_id', $id)
            ->where('pb.stok', '>', 0)
            ->select(
                'pb.id as batch_id',
                'pb.stok',
                'pb.unitprice',
                'pb.hpp_avg_per_unit',
                'pb.tgl_kadaluarsa',
                'pb.status',
                'p.id as produk_id',
                'p.nama as nama_produk',
                'p.golongan',
                's.nama as nama_satuan',
                'd.nama as nama_distributor'
            )
            ->orderBy('p.nama')
            ->orderBy('pb.tgl_kadaluarsa')
            ->paginate(10);

        return view('gudang.produk', [
            'gudang' => $gudang,
            'datas' => $datas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $gudangs = Gudang::all();
        return view('gudang.create', ['gudangs' => $gudangs]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lokasi' => 'required',
        ]); //ini memberitahu bahwa kolom name itu perlu, agar tidak null
        $data = new Gudang();
        $data->lokasi = $request->get('lokasi');
        $data->save();

        // Type::create($request->all());
        return redirect('gudangs')->with('status', 'The new data has been inserted');
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
        // $objType = $type;
        // dd($type);
        $data = Gudang::find($id);
        // dd($data);
        // echo'masuk form edit';
        return view('gudang.edit', ['datas' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = Gudang::find($id);
        $data->lokasi = $request->get('lokasi');
        $data->save();

        // Type::create($request->all());
        return redirect('gudangs')->with('status', 'The new data has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = Gudang::find($id);
            $deletedData->delete();
            return redirect('gudangs')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('gudangs')->with('status', $msg);
        }
    }
}
