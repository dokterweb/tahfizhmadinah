<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SiswaSabqiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $siswa = $user->siswa()->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'tanggal_dari' => [
                'nullable',
                'date',
            ],

            'tanggal_sampai' => [
                'nullable',
                'date',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $query = $siswa->sabqiHistories()
            ->with('surat')
            ->orderByDesc('tgl_sabqi')
            ->orderByDesc('id');

        /*
        |--------------------------------------------------------------------------
        | Filter tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal_dari')) {
            $query->where(
                'tgl_sabqi',
                '>=',
                $request->tanggal_dari
            );
        }

        if ($request->filled('tanggal_sampai')) {
            $query->where(
                'tgl_sabqi',
                '<=',
                $request->tanggal_sampai
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = $request->integer(
            'per_page',
            10
        );

        $paginator = $query->paginate(
            $perPage
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'siswa' => [
                'id' => $siswa->id,
                'nama_siswa' => $user->name,
            ],

            'sabqi' => collect(
                $paginator->items()
            )->map(function ($item) {
                return [
                    'id' => $item->id,

                    'surat_id' =>
                        $item->surat_id,

                    'surat_no' =>
                        $item->surat_no,

                    'surat' =>
                        $item->surat?->sura_name,

                    'dariayat' =>
                        $item->dariayat,

                    'sampaiayat' =>
                        $item->sampaiayat,

                    'tgl_sabqi' =>
                        $item->tgl_sabqi,

                    'nilai' =>
                        $item->nilai,

                    'keterangan' =>
                        $item->keterangan,
                ];
            })->values(),

            'pagination' => [
                'current_page' =>
                    $paginator->currentPage(),

                'last_page' =>
                    $paginator->lastPage(),

                'per_page' =>
                    $paginator->perPage(),

                'total' =>
                    $paginator->total(),

                'from' =>
                    $paginator->firstItem(),

                'to' =>
                    $paginator->lastItem(),
            ],
        ]);
    }
}