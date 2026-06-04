@extends('layout.conquer')
@section('title')
@section('content')

    @if (session('status'))
        <script>
            alert("{{ session('status') }}");
        </script>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Penerimaan Batch</h1>

    <div class="alert alert-info">
        <strong>Informasi Penerimaan:</strong><br>
        Dipesan: <strong>{{ $qtyOrdered }}</strong><br>
        Sudah Diterima: <strong>{{ $qtyReceived }}</strong><br>
        Sisa yang harus diterima:
        <strong class="{{ $qtyRemaining < 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ $qtyRemaining < 0 ? 'Kelebihan (' . abs($qtyRemaining) . ')' : $qtyRemaining }}
        </strong>
    </div>

    <form method="POST" action="{{ route('produks.updateTerimaBatch', [$datas->id]) }}">
        @csrf
        <input type="hidden" name="pegawai_id" value="{{ auth()->user()->id }}">
        @method('PUT')
        <input type="hidden" class="form-control" name="produks_id" aria-describedby="nameHelp"
            value="{{ $datas->produks_id }}">
        <div class="form-group">
            <label for="stok">Stok Produk</label>
            <input type="number" class="form-control" name="stok" aria-describedby="nameHelp"
                placeholder="Masukkan Stok Obat">
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="gudangs">Gudang Produk</label>
            <select class="form-control" name="gudangs">
                @foreach ($gudangs as $g)
                    <option value="{{ $g->id }}"{{ $g->id == $datas->gudangs_id ? 'selected' : '' }}>
                        {{ $g->lokasi }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="tgl_datang">Tanggal Datang</label>
            <input type="date" class="form-control" name="tgl_datang" aria-describedby="dateHelp"
                value="{{ $datas->tgl_datang }}">
            <small id="dateHelp" class="form-text text-muted">Pilih tanggal datang produk.</small>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('produks.batch', ['id' => $datas->produks_id]) }}"
            class="btn btn-primary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
    </form>
@endsection
