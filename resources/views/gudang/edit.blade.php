@extends('layout.conquer')
@section('title')
@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Detail Gudang</h1>

<form method="POST" action="{{route('gudangs.update', $datas->id)}}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="lokasi">Lokasi Gudang</label>
        <input type="text" class="form-control" name="lokasi" aria-describedby="nameHelp"
            placeholder="Masukkan lokasi" value="{{$datas->lokasi}}">
        <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="{{ route('gudangs.index') }}" class="btn btn-primary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
</form>
@endsection