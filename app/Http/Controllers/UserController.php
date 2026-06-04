<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //user() error karena 
        if (Auth::user()->tipe_user !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        $sortBy = $request->get('sort_by', 'nama');  // Default to 'nama'
        $sortOrder = $request->get('sort_order', 'asc');  // Default to ascending
        $search = $request->get('search');              // Search query

        $query = User::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%$search%")
                    ->orWhere('nama', 'LIKE', "%$search%")
                    ->orWhere('no_hp', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('tipe_user', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%")
                    ->orWhere('created_at', 'LIKE', "%$search%")
                    ->orWhere('updated_at', 'LIKE', "%$search%");
            });
        }
        $users = $query->orderBy($sortBy, $sortOrder)->paginate(6);
        // dd($users);
        return view('user.index', [
            'datas' => $users,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search
        ]);
    }

    public function detail(Request $request)
    {
        $sortBy = $request->get('sort_by', 'nama');  // Default to 'nama'
        $sortOrder = $request->get('sort_order', 'asc');  // Default to ascending
        $search = $request->get('search');              // Search query

        $query = User::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%$search%")
                    ->orWhere('nama', 'LIKE', "%$search%")
                    ->orWhere('no_hp', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('tipe_user', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%")
                    ->orWhere('created_at', 'LIKE', "%$search%")
                    ->orWhere('updated_at', 'LIKE', "%$search%");
            });
        }
        $users = $query->orderBy($sortBy, $sortOrder)->get();
        // dd($users);
        return view('user.profile', [
            'datas' => $users,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        // $objType = $type;
        // dd($type);
        $data = User::find($id);
        if (Auth::user()->tipe_user !== 'admin' && Auth::id() != $id) {
            abort(403, 'Unauthorized action.');
        }

        // dd($data);
        // echo'masuk form edit';
        return view('user.edit', ['datas' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::user()->tipe_user !== 'admin' && Auth::id() != $id) {
            abort(403, 'Unauthorized action.');
        }

        $data = User::find($id);

        if (!$data) {
            return redirect()
                ->route('user')
                ->with('error', 'Data user tidak ditemukan.');
        }

        $isAdminLogin = Auth::user()->tipe_user === 'admin';

        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255|unique:users,email,' . $data->id,
            'username' => 'required|string|max:255|unique:users,username,' . $data->id,
            'password' => 'nullable|string|min:8',
            'tipe_user' => $isAdminLogin
                ? 'required|in:admin,apoteker,kasir'
                : 'nullable',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cegah admin mengubah role terakhir menjadi role lain
        |--------------------------------------------------------------------------
        */
        if ($isAdminLogin && $request->has('tipe_user')) {
            $roleLama = $data->tipe_user;
            $roleBaru = $request->get('tipe_user');

            $requiredRoles = ['admin', 'apoteker', 'kasir'];

            if ($roleLama !== $roleBaru && in_array($roleLama, $requiredRoles)) {
                $sisaUserRoleLama = User::where('tipe_user', $roleLama)
                    ->where('id', '!=', $data->id)
                    ->count();

                if ($sisaUserRoleLama < 1) {
                    return redirect()
                        ->route('user')
                        ->with('error', 'Tidak dapat mengubah role user ini karena role ' . ucfirst($roleLama) . ' harus memiliki minimal 1 akun aktif.');
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update data dasar
        |--------------------------------------------------------------------------
        */
        $data->nama = $request->get('nama');
        $data->no_hp = $request->get('no_hp');
        $data->email = $request->get('email');
        $data->username = $request->get('username');

        /*
        |--------------------------------------------------------------------------
        | Role hanya boleh diubah oleh admin
        |--------------------------------------------------------------------------
        */
        if ($isAdminLogin && $request->filled('tipe_user')) {
            $data->tipe_user = $request->get('tipe_user');
        }

        /*
        |--------------------------------------------------------------------------
        | Password hanya diubah jika diisi
        |--------------------------------------------------------------------------
        */
        if ($request->filled('password')) {
            $data->password = Hash::make($request->get('password'));
        }

        $data->save();

        /*
        |--------------------------------------------------------------------------
        | Redirect setelah update
        |--------------------------------------------------------------------------
        | Jika admin update karyawan, kembali ke daftar karyawan.
        | Jika user update profil sendiri, kembali ke halaman sebelumnya.
        |--------------------------------------------------------------------------
        */
        if ($isAdminLogin) {
            return redirect()
                ->route('user')
                ->with('status', 'Data karyawan berhasil diperbarui.');
        }

        return redirect()
            ->back()
            ->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::user()->tipe_user !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);

        // Cegah user menghapus akun sendiri
        if (Auth::id() == $user->id) {
            return redirect('users')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        // Role yang wajib minimal punya 1 akun aktif
        $requiredRoles = ['admin', 'apoteker', 'kasir'];

        if (in_array($user->tipe_user, $requiredRoles)) {
            $sisaUserRole = User::where('tipe_user', $user->tipe_user)
                ->where('id', '!=', $user->id)
                ->count();

            if ($sisaUserRole < 1) {
                return redirect('users')
                    ->with('error', 'Tidak dapat menghapus user ini karena role ' . ucfirst($user->tipe_user) . ' harus memiliki minimal 1 akun aktif.');
            }
        }

        try {
            $user->delete();

            return redirect('users')
                ->with('status', 'Data karyawan berhasil dihapus.');
        } catch (\PDOException $ex) {
            return redirect('users')
                ->with('error', 'Gagal menghapus data karyawan. Pastikan tidak ada data terkait sebelum menghapus.');
        }
    }
}
