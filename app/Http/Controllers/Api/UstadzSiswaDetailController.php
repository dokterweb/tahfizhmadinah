<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class UstadzSiswaDetailController extends Controller
{
   public function show(Siswa $siswa)
{
    $user = request()->user();

    /*
    |--------------------------------------------------------------------------
    | 1. Pastikan user memiliki data ustadz
    |--------------------------------------------------------------------------
    */

    $ustadz = $user->ustadz;

    if (!$ustadz) {
        return response()->json([
            'success' => false,
            'message' => 'Data ustadz tidak ditemukan.',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Pastikan ustadz memiliki akses ke siswa
    |--------------------------------------------------------------------------
    */

    $hasAccess = $ustadz->subKelas()
        ->where(
            'sub_kelas.id',
            $siswa->sub_kelas_id
        )
        ->exists();

    if (!$hasAccess) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki akses ke siswa ini.',
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Load data siswa dan seluruh riwayat
    |--------------------------------------------------------------------------
    */

    $siswa->load([
        'user',
        'subKelas',

        'sabaqHistories' => function ($query) {
            $query
                ->with('surat')
                ->orderByDesc('tgl_sabaq')
                ->orderByDesc('id');
        },

        'sabqiHistories' => function ($query) {
            $query
                ->with('surat')
                ->orderByDesc('tgl_sabqi')
                ->orderByDesc('id');
        },

        'manzilHistories' => function ($query) {
            $query
                ->with('surat')
                ->orderByDesc('tgl_manzil')
                ->orderByDesc('id');
        },
    ]);

    /*
    |--------------------------------------------------------------------------
    | 4. Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,

        'siswa' => [
            'id' => $siswa->id,

            'user_id' => $siswa->user_id,

            'nama_siswa' => $siswa->user?->name ?? '',

            'email' => $siswa->user?->email ?? '',

            'kelamin' => $siswa->kelamin,

            'tempat_lahir' => $siswa->tempat_lahir,

            'tgl_lahir' => $siswa->tgl_lahir,

            'alamat' => $siswa->alamat,

            'no_hp' => $siswa->no_hp,

            'sub_kelas' => $siswa->subKelas
                ? [
                    'id' => $siswa->subKelas->id,
                    'nama_sub_kelas' =>
                        $siswa->subKelas->nama_sub_kelas,
                ]
                : null,
        ],

        'sabaq' => $siswa->sabaqHistories
            ->map(function ($item) {
                return [
                    'id' => $item->id,

                    'surat_id' => $item->surat_id,

                    'surat_no' => $item->surat_no,

                    'surat' =>
                        $item->surat?->sura_name ?? '',

                    'dariayat' => $item->dariayat,

                    'sampaiayat' => $item->sampaiayat,

                    'tgl_sabaq' => $item->tgl_sabaq,

                    'nilai' => $item->nilai,

                    'keterangan' =>
                        $item->keterangan ?? '',
                ];
            })
            ->values(),

        'sabqi' => $siswa->sabqiHistories
            ->map(function ($item) {
                return [
                    'id' => $item->id,

                    'surat_id' => $item->surat_id,

                    'surat_no' => $item->surat_no,

                    'surat' =>
                        $item->surat?->sura_name ?? '',

                    'dariayat' => $item->dariayat,

                    'sampaiayat' => $item->sampaiayat,

                    'tgl_sabqi' => $item->tgl_sabqi,

                    'nilai' => $item->nilai,

                    'keterangan' =>
                        $item->keterangan ?? '',
                ];
            })
            ->values(),

        'manzil' => $siswa->manzilHistories
            ->map(function ($item) {
                return [
                    'id' => $item->id,

                    'surat_id' => $item->surat_id,

                    'surat_no' => $item->surat_no,

                    'surat' =>
                        $item->surat?->sura_name ?? '',

                    'dariayat' => $item->dariayat,

                    'sampaiayat' => $item->sampaiayat,

                    'tgl_manzil' => $item->tgl_manzil,

                    'nilai' => $item->nilai,

                    'keterangan' =>
                        $item->keterangan ?? '',
                ];
            })
            ->values(),
    ]);
}
}