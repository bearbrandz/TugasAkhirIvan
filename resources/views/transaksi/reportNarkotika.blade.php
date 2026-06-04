@extends('layout.conquer')

@section('title', 'Laporan Narkotika')

@section('content')
    <div class="report-wrapper" style="padding:24px; border-radius:8px;">
    <style>
        * { box-sizing: border-box; }

        /* ===== SCREEN (DARK MODE INHERIT) ===== */
        .print-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid rgba(255,255,255,0.1); padding-bottom: 14px; margin-bottom: 18px; }
        .print-title { text-align: right; }
        .print-title h1 { font-size: 20px; margin-bottom: 4px; }
        .print-title p { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .filter-badge { display: inline-block; background: #3b82f6; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-top: 4px; }

        /* ===== PANEL EKSPOR (hidden when printing) ===== */
        .export-panel { background: linear-gradient(135deg, #1e3a5f, #162033); border: 1px solid #2a4a7f; border-radius: 10px; padding: 18px; margin-bottom: 20px; }
        .export-panel-title { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .export-badge { background: #e74c3c; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .export-panel h2 { margin: 0; font-size: 15px; font-weight: 700; color: #fff; }
        .export-panel p { color: #94a3b8; font-size: 12px; margin-bottom: 14px; }
        .export-form { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; }
        .export-form label { display: block; font-size: 11px; color: #94a3b8; margin-bottom: 4px; }
        .export-form select { background: #111827; color: #fff; border: 1px solid #374151; border-radius: 6px; padding: 7px 12px; font-size: 13px; }
        .btn-sipnap { background: #3b82f6; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-simona { background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }

        /* ===== TABLE ===== */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid rgba(255,255,255,0.1); padding: 8px 10px; text-align: left; vertical-align: top; word-break: break-word; }
        thead th { background: rgba(255,255,255,0.05); color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; }
        tbody tr:nth-child(even) td { background: rgba(255,255,255,0.02); }

        .col-identitas { width: 12%; }
        .col-obat      { width: 22%; }
        .col-stok      { width: 24%; }
        .col-pasien    { width: 28%; }
        .col-tanggal   { width: 10%; text-align: center; }
        .col-no        { width: 4%; text-align: center; }

        .cell-title { font-weight: 700; margin-bottom: 3px; font-size: 12px; }
        .cell-sub { display: block; color: #94a3b8; font-size: 11px; margin-top: 2px; }

        .stok-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
        .stok-item { border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; padding: 5px 7px; display: flex; align-items: center; justify-content: space-between; gap: 6px; }
        .stok-item.dipakai { border-color: rgba(248,113,113,0.5); background: rgba(248,113,113,0.1); }
        .stok-item.akhir   { border-color: rgba(74,222,128,0.5); background: rgba(74,222,128,0.1); }
        .stok-label { color: #94a3b8; font-size: 10px; white-space: nowrap; }
        .stok-value { font-weight: 700; font-size: 12px; white-space: nowrap; }

        tfoot th { background: rgba(255,255,255,0.05); font-weight: 700; font-size: 12px; }

        /* ===== ACTION BUTTONS ===== */
        .action-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; }
        .btn-back     { background: #64748b; color: #fff; }
        .btn-print    { background: #3b82f6; color: #fff; }
        .btn-csv      { background: #10b981; color: #fff; }

        /* ===== PRINT STYLES ===== */
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .main-content { padding: 0 !important; background: #fff !important; }
            body, .report-wrapper { padding: 0; background: #fff !important; color: #000 !important; }
            
            /* Print colors */
            .print-header { border-bottom: 3px solid #1e3a5f; }
            .print-title h1 { color: #1e3a5f; }
            .print-title p, .cell-sub, .stok-label { color: #64748b !important; }
            .filter-badge { background: #1e3a5f !important; }
            
            table { color: #000 !important; }
            th, td { border: 1px solid #cbd5e1 !important; }
            thead th { background: #1e3a5f !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr:nth-child(even) td { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tfoot th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            
            .stok-item { border: 1px solid #e2e8f0 !important; }
            .stok-item.dipakai { background: #fff5f5 !important; border-color: #fca5a5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stok-item.akhir   { background: #f0fdf4 !important; border-color: #86efac !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            
            .logo-apotek-print { color: #b45309 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .logo-m-print { border-color: #dc2626 !important; color: #dc2626 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .logo-edico-print { color: #dc2626 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 1.2cm; size: A4 landscape; }
        }
    </style>
    {{-- ACTION BAR (hidden on print) --}}
    <div class="action-bar no-print">
        <button onclick="window.history.back()" class="btn btn-back">&#8592; Kembali</button>
        <button onclick="window.print()" class="btn btn-print">&#128438; Print Laporan</button>
        <a href="{{ route('racikans.CsvNarkotika', request()->all()) }}" class="btn btn-csv">&#8659; Download CSV</a>
    </div>

    {{-- ===== PANEL EKSPOR SIPNAP & SIMONA ===== --}}
    <div class="export-panel no-print">
        <div class="export-panel-title">
            <span class="export-badge">PELAPORAN EKSTERNAL</span>
            <h2>Ekspor Data ke SIPNAP &amp; SIMONA</h2>
        </div>
        <p>Pilih bulan dan tahun periode pelaporan, lalu unduh file CSV sesuai format yang dibutuhkan sistem BPOM (SIPNAP) atau Dinas Kesehatan (SIMONA).</p>
        <form method="GET" id="exportForm" class="export-form" action="{{ route('racikans.reportNarkotika') }}">
            <div>
                <label>Bulan</label>
                <select name="bulan" onchange="this.form.submit()">
                    <option value="all" {{ request('bulan') == 'all' ? 'selected' : '' }}>Setahun Penuh</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('bulan', now()->month) == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(now()->year, $m, 1)->locale('id')->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label>Tahun</label>
                <select name="tahun" onchange="this.form.submit()">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="button" onclick="downloadEkspor('sipnap')" class="btn-sipnap">&#8659; Ekspor SIPNAP (BPOM)</button>
                <button type="button" onclick="downloadEkspor('simona')" class="btn-simona">&#8659; Ekspor SIMONA (Dinkes)</button>
            </div>
        </form>
    </div>

    {{-- ===== KOP LAPORAN ===== --}}
    <div class="print-header">
        <div style="font-family: 'Arial Black', Impact, sans-serif; font-size: 26px; line-height: 1.1; font-weight: 900; letter-spacing: 1px;">
            <span class="logo-apotek-print" style="color: #facc15 !important; text-transform: uppercase;">Apotek</span><br>
            <span class="logo-m-print" style="color: #ef4444 !important; border: 3px solid #ef4444; padding: 0 4px; margin-right: 1px; display: inline-block; line-height: 0.9;">M</span><span class="logo-edico-print" style="color: #ef4444 !important;">edico</span>
        </div>
        <div class="print-title">
            <h1>Laporan Narkotika dan Psikotropika</h1>
            <p>
                Filter Periode:
                <span class="filter-badge">
                    {{ $filterLabel ?? 'Semua Periode' }}
                </span>
                <br>Tanggal Cetak: {{ now()->format('d M Y, H:i') }}
            </p>
        </div>
    </div>

    {{-- ===== TABLE ===== --}}
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-identitas">Racikan / Batch</th>
                <th class="col-obat">Obat / Satuan / Distributor</th>
                <th class="col-stok">Pergerakan Stok</th>
                <th class="col-pasien">Pasien / Dokter</th>
                <th class="col-tanggal">Tgl Ambil</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $i => $d)
                <tr>
                    <td class="col-no" style="text-align:center;">{{ $i + 1 }}</td>
                    <td class="col-identitas">
                        <div class="cell-title">Racikan #{{ $d->racikan_id ?? '-' }}</div>
                        <span class="cell-sub">Batch: {{ $d->batch_id ?? '-' }}</span>
                    </td>
                    <td class="col-obat">
                        <div class="cell-title">{{ $d->nama_produk ?? '-' }}</div>
                        <span class="cell-sub">Satuan: {{ $d->nama_satuan ?? '-' }}</span>
                        <span class="cell-sub">Dist: {{ $d->nama_distributor ?? '-' }}</span>
                    </td>
                    <td class="col-stok">
                        <div class="stok-grid">
                            <div class="stok-item">
                                <span class="stok-label">Awal</span>
                                <span class="stok-value">{{ number_format($d->stok_awalbulan ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="stok-item">
                                <span class="stok-label">Masuk</span>
                                <span class="stok-value">{{ number_format($d->stok_diterima ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="stok-item dipakai">
                                <span class="stok-label">Dipakai</span>
                                <span class="stok-value">{{ number_format($d->stok_keluar ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="stok-item akhir">
                                <span class="stok-label">Akhir</span>
                                <span class="stok-value">{{ number_format($d->stok_setelah_transaksi ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="col-pasien">
                        <div class="cell-title">{{ $d->nama_pasien ?? '-' }}</div>
                        <span class="cell-sub">{{ $d->alamat_pasien ?? '-' }}</span>
                        <span class="cell-sub">Dokter: {{ $d->nama_dokter ?? '-' }}</span>
                        <span class="cell-sub">{{ $d->alamat_dokter ?? '-' }}</span>
                    </td>
                    <td class="col-tanggal">
                        @if(!empty($d->tgl_ambil))
                            {{ \Carbon\Carbon::parse($d->tgl_ambil)->format('d/m/Y') }}
                        @else -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#64748b;">
                        Tidak ada data laporan narkotika/psikotropika pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right;">Total Pemakaian:</th>
                <th>{{ number_format($total ?? $datas->sum('stok_keluar'), 0, ',', '.') }} item dipakai</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; text-align: right; font-size: 13px;">
        <p>Mengetahui, Apoteker Penanggung Jawab</p>
        <br><br><br>
        <p><strong>{{ auth()->user()->nama ?? 'Apoteker' }}</strong></p>
        <p>Apotek Medico</p>
    </div>

    <script>
        function downloadEkspor(tipe) {
            const form = document.getElementById('exportForm');
            const bulan = form.querySelector('[name=bulan]').value;
            const tahun = form.querySelector('[name=tahun]').value;
            const baseUrl = tipe === 'sipnap'
                ? '{{ route("racikans.exportSipnap") }}'
                : '{{ route("racikans.exportSimona") }}';
            window.location.href = baseUrl + '?bulan=' + bulan + '&tahun=' + tahun;
        }
    </script>
    </div>
@endsection