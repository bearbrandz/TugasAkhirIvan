@extends('layout.conquer')
@section('title')
@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Buat Profil Apotek</h1>
    <form method="POST" action="{{ route('profilapoteks.store') }}">
        @csrf
        <div class="form-group">
            <label for="nama">Nama Apotek</label>
            <input type="text" class="form-control" name="nama" aria-describedby="nameHelp" placeholder="Masukkan nama">
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="alamat">Alamat Apotek</label>
            <input type="text" class="form-control" name="alamat" aria-describedby="nameHelp"
                placeholder="Masukkan alamat">
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="no_hp">No HP Apotek</label>
            <input type="text" class="form-control" name="no_hp" aria-describedby="nameHelp"
                placeholder="Masukkan no hp">
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="email">Email Apotek</label>
            <input type="text" class="form-control" name="email" aria-describedby="nameHelp"
                placeholder="Masukkan email">
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi Apotek</label>
            <textarea class="form-control" name="deskripsi" rows="4" placeholder="Masukkan Deskripsi Apotek"></textarea>
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="jam_operasional">Jam Operasional Apotek</label>
            <input type="text" class="form-control" name="jam_operasional" aria-describedby="nameHelp"
                placeholder="Masukkan jam operasional">
            <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
        </div>
        <div class="form-group">
            <label for="pemilik_id">Pemilik</label>
            <select class="form-control" name="pemilik_id">
                @foreach ($user as $u)
                    <option value="{{ $u->id }}">
                        {{ $u->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('profilapoteks.index') }}" class="btn btn-secondary ml-2">Batal</a>
    </form>
@endsection
