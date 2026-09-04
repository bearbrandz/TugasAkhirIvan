@extends('layout.conquer')

@section('title')
    Edit Master Pasien
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Master Pasien</h1>

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
        <form method="POST" action="{{ route('pasiens.update', $data->id) }}">
            @csrf
            @method('PUT')
            
            <div class="form-group mb-4">
                <label for="nama" class="font-semibold">Nama Pasien Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama" id="nama" value="{{ old('nama', $data->nama) }}" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label for="tanggal_lahir" class="font-semibold">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir', $data->tanggal_lahir) }}">
                        <small class="text-muted">Penting untuk perhitungan dosis anak / lansia.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label for="jenis_kelamin" class="font-semibold">Jenis Kelamin</label>
                        <select class="form-control" name="jenis_kelamin" id="jenis_kelamin">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L" {{ old('jenis_kelamin', $data->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin', $data->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="no_telp" class="font-semibold">No. Telepon / HP</label>
                <input type="text" class="form-control" name="no_telp" id="no_telp" value="{{ old('no_telp', $data->no_telp) }}">
            </div>

            <div class="form-group mb-4">
                <label for="alamat" class="font-semibold">Alamat Rumah</label>
                <textarea class="form-control" name="alamat" id="alamat" rows="3">{{ old('alamat', $data->alamat) }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-warning">Perbarui Data</button>
                <a href="{{ route('pasiens.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
