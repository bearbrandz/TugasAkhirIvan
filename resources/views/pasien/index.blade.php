@extends('layout.conquer')

@section('title')
    Daftar Master Pasien
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Master Data Pasien</h1>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Sukses! </strong> {{ session('status') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('pasiens.create') }}" class="btn btn-primary btn-lg text-white font-bold rounded" style="background-color: #6C5B7B; border: none; padding: 10px 20px;">
            <i class="fa fa-plus" style="font-size: 16px; margin-right: 8px;"></i> Pasien Baru
        </a>
    </div>

    <form method="GET" action="{{ route('pasiens.index') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari nama atau no. telepon pasien..." value="{{ $search ?? '' }}">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary" style="background-color: #6C5B7B; border-color: #6C5B7B;">Cari</button>
            </span>
        </div>
    </form>

    <div class="bg-white rounded shadow-sm p-4 overflow-x-auto">
        <table class="table table-bordered table-hover">
            <thead style="background-color: #355C7D; color: white;">
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Pasien</th>
                    <th>Tanggal Lahir / Umur</th>
                    <th>Jenis Kelamin</th>
                    <th>No. Telp</th>
                    <th>Alamat</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($datas as $key => $d)
                    <tr>
                        <td>{{ $datas->firstItem() + $key }}</td>
                        <td>{{ strtoupper($d->nama) }}</td>
                        <td>
                            @if($d->tanggal_lahir)
                                {{ \Carbon\Carbon::parse($d->tanggal_lahir)->format('d-F-Y') }} 
                                <br><small class="text-muted">({{ \Carbon\Carbon::parse($d->tanggal_lahir)->age }} thn)</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $d->jenis_kelamin == 'L' ? 'Laki-laki' : ($d->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                        <td> {{ $d->no_telp ? wordwrap ($d->no_telp, 4, '-', true) : '-' }}</td>
                        <td>{{ $d->alamat ?? '-' }}</td>

                        <td>
                            <a href="{{ route('pasiens.edit', $d->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            <form action="{{ route('pasiens.destroy', $d->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data pasien ini?');"> 
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-danger"><i>Data Pasien Kosong.</i></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-3">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
