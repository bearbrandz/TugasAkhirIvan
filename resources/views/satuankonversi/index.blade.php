@extends('layout.conquer')
@section('title')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Konversi Satuan</h1>
    <p class="text-gray-600 mb-4">Kelola konversi antar satuan (contoh: 1 Box = 10 Strip = 100 Biji)</p>

    <a href="{{ route('satuankonversi.create') }}" class="btn btn-primary mb-3">Tambah Konversi Baru</a>

    <div class="container">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Satuan Besar</th>
                <th>Satuan Kecil</th>
                <th>Nilai Konversi</th>
                <th>Rumus Konversi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $index => $data)
                <tr>
                    <td>{{ $datas->firstItem() + $index }}</td>

                    <td>{{ $data->satuanBesar->nama ?? '-' }}</td>

                    <td>{{ $data->satuanKecil->nama ?? '-' }}</td>

                    <td>{{ $data->nilai_konversi }}</td>

                    <td>
                        @if ($data->satuanBesar && $data->satuanKecil)
                            1 {{ $data->satuanBesar->nama }} =
                            {{ $data->nilai_konversi }}
                            {{ $data->satuanKecil->nama }}
                        @else
                            Data satuan belum lengkap
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('satuankonversi.edit', $data->id) }}" class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <form action="{{ route('satuankonversi.destroy', $data->id) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Yakin ingin menghapus konversi satuan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Belum ada data konversi satuan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

        <div class="mt-3">
            {{ $datas->links() }}
        </div>
    </div>
@endsection
