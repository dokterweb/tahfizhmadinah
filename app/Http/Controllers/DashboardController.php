<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Ustadz;
use App\Models\Kelasnya;
use App\Models\Sabaq_history;
use App\Models\Sabqi_history;
use App\Models\Manzil_history;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Siswa::count();
        $totalUstadz = Ustadz::count();
        $totalKelas = Kelasnya::count();

        // 5 history Sabaq terbaru
        $sabaqs = Sabaq_history::with(['surat','siswa.user'])
        ->orderByDesc('tgl_sabaq')
        ->orderByDesc('id')
        ->limit(5)
        ->get();

        // 5 history Sabqi terbaru
        $sabqis = Sabqi_history::with(['surat','siswa.user'])
        ->orderByDesc('tgl_sabqi')
        ->orderByDesc('id')
        ->limit(5)
        ->get();

        // 5 history Manzil terbaru
        $manzils = Manzil_history::with(['surat','siswa.user'])
        ->orderByDesc('tgl_manzil')
        ->orderByDesc('id')
        ->limit(5)
        ->get();

        return view('dashboard.index', compact(
            'totalSiswa',
            'totalUstadz',
            'totalKelas',
            'sabaqs',
            'sabqis',
            'manzils'
        ));
    }

}
