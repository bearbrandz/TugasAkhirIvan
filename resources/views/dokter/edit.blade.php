@extends('layout.conquer')

@section('title')
    Edit Master Dokter
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Master Dokter</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded shadow-sm p-4">
        <form method="POST" action="{{ route('dokters.update', $data->id) }}">
            @csrf
            @method('PUT')
            
            <div class="form-group mb-4">
                <label for="nama" class="font-semibold">Nama Dokter Lengkap (dengan gelar) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama" id="nama" value="{{ strtoupper(old('nama', $data->nama)) }}" required>
            </div>



            <div class="form-group mb-4">
                <label for="sip" class="font-semibold">Nomor SIP (Surat Izin Praktik)</label>
                <input type="text" class="form-control" name="sip" id="sip" value="{{ old('sip', $data->sip) }}">
                <small class="text-muted">Dibutuhkan untuk validasi legalitas resep obat keras / narkotika.</small>
            </div>

            <div class="form-group mb-4">
                <label for="no_telp" class="font-semibold">No. Telepon / HP</label>
                <input type="text" class="form-control" name="no_telp" id="no_telp" value="{{ old('no_telp', $data->no_telp) }}">
            </div>

            <div class="form-group mb-4">
                <label for="alamat" class="font-semibold">Alamat Praktik / Rumah</label>
                <textarea class="form-control" name="alamat" id="alamat" rows="3">{{ old('alamat', $data->alamat) }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-warning">Perbarui Data</button>
                <a href="{{ route('dokters.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
