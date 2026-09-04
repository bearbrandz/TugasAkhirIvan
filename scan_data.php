<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== NOTABELIS COLUMNS ===\n";
$cols = DB::select("DESCRIBE notabelis");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type}) Default:{$c->Default}\n";
}

echo "\n=== NOTABELIS_HAS_PRODUKS COLUMNS ===\n";
$cols = DB::select("DESCRIBE notabelis_has_produks");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type}) Default:{$c->Default}\n";
}

echo "\n=== NOTAJUALS COLUMNS ===\n";
$cols = DB::select("DESCRIBE notajuals");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type}) Default:{$c->Default}\n";
}

echo "\n=== HPP_RECORDS COLUMNS ===\n";
$cols = DB::select("DESCRIBE hpp_records");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}

echo "\n=== NOTABELI BISA DI-RETUR ===\n";
$nb = DB::table('notabelis as nb')
    ->join('notabelis_has_produks as nhp', 'nb.id', '=', 'nhp.notabelis_id')
    ->join('produkbatches as pb', 'nhp.produkbatches_id', '=', 'pb.id')
    ->join('produks as p', 'pb.produks_id', '=', 'p.id')
    ->whereNull('nhp.deleted_at')
    ->where('pb.stok', '>', 5)
    ->where('pb.status', 'tersedia')
    ->select('nb.id as nota_id', 'p.nama', 'pb.id as batch_id', 'nhp.quantity as qty_beli', 'pb.stok as stok_skrg', 'nb.created_at', 'pb.unitprice', 'nhp.id as nhp_id')
    ->orderByDesc('nb.id')
    ->take(10)
    ->get();
foreach ($nb as $n) {
    echo "NotaBeli#{$n->nota_id} | Tgl:{$n->created_at} | {$n->nama} (Batch#{$n->batch_id}) | QtBeli:{$n->qty_beli} | StokSkrg:{$n->stok_skrg} | Harga:{$n->unitprice}\n";
}

echo "\n=== PRODUKOPNAMES SAMPLE DATA ===\n";
$op = DB::table('produkopnames')->take(3)->get();
foreach ($op as $o) {
    print_r((array)$o);
}

echo "\n=== RETUR_PEMBELIANS COLUMNS ===\n";
$cols = DB::select("DESCRIBE retur_pembelians");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type}) Default:{$c->Default}\n";
}
