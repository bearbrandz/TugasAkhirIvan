<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\DistributorController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\NotabeliController;
use App\Http\Controllers\NotajualController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProdukopnameController;
use App\Http\Controllers\ProfilapotekController;
use App\Http\Controllers\RacikanController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SatuanKonversiController;
use App\Http\Controllers\UserController;

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsAdminOrApoteker;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\DB;

Route::get('/fix-live-db', function () {
    // 1. Hapus Duplikat notajuals_has_racikans
    $allNjr = DB::table('notajuals_has_racikans')->orderBy('created_at', 'asc')->get();
    $seen = [];
    $deleted = 0;
    foreach ($allNjr as $record) {
        if (in_array($record->racikans_id, $seen)) {
            DB::table('notajuals_has_racikans')
                ->where('notajuals_id', $record->notajuals_id)
                ->where('racikans_id', $record->racikans_id)
                ->delete();
            $deleted++;
        } else {
            $seen[] = $record->racikans_id;
        }
    }

    // 2. Hapus notajuals yang tidak punya relasi
    $orphans = DB::table('notajuals')
        ->leftJoin('notajuals_has_produks', 'notajuals.id', '=', 'notajuals_has_produks.notajuals_id')
        ->leftJoin('notajuals_has_racikans', 'notajuals.id', '=', 'notajuals_has_racikans.notajuals_id')
        ->whereNull('notajuals_has_produks.notajuals_id')
        ->whereNull('notajuals_has_racikans.notajuals_id')
        ->pluck('notajuals.id');
    if ($orphans->count() > 0) {
        DB::table('notajuals')->whereIn('id', $orphans)->delete();
    }

    // 3. Tambah Racikan "Daniel Budianto" jika belum ada
    $exists = DB::table('racikans')->where('nama_pasien', 'Daniel Budianto')->exists();
    if (!$exists) {
        $racikanId = DB::table('racikans')->insertGetId([
            'nama' => 'Racikan Penurun Panas',
            'deskripsi' => 'Serbuk/Puyer',
            'aturan_pakai' => '3 x 1 Pcs',
            'nama_pasien' => 'Daniel Budianto',
            'nama_dokter' => 'dr. Budi',
            'biaya_embalase' => 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notajualId = DB::table('notajuals')->insertGetId([
            'pegawai_id' => 1,
            'total_bayar' => 10000,
            'nominal_bayar' => 10000,
            'kembalian' => 0,
            'metode_bayar' => 'tunai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notajuals_has_racikans')->insert([
            'notajuals_id' => $notajualId,
            'racikans_id' => $racikanId,
            'quantity' => 1,
            'subtotal' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('racikanproduks')->insert([
            'racikans_id' => $racikanId,
            'produks_id' => 44,
            'quantity' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // 4. Sinkronisasi Stok Semua Produk
    $produks = \App\Models\Produk::all();
    $adjusted = 0;
    foreach ($produks as $p) {
        $beli = DB::table('notabelis_has_produks')
            ->join('produkbatches', 'notabelis_has_produks.produkbatches_id', '=', 'produkbatches.id')
            ->where('produkbatches.produks_id', $p->id)
            ->sum('notabelis_has_produks.quantity');
            
        $jual = DB::table('notajuals_has_produks')
            ->join('produkbatches', 'notajuals_has_produks.produkbatches_id', '=', 'produkbatches.id')
            ->where('produkbatches.produks_id', $p->id)
            ->sum('notajuals_has_produks.quantity');
            
        $jual_racikan = DB::table('notajuals_has_racikans')
            ->join('racikanproduks', 'notajuals_has_racikans.racikans_id', '=', 'racikanproduks.racikans_id')
            ->where('racikanproduks.produks_id', $p->id)
            ->sum(DB::raw('notajuals_has_racikans.quantity * racikanproduks.quantity'));
            
        $retur = DB::table('retur_pembelian_items')
            ->join('produkbatches', 'retur_pembelian_items.produkbatches_id', '=', 'produkbatches.id')
            ->where('produkbatches.produks_id', $p->id)
            ->sum('retur_pembelian_items.quantity');
            
        $expected_stok = $beli - $jual - $jual_racikan - $retur;
        
        $batches = \App\Models\Produkbatches::where('produks_id', $p->id)->orderBy('id', 'desc')->get();
        $current_stok = $batches->sum('stok');
        
        if ($expected_stok != $current_stok && $batches->count() > 0) {
            $diff = $current_stok - $expected_stok;
            $batch = $batches->first();
            $batch->stok = $batch->stok - $diff;
            $batch->save();
            $adjusted++;
        }
    }

    return "Database live berhasil diperbaiki! Dihapus {$deleted} duplikat nota racikan, dan disinkronkan stok untuk {$adjusted} produk.";
});

Route::get('/fix-sella', function () {
    $produks = DB::table('produks')
        ->where('nama', 'LIKE', '%Sella%')
        ->orderBy('id', 'asc')
        ->get();

    $response = "Debug Daftar Produk Sella:<br><br>";
    foreach ($produks as $p) {
        $response .= "ID: {$p->id} | Nama: '{$p->nama}' | Deleted_at: " . ($p->deleted_at ?? 'NULL') . "<br>";
    }
    
    return $response;
});

Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

Auth::routes(['register' => false]);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD & PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [ProdukController::class, 'homeProduk'])
        ->name('homeProduk');

    Route::get('/profilapotek', [ProfilapotekController::class, 'index'])
        ->name('profilapotek');

    Route::get('/user/profile', [UserController::class, 'detail'])
        ->name('profile');

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', function () {
        if (auth()->check()) {
            \App\Models\LogActivity::catat(
                'logout',
                'Auth',
                'User ' . auth()->user()->nama . ' logout dari sistem.'
            );
        }

        Session::forget(['cart', 'cart_jual', 'cart_beli', 'racikan_cart']);

        Auth::logout();

        return redirect()->route('login');
    })->name('logout');

    /*
    |--------------------------------------------------------------------------
    | NOTA JUAL / PENJUALAN
    |--------------------------------------------------------------------------
    */

    Route::resource('notajuals', NotajualController::class);

    Route::post('/notajuals/cart', [NotajualController::class, 'addToCart'])
        ->name('notajuals.cart');

    Route::delete('/notajuals/cart/delete/{id}', [NotajualController::class, 'deleteFromCart'])
        ->name('notajualscart.delete');

    Route::get('/notajuals/{id}/print', [NotajualController::class, 'print'])
        ->name('notajuals.print');

    /*
    |--------------------------------------------------------------------------
    | RACIKAN UNTUK PENJUALAN
    |--------------------------------------------------------------------------
    */

    Route::get('racikan/komposisi/{id}', [RacikanController::class, 'komposisi'])
        ->name('racikans.komposisi');

    Route::get('racikan/notaracikan', [RacikanController::class, 'notaRacikan'])
        ->name('racikans.notaRacikan');

    Route::get('racikan/checkout/{id}', [RacikanController::class, 'checkoutRacikan'])
        ->name('racikans.checkout');

    Route::post('racikan/bayar/{id}', [RacikanController::class, 'bayarRacikan'])
        ->name('racikans.bayar');

    Route::post('racikan/jualracikan/{id}', [RacikanController::class, 'jualRacikan'])
        ->name('racikans.jualRacikan');

    Route::delete(
        'racikan/destroyKomposisi/{racikans_id}/{produks_id}',
        [RacikanController::class, 'destroyKomposisi']
    )->name('racikans.destroyKomposisi');

    /*
    |--------------------------------------------------------------------------
    | LAPORAN PENJUALAN (bisa diakses semua role: admin, apoteker, kasir)
    |--------------------------------------------------------------------------
    */

    Route::get('transaksi/report/reportPenjualan', [NotajualController::class, 'report'])
        ->name('notajuals.report');

    Route::get('transaksi/report/reportPenjualan/csv', [NotajualController::class, 'reportCsv'])
        ->name('notajuals.csv');

});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', IsAdmin::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | REGISTER USER
    |--------------------------------------------------------------------------
    */

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
        ->name('registerUser');

    Route::post('/register', [RegisterController::class, 'register'])
        ->name('register');

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/user', [UserController::class, 'index'])
        ->name('user');

    Route::resource('users', UserController::class);

    /*
    |--------------------------------------------------------------------------
    | PROFIL APOTEK MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::resource('profilapoteks', ProfilapotekController::class);

    /*
    |--------------------------------------------------------------------------
    | KONVERSI SATUAN
    |--------------------------------------------------------------------------
    */

    Route::resource('satuankonversi', SatuanKonversiController::class);

    /*
    |--------------------------------------------------------------------------
    | LAPORAN LABA RUGI
    |--------------------------------------------------------------------------
    */

    Route::get('laporan/labarugi', [LaporanController::class, 'labaRugi'])
        ->name('laporan.labarugi');

    Route::get('laporan/labarugi/csv', [LaporanController::class, 'labaRugiCsv'])
        ->name('laporan.labarugi.csv');

    /*
    |--------------------------------------------------------------------------
    | LOG ACTIVITY
    |--------------------------------------------------------------------------
    */

    Route::get('log-activity', [LogActivityController::class, 'index'])
        ->name('log.index');
});

/*
|--------------------------------------------------------------------------
| ADMIN & APOTEKER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', IsAdminOrApoteker::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SHORTCUT MENU
    |--------------------------------------------------------------------------
    */

    Route::get('/distributor', [DistributorController::class, 'index'])
        ->name('distributor');

    Route::get('/satuan', [SatuanController::class, 'index'])
        ->name('satuan');

    Route::get('/produk', [ProdukController::class, 'index'])
        ->name('produk');

    Route::get('/racikan', [RacikanController::class, 'index'])
        ->name('racikan');

    Route::get('/gudang', [GudangController::class, 'index'])
        ->name('gudang');

    Route::get('/opname', [ProdukopnameController::class, 'index'])
        ->name('opname');

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA
    |--------------------------------------------------------------------------
    */

    Route::resource('distributors', DistributorController::class);
    Route::resource('satuans', SatuanController::class);
    Route::resource('gudangs', GudangController::class);
    Route::get('/gudangs/{id}/produk', [GudangController::class, 'produk'])
    ->name('gudangs.produk');
    Route::resource('racikans', RacikanController::class);
    Route::get('opnames/report/csv', [ProdukopnameController::class, 'reportCsv'])
    ->name('opnames.csv');
    Route::resource('opnames', ProdukopnameController::class);

    /*
    |--------------------------------------------------------------------------
    | PRODUK
    |--------------------------------------------------------------------------
    | Route spesifik harus berada di atas produk/{id}.
    |--------------------------------------------------------------------------
    */

    Route::get('produk/batch/{id}', [ProdukController::class, 'batch'])
        ->name('produks.batch');

    Route::get('produk/batch/{id}/print', [ProdukController::class, 'print'])
        ->name('produks.print');

    Route::get('produk/terimaBatch/{id}', [ProdukController::class, 'terimaBatch'])
        ->name('produks.terimaBatch');

    Route::put('produk/updateTerimaBatch/{id}', [ProdukController::class, 'updateTerimaBatch'])
        ->name('produks.updateTerimaBatch');

    Route::get('produk/editBatch/{id}', [ProdukController::class, 'editBatch'])
        ->name('produks.editBatch');

    Route::put('produk/updateBatch/{id}', [ProdukController::class, 'updateBatch'])
        ->name('produks.updateBatch');

    Route::delete('produk/destroyBatch/{id}', [ProdukController::class, 'destroyBatch'])
        ->name('produks.destroyBatch');

    Route::delete('produk/destroyTerima/{id}', [ProdukController::class, 'destroyTerima'])
        ->name('produks.destroyTerima');

    Route::get('produk/daftarTerima', [ProdukController::class, 'daftarTerima'])
        ->name('produks.daftarTerima');
    
    Route::get('produk/daftarTerima/{id}/print', [ProdukController::class, 'printTerima'])
        ->name('produks.printTerima');

    Route::get('produk/daftarKadaluarsa', [ProdukController::class, 'daftarKadaluarsa'])
        ->name('produks.daftarKadaluarsa');

    Route::get('produk/report/reportKadaluarsa', [ProdukController::class, 'reportKadaluarsa'])
        ->name('produks.reportKadaluarsa');

    Route::get('produk/report/reportKadaluarsa/csv', [ProdukController::class, 'reportCsvKadaluarsa'])
        ->name('produks.csvKadaluarsa');

    Route::get('produk/printKadaluarsa/{id}', [ProdukController::class, 'printKadaluarsa'])
        ->name('produks.printKadaluarsa');

    Route::get('produk/arsip', [ProdukController::class, 'arsip'])
        ->name('produks.arsip');
    
    Route::post('produk/restore/{id}', [ProdukController::class, 'restore'])
        ->name('produks.restore');

    Route::resource('produks', ProdukController::class)
        ->except(['create', 'store', 'show']);

    /*
    |--------------------------------------------------------------------------
    | ROUTE DINAMIS PRODUK HARUS PALING BAWAH
    |--------------------------------------------------------------------------
    */

    Route::get('produk/{id}', [ProdukController::class, 'show'])
        ->name('produks.show');

    /*
    |--------------------------------------------------------------------------
    | NOTA BELI / PEMBELIAN
    |--------------------------------------------------------------------------
    */

    Route::resource('notabelis', NotabeliController::class);

    Route::post('/notabelis/cart', [NotabeliController::class, 'addToCart'])
        ->name('notabelis.cart');

    Route::delete('/notabelis/cart/delete/{id}', [NotabeliController::class, 'deleteFromCart'])
        ->name('notabeliscart.delete');

    Route::post('/notabelis/beliProdukBaru', [NotabeliController::class, 'beliProdukBaru'])
        ->name('notabelis.beliProdukBaru');

    Route::get('/notabelis/{id}/print', [NotabeliController::class, 'print'])
        ->name('notabelis.print');

    Route::get('transaksi/report/reportPembelian', [NotabeliController::class, 'report'])
        ->name('notabelis.report');

    Route::get('transaksi/report/reportPembelian/csv', [NotabeliController::class, 'reportCsv'])
        ->name('notabelis.csv');

    /*
    |--------------------------------------------------------------------------
    | TRANSAKSI MENU
    |--------------------------------------------------------------------------
    */

    Route::get('/transaksi', function () {
        return view('transaksi.tipe');
    })->name('transaksi');

    /*
    |--------------------------------------------------------------------------
    | RETUR PEMBELIAN
    |--------------------------------------------------------------------------
    */

    Route::get('retur', [ReturPembelianController::class, 'index'])
        ->name('retur.index');

    Route::get('retur/create', [ReturPembelianController::class, 'create'])
        ->name('retur.create');

    Route::post('retur/cari', [ReturPembelianController::class, 'cariNota'])
        ->name('retur.cari');

    Route::post('retur/store', [ReturPembelianController::class, 'store'])
        ->name('retur.store');

    Route::get('retur/notabeli/{id}', [ReturPembelianController::class, 'createFromNota'])
        ->name('retur.fromNota');    

    Route::get('retur/{id}', [ReturPembelianController::class, 'show'])
        ->name('retur.show');

    Route::get('retur/{id}/print', [ReturPembelianController::class, 'print'])
        ->name('retur.print');

    /*
    |--------------------------------------------------------------------------
    | NARKOTIKA / PSIKOTROPIKA
    |--------------------------------------------------------------------------
    */

    Route::get('racikan/daftarNarkotika', [RacikanController::class, 'daftarNarkotika'])
        ->name('racikans.daftarNarkotika');

    Route::get('racikan/report/reportNarkotika', [RacikanController::class, 'reportNarkotika'])
        ->name('racikans.reportNarkotika');

    Route::get('racikan/report/reportNarkotika/csv', [RacikanController::class, 'reportCsvNarkotika'])
        ->name('racikans.CsvNarkotika');

    Route::get('racikan/printNarkotika/{id}', [RacikanController::class, 'printNarkotika'])
        ->name('racikans.printNarkotika');

    Route::get('racikan/export/sipnap', [RacikanController::class, 'exportSipnap'])
        ->name('racikans.exportSipnap');

    Route::get('racikan/export/simona', [RacikanController::class, 'exportSimona'])
        ->name('racikans.exportSimona');
});