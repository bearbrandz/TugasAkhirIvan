@extends('layout.conquer')
@section('title')
@section('content')

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Ubah Detail User</h1>
    <form method="POST" action="{{ route('users.update', $datas->id) }}">
        @csrf
        @method('PUT')
        @if (auth()->user()->tipe_user === 'admin')
            <div class="form-group">
                <label for="nama">Nama User</label>
                <input type="text" class="form-control" name="nama" aria-describedby="nameHelp"
                    placeholder="Masukkan Nama Obat" value="{{ $datas->nama }}">
                <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
            </div>
            <div class="form-group">
                <label for="no_hp">No HP</label>
                <input type="text" class="form-control" name="no_hp" aria-describedby="nameHelp"
                    placeholder="Masukkan No HP" value="{{ $datas->no_hp }}">
                <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
            </div>
            <div class="form-group">
                <label for="email">Email User</label>
                <input type="text" class="form-control" name="email" aria-describedby="nameHelp"
                    placeholder="Masukkan Email User" value="{{ $datas->email }}">
                <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
            </div>
            <div class="form-group">
                <label for="username">Username User</label>
                <input type="text" class="form-control" name="username" aria-describedby="nameHelp"
                    placeholder="Masukkan Username User" value="{{ $datas->username }}">
                <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
            </div>
            <div class="form-group">
                <label for="password">Password User</label>
                <input type="password" class="form-control" name="password" aria-describedby="nameHelp"
                    placeholder="Masukkan password User">
                <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
            </div>
            <div class="form-group">
                <label for="tipe_user">Tipe User</label>
                <select class="form-control" name="tipe_user" aria-describedby="nameHelp">
                    <option value="admin" {{ $datas->tipe_user == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="apoteker" {{ $datas->tipe_user == 'apoteker' ? 'selected' : '' }}>Apoteker</option>
                    <option value="kasir" {{ $datas->tipe_user == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    {{-- <option value="karyawan" {{ $datas->tipe_user == 'karyawan' ? 'selected' : '' }}>Karyawan</option> --}}
                </select>
                <small id="nameHelp" class="form-text text-muted">Mohon pilih input yang diinginkan.</small>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('users.index') }}"
                class="btn btn-primary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
        @else
            <div class="form-group">
                <label for="password">Password User</label>
                <input type="password" class="form-control" name="password" aria-describedby="nameHelp"
                    placeholder="Masukkan password User">
                <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('profile') }}"
            class="btn btn-primary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
        @endif

        {{-- perlu tgl lahir? --}}
        {{-- <div class="form-group">
        <label for="tgl_lahir">Tanggal Lahir</label>
        <input type="datetime-local" class="form-control" name="tgl_lahir" aria-describedby="dateHelp" 
               value="{{$datas->tgl_lahir}}">
        <small id="dateHelp" class="form-text text-muted">Pilih tanggal lahir user.</small>
    </div> --}}
    </form>
@endsection
