<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $modul  = $request->get('modul', '');

        // Default: tampilkan hari ini jika tidak ada filter tanggal sama sekali
        $isReset    = $request->has('reset');
        $hasFilter  = $request->hasAny(['search', 'modul', 'start_date', 'end_date']);

        $startDateRaw = $request->get('start_date', '');
        $endDateRaw   = $request->get('end_date', '');

        // Jika tidak ada filter tanggal sama sekali dan bukan reset, default ke hari ini
        if (!$startDateRaw && !$endDateRaw && !$isReset) {
            $startDateRaw = now()->toDateString();
            $endDateRaw   = now()->toDateString();
        }

        // Parse tanggal secara aman (handle format YYYY-MM-DD maupun DD/MM/YYYY)
        $startDate = '';
        $endDate   = '';

        if ($startDateRaw) {
            try {
                $startDate = Carbon::parse($startDateRaw)->toDateString();
            } catch (\Exception $e) {
                $startDate = '';
            }
        }

        if ($endDateRaw) {
            try {
                $endDate = Carbon::parse($endDateRaw)->toDateString();
            } catch (\Exception $e) {
                $endDate = '';
            }
        }

        $query = LogActivity::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_user', 'like', "%$search%")
                    ->orWhere('aksi', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%");
            });
        }

        if ($modul) {
            $query->where('modul', $modul);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $datas = $query->orderBy('created_at', 'desc')->paginate(20);

        $moduls = LogActivity::select('modul')->distinct()->pluck('modul');

        return view('log.index', compact('datas', 'search', 'modul', 'moduls', 'startDate', 'endDate'));
    }
}
