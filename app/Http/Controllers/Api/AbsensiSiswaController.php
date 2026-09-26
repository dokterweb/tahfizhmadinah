<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi_siswa;
use App\Models\Siswa;
use App\Models\Ustadz;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    public function index(Request $request)
    {
        $ustadz = Ustadz::where(
            'user_id',
            auth()->id()
        )->first();

        if (!$ustadz) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ustadz tidak ditemukan.'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Tanggal
        |--------------------------------------------------------------------------
        */

        $startDate = $request->input(
            'start_date',
            Carbon::now()->startOfMonth()->toDateString()
        );

        $endDate = $request->input(
            'end_date',
            Carbon::now()->toDateString()
        );

        /*
        |--------------------------------------------------------------------------
        | Filter Sub Kelas
        |--------------------------------------------------------------------------
        */

        $subKelasId = $request->input('sub_kelas_id');

        /*
        |--------------------------------------------------------------------------
        | Query Absensi
        |--------------------------------------------------------------------------
        */

        $query = Absensi_siswa::whereHas(
            'siswa',
            function ($query) use ($ustadz, $subKelasId) {

                $query->where(
                    'ustadz_id',
                    $ustadz->id
                );

                /*
                | Jika sub kelas dipilih,
                | tambahkan filter sub kelas.
                */
                if ($subKelasId) {
                    $query->where(
                        'sub_kelas_id',
                        $subKelasId
                    );
                }
            }
        )
        ->with([
            'siswa.user',
            'siswa.kelasnya',
            'siswa.subKelas',
        ])
        ->whereBetween(
            'tgl_absen',
            [
                $startDate,
                $endDate
            ]
        )
        ->orderByDesc('tgl_absen')
        ->orderByDesc('id');

        $absensi = $query->get();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => 'success',

            'data' => $absensi,

            'start_date' => $startDate,

            'end_date' => $endDate,

            'sub_kelas_id' => $subKelasId,
        ]);
    }

    public function siswaBySubKelas($sub_kelas_id)
    {
        $ustadz = Ustadz::where(
            'user_id',
            auth()->id()
        )->first();

        if (!$ustadz) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ustadz tidak ditemukan.'
            ], 404);
        }

        $siswas = Siswa::with('user')
            ->where('ustadz_id', $ustadz->id)
            ->where('sub_kelas_id', $sub_kelas_id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $siswas,
        ]);
    }    

    public function store(Request $request)
    {
        $request->validate([
            'sub_kelas_id' => 'required|integer',
            'tgl_absen' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'required|in:hadir,absen,izin',
        ]);

        $ustadz = Ustadz::where(
            'user_id',
            auth()->id()
        )->first();

        if (!$ustadz) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ustadz tidak ditemukan.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil siswa yang memang menjadi tanggung jawab ustadz
        |--------------------------------------------------------------------------
        */

        $siswas = Siswa::where(
            'ustadz_id',
            $ustadz->id
        )
        ->where(
            'sub_kelas_id',
            $request->sub_kelas_id
        )
        ->get();

        if ($siswas->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada siswa pada sub kelas tersebut.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan semua siswa sudah memiliki status
        |--------------------------------------------------------------------------
        */

        $statusData = $request->status;

        foreach ($siswas as $siswa) {

            if (!isset($statusData[$siswa->id])) {
                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'Status absensi untuk siswa ID '
                        . $siswa->id
                        . ' belum diisi.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Cek apakah absensi untuk sub kelas dan tanggal tersebut
        | sudah pernah dibuat
        |--------------------------------------------------------------------------
        */

        $siswaIds = $siswas->pluck('id')->toArray();

        $sudahAda = Absensi_siswa::whereDate(
            'tgl_absen',
            $request->tgl_absen
        )
        ->whereIn(
            'siswa_id',
            $siswaIds
        )
        ->exists();

        if ($sudahAda) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'Absensi untuk sub kelas ini pada tanggal '
                    . $request->tgl_absen
                    . ' sudah dibuat.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan semua absensi
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $siswas,
            $statusData,
            $request
        ) {

            foreach ($siswas as $siswa) {

                Absensi_siswa::create([
                    'tgl_absen' => $request->tgl_absen,
                    'status' => $statusData[$siswa->id],
                    'siswa_id' => $siswa->id,
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi siswa berhasil disimpan.',
        ]);
    }

    public function siswaAbsensi(Request $request)
    {
        $siswa = Siswa::where(
            'user_id',
            auth()->id()
        )->first();

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $startDate = $request->input(
            'start_date',
            Carbon::now()->startOfMonth()->toDateString()
        );

        $endDate = $request->input(
            'end_date',
            Carbon::now()->toDateString()
        );

        $absensi = Absensi_siswa::with([
            'siswa.user',
            'siswa.kelasnya',
            'siswa.subKelas',
        ])
        ->where(
            'siswa_id',
            $siswa->id
        )
        ->whereBetween(
            'tgl_absen',
            [
                $startDate,
                $endDate
            ]
        )
        ->orderByDesc('tgl_absen')
        ->orderByDesc('id')
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $absensi,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }    
}