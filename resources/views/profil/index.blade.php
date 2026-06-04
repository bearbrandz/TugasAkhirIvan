@extends('layout.conquer')

@section('title')

@section('content')
    <div class="max-w-5xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Profil Apotek</h1>
        @if ($profil)
            <div class="bg-white shadow-xl rounded-3xl p-8 md:p-12">
                <div class="flex flex-col items-center text-center">
                    @if ($profil->logo)
                        <img height="100px" src="{{ asset('/company_logo/' . $profil->logo) }}" alt="Company Image"
                            class="w-32 h-32 object-cover rounded-full border-4 border-blue-100 shadow-md mb-4" />
                        @if (auth()->user())
                            <a href="{{ url('profilapotek/uploadImage/' . $profil->id) }}"
                                class="btn btn-xs btn-default">Upload</a>
                        @endif
                    @else
                        <div
                            class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-sm mb-4">
                            No Logo
                        </div>
                        @if (auth()->user())
                            <a href="{{ url('profilapotek/uploadImage/' . $profil->id) }}"
                                class="btn btn-xs btn-default">Upload</a>
                        @endif
                    @endif

                    <!-- Nama Apotek -->
                    <h1 class="text-3xl font-bold text-blue-800 mb-2">{{ $profil->nama }}</h1>
                    <p class="text-gray-500">{{ $profil->deskripsi }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-gray-700 font-semibold">Alamat</h2>
                            <p class="text-gray-600">{{ $profil->alamat }}</p>
                        </div>

                        <div>
                            <h2 class="text-gray-700 font-semibold">No HP</h2>
                            <p class="text-gray-600">{{ $profil->no_hp }}</p>
                        </div>

                        <div>
                            <h2 class="text-gray-700 font-semibold">Email</h2>
                            <p class="text-gray-600">{{ $profil->email }}</p>
                        </div>

                        <div>
                            <h2 class="text-gray-700 font-semibold">Jam Operasional</h2>
                            <p class="text-gray-600">{{ $profil->jam_operasional }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h2 class="text-gray-700 font-semibold">Pemilik</h2>
                            <p class="text-gray-600">{{ $profil->user->nama ?? '-' }}</p>
                        </div>

                        @if (auth()->user())
                            <div class="mt-8">
                                <a href="{{ route('profilapoteks.edit', $profil->id) }}"
                                    class="inline-block bg-blue-600 text-white text-sm px-5 py-3 rounded-full shadow hover:bg-blue-700 transition">
                                    ✏️ Edit Profil
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="text-center mt-12">
                <p class="text-gray-600 text-lg mb-4">Belum ada profil apotek yang dibuat.</p>
                <a href="{{ url('profilapoteks/create') }}"
                    class="inline-block bg-green-600 text-white text-sm px-5 py-3 rounded-full shadow hover:bg-green-700 transition">
                    ➕ Buat Profil Apotek
                </a>
            </div>
        @endif
    </div>
@endsection
