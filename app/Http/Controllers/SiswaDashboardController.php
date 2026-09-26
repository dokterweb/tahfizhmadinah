<?php

namespace App\Http\Controllers;

use App\Models\Kelasnya;
use App\Models\Siswa;
use App\Models\Ustadz;
use App\Models\Sabaq_history;
use App\Models\Sabqi_history;
use App\Models\Manzil_history;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
class SiswaDashboardController extends Controller
{
    public function index()
    {
        $siswa = Auth::user()->siswa;

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $totalSiswa = Siswa::count();
        $totalUstadz = Ustadz::count();
        $totalKelas = Kelasnya::count();


        /*
        |--------------------------------------------------------------------------
        | Sabaq Terbaru - hanya siswa login
        |--------------------------------------------------------------------------
        */

        $sabaqs = Sabaq_history::with([
            'surat',
            'siswa.user',
            'ustadz.user',
            'subKelas',
        ])
        ->where('siswa_id', $siswa->id)
        ->orderByDesc('tgl_sabaq')
        ->orderByDesc('id')
        ->limit(5)
        ->get();


        /*
        |--------------------------------------------------------------------------
        | Sabqi Terbaru - hanya siswa login
        |--------------------------------------------------------------------------
        */

        $sabqis = Sabqi_history::with([
            'surat',
            'siswa.user',
            'ustadz.user',
            'subKelas',
        ])
        ->where('siswa_id', $siswa->id)
        ->orderByDesc('tgl_sabqi')
        ->orderByDesc('id')
        ->limit(5)
        ->get();


        /*
        |--------------------------------------------------------------------------
        | Manzil Terbaru - hanya siswa login
        |--------------------------------------------------------------------------
        */

        $manzils = Manzil_history::with([
            'surat',
            'siswa.user',
            'ustadz.user',
            'subKelas',
        ])
        ->where('siswa_id', $siswa->id)
        ->orderByDesc('tgl_manzil')
        ->orderByDesc('id')
        ->limit(5)
        ->get();


        return view('siswas.dashboard', compact(
            'siswa',
            'totalSiswa',
            'totalUstadz',
            'totalKelas',
            'sabaqs',
            'sabqis',
            'manzils'
        ));
    }
}