<?php
$notas = \App\Models\Notabeli::all();
foreach($notas as $n) {
    $firstItem = $n->notaBeliProduks()->with('produkbatches')->first();
    if($firstItem && $firstItem->produkbatches) {
        $n->distributors_id = $firstItem->produkbatches->distributors_id;
        $n->save();
    }
}
echo "Done.";
