@extends('layout.conquer')

@section('title')
    Tambah Master Dokter
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Tambah Master Dokter</h1>

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
        <form method="POST" action="{{ route('dokters.store') }}">
            @csrf
            
            <div class="form-group mb-4">
                <label for="nama" class="font-semibold">Nama Dokter Lengkap (dengan gelar) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama" id="nama" value="{{ old('nama') }}" required placeholder="Contoh: dr. Budi Santoso, Sp.PD">
            </div>



            <div class="form-group mb-4">
                <label for="sip" class="font-semibold">Nomor SIP (Surat Izin Praktik)</label>
                <input type="text" class="form-control" name="sip" id="sip" value="{{ old('sip') }}" placeholder="Contoh: 445/123/SIP.Dr/2026">
                <small class="text-muted">Dibutuhkan untuk validasi legalitas resep obat keras / narkotika.</small>
            </div>

            <div class="form-group mb-4">
                <label for="no_telp" class="font-semibold">No. Telepon / HP</label>
                <input type="text" class="form-control" name="no_telp" id="no_telp" value="{{ old('no_telp') }}">
            </div>

            <div class="form-group mb-4">
                <label for="alamat" class="font-semibold">Alamat Praktik / Rumah</label>
                <textarea class="form-control" name="alamat" id="alamat" rows="3">{{ old('alamat') }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary" style="background-color: #6C5B7B; border-color: #6C5B7B;">Simpan</button>
                <a href="{{ route('dokters.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
