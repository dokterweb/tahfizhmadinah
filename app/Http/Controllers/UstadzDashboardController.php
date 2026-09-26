<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Ustadz;
use App\Models\Kelasnya;
use App\Models\Sabaq_history;
use App\Models\Sabqi_history;
use App\Models\Manzil_history;
use Illuminate\Support\Facades\Auth;

class UstadzDashboardController extends Controller
{
    public function index()
    {
        $ustadz = Auth::user()->ustadz;

        $sabaqs = Sabaq_history::with(['surat','siswa.user',])
        ->where('ustadz_id', $ustadz->id)
        ->orderByDesc('tgl_sabaq')
        ->orderByDesc('id')
        ->limit(10)
        ->get();


       
        $sabqis = Sabqi_history::with(['surat','siswa.user',])
        ->where('ustadz_id', $ustadz->id)
        ->orderByDesc('tgl_sabqi')
        ->orderByDesc('id')
        ->limit(10)
        ->get();

        $manzils = Manzil_history::with([
            'surat',
            'siswa.user',
        ])
        ->where('ustadz_id', $ustadz->id)
        ->orderByDesc('tgl_manzil')
        ->orderByDesc('id')
        ->limit(10)
        ->get();


        return view('ustadzs.dashboard', compact(
            'ustadz',
            'sabaqs',
            'sabqis',
            'manzils'
        ));
    }
}