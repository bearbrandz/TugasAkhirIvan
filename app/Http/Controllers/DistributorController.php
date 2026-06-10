<?php
namespace App\Http\Controllers;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $datas = Distributor::withCount('produkbatches')
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%$search%")
                    ->orWhere('alamat', 'like', "%$search%")
                    ->orWhere('no_hp', 'like', "%$search%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate(10)
            ->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]);
        return view('distributor.index', compact('datas', 'search', 'sortBy', 'sortOrder'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $distributors = Distributor::all();
        return view('distributor.create', ['distributors' => $distributors]);
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
        ]); 
        $data = new Distributor();
        $data->nama = $request->get('nama');
        $data->alamat = $request->get('alamat');
        $data->no_hp = $request->get('no_hp');
        $data->save();
        return redirect('distributors')->with('status', 'The new data has been inserted');
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
        $data = Distributor::find($id);
        return view('distributor.edit', ['datas' => $data]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = Distributor::find($id);
        $data->nama = $request->get('nama');
        $data->alamat = $request->get('alamat');
        $data->no_hp = $request->get('no_hp');
        $data->save();
        return redirect('distributors')->with('status', 'The new data has been updated');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $deletedData = Distributor::find($id);
            $deletedData->delete();
            return redirect('distributors')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect('distributors')->with('status', $msg);
        }
    }
}
