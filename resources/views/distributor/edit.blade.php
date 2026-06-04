@extends('layout.conquer')
@section('title')
@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Detail Distributor</h1>

<form method="POST" action="{{route('distributors.update', $datas->id)}}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="nama">Nama Distributor</label>
        <input type="text" class="form-control" name="nama" aria-describedby="nameHelp"
            placeholder="Masukkan nama" value="{{$datas->nama}}">
        <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
    </div>
    <div class="form-group">
        <label for="alamat">Alamat Distributor</label>
        <input type="text" class="form-control" name="alamat" aria-describedby="nameHelp"
            placeholder="Masukkan alamat" value="{{$datas->alamat}}">
        <small id="addHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
    </div>
    <div class="form-group">
        <label for="no_hp">Kontak Distributor</label>
        <input type="text" class="form-control" name="no_hp" aria-describedby="nameHelp"
            placeholder="Masukkan no Hp" value="{{$datas->no_hp}}">
        <small id="hpHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="{{ route('distributors.index') }}" class="btn btn-primary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
</form>
@endsection