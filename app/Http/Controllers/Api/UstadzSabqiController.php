<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Sabqi_history;
use App\Models\Madina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UstadzSabqiController extends Controller
{
    public function index(Request $request, Siswa $siswa)
    {
        $user = $request->user();

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
        | 2. Pastikan ustadz memiliki akses ke sub kelas siswa
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
        | 3. Ambil history Sabqi siswa
        |--------------------------------------------------------------------------
        */

        $sabqi = Sabqi_history::with('surat')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tgl_sabqi')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Data Sabqi berhasil diambil.',
            'data' => $sabqi->map(function ($item) {
                return [
                    'id' => $item->id,

                    'siswa_id' => $item->siswa_id,

                    'ustadz_id' => $item->ustadz_id,

                    'sub_kelas_id' => $item->sub_kelas_id,

                    'surat_id' => $item->surat_id,

                    'surat_no' => $item->surat_no,

                    'surat' => $item->surat?->sura_name ?? '',

                    'dariayat' => $item->dariayat,

                    'sampaiayat' => $item->sampaiayat,

                    'tgl_sabqi' => $item->tgl_sabqi,

                    'nilai' => $item->nilai,

                    'keterangan' => $item->keterangan,

                    'created_at' => $item->created_at,

                    'updated_at' => $item->updated_at,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request, Siswa $siswa)
    {
        $user = $request->user();

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
        | 2. Pastikan ustadz memiliki akses ke sub kelas siswa
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
        | 3. Validasi input
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(
            $request->all(),
            [
                'surat_id' => [
                    'required',
                    'integer',
                    'exists:madina,id',
                ],

                'surat_no' => [
                    'required',
                    'integer',
                    'exists:madina,sura_no',
                ],

                'dariayat' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'sampaiayat' => [
                    'required',
                    'integer',
                    'gte:dariayat',
                ],

                'tgl_sabqi' => [
                    'required',
                    'date',
                ],

                'nilai' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:100',
                ],

                'keterangan' => [
                    'nullable',
                    'string',
                ],
            ],
            [
                'surat_id.required' =>
                    'Surat wajib dipilih.',

                'surat_id.exists' =>
                    'Surat tidak ditemukan.',

                'surat_no.required' =>
                    'Nomor surat wajib diisi.',

                'surat_no.exists' =>
                    'Nomor surat tidak ditemukan.',

                'dariayat.required' =>
                    'Ayat awal wajib diisi.',

                'dariayat.min' =>
                    'Ayat awal minimal 1.',

                'sampaiayat.required' =>
                    'Ayat akhir wajib diisi.',

                'sampaiayat.gte' =>
                    'Ayat akhir harus lebih besar atau sama dengan ayat awal.',

                'tgl_sabqi.required' =>
                    'Tanggal Sabqi wajib diisi.',

                'nilai.required' =>
                    'Nilai wajib diisi.',

                'nilai.min' =>
                    'Nilai minimal 0.',

                'nilai.max' =>
                    'Nilai maksimal 100.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        /*
        |--------------------------------------------------------------------------
        | 4. Ambil surat berdasarkan surat_id
        |--------------------------------------------------------------------------
        */

        $surat = Madina::where(
            'id',
            $data['surat_id']
        )->first();

        if (!$surat) {
            return response()->json([
                'success' => false,
                'message' => 'Surat tidak ditemukan.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Pastikan surat_id dan surat_no konsisten
        |--------------------------------------------------------------------------
        */

        if ((int) $surat->sura_no !== (int) $data['surat_no']) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Surat yang dipilih tidak sesuai dengan nomor surat.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Pastikan ayat berada dalam surat yang dipilih
        |--------------------------------------------------------------------------
        */

        $maxAyat = Madina::where(
            'sura_no',
            $data['surat_no']
        )->max('aya_no');

        if (!$maxAyat) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Data ayat untuk surat tersebut tidak ditemukan.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Validasi ayat awal
        |--------------------------------------------------------------------------
        */

        if ($data['dariayat'] > $maxAyat) {
            return response()->json([
                'success' => false,
                'message' =>
                    "Ayat awal tidak valid. Surat {$surat->sura_name} memiliki {$maxAyat} ayat.",
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Validasi ayat akhir
        |--------------------------------------------------------------------------
        */

        if ($data['sampaiayat'] > $maxAyat) {
            return response()->json([
                'success' => false,
                'message' =>
                    "Ayat akhir tidak valid. Surat {$surat->sura_name} memiliki {$maxAyat} ayat.",
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Simpan Sabqi
        |--------------------------------------------------------------------------
        */

        $sabqi = Sabqi_history::create([
            'siswa_id' => $siswa->id,

            // Ditentukan oleh Laravel
            'ustadz_id' => $ustadz->id,

            // Diambil dari siswa
            'sub_kelas_id' => $siswa->sub_kelas_id,

            // madina.id
            'surat_id' => $data['surat_id'],

            // madina.sura_no
            'surat_no' => $data['surat_no'],

            'dariayat' => $data['dariayat'],
            'sampaiayat' => $data['sampaiayat'],

            'tgl_sabqi' => $data['tgl_sabqi'],

            'nilai' => $data['nilai'],

            'keterangan' =>
                $data['keterangan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 10. Load relasi surat
        |--------------------------------------------------------------------------
        */

        $sabqi->load('surat');

        /*
        |--------------------------------------------------------------------------
        | 11. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Sabqi berhasil disimpan.',

            'data' => [
                'id' => $sabqi->id,

                'siswa_id' => $sabqi->siswa_id,

                'ustadz_id' => $sabqi->ustadz_id,

                'sub_kelas_id' => $sabqi->sub_kelas_id,

                'surat_id' => $sabqi->surat_id,

                'surat_no' => $sabqi->surat_no,

                'surat' => $sabqi->surat?->sura_name ?? '',

                'dariayat' => $sabqi->dariayat,

                'sampaiayat' => $sabqi->sampaiayat,

                'tgl_sabqi' => $sabqi->tgl_sabqi,

                'nilai' => $sabqi->nilai,

                'keterangan' => $sabqi->keterangan,

                'created_at' => $sabqi->created_at,
            ],
        ], 201);
    }


    /**
     * Update Sabqi
     */
    public function update(
        Request $request,
        Siswa $siswa,
        Sabqi_history $sabqi
    ) {
        $user = $request->user();

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
        | 2. Pastikan ustadz memiliki akses ke sub kelas siswa
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
        | 3. Pastikan Sabqi memang milik siswa tersebut
        |--------------------------------------------------------------------------
        */

        if ((int) $sabqi->siswa_id !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sabqi tidak ditemukan untuk siswa ini.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Validasi input
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(
            $request->all(),
            [
                'surat_id' => [
                    'required',
                    'integer',
                    'exists:madina,id',
                ],

                'surat_no' => [
                    'required',
                    'integer',
                    'exists:madina,sura_no',
                ],

                'dariayat' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'sampaiayat' => [
                    'required',
                    'integer',
                    'gte:dariayat',
                ],

                'tgl_sabqi' => [
                    'required',
                    'date',
                ],

                'nilai' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:100',
                ],

                'keterangan' => [
                    'nullable',
                    'string',
                ],
            ],
            [
                'surat_id.required' =>
                    'Surat wajib dipilih.',

                'surat_id.exists' =>
                    'Surat tidak ditemukan.',

                'surat_no.required' =>
                    'Nomor surat wajib diisi.',

                'surat_no.exists' =>
                    'Nomor surat tidak ditemukan.',

                'dariayat.required' =>
                    'Ayat awal wajib diisi.',

                'dariayat.min' =>
                    'Ayat awal minimal 1.',

                'sampaiayat.required' =>
                    'Ayat akhir wajib diisi.',

                'sampaiayat.gte' =>
                    'Ayat akhir harus lebih besar atau sama dengan ayat awal.',

                'tgl_sabqi.required' =>
                    'Tanggal Sabqi wajib diisi.',

                'nilai.required' =>
                    'Nilai wajib diisi.',

                'nilai.min' =>
                    'Nilai minimal 0.',

                'nilai.max' =>
                    'Nilai maksimal 100.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        /*
        |--------------------------------------------------------------------------
        | 5. Ambil surat
        |--------------------------------------------------------------------------
        */

        $surat = Madina::where(
            'id',
            $data['surat_id']
        )->first();

        if (!$surat) {
            return response()->json([
                'success' => false,
                'message' => 'Surat tidak ditemukan.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Pastikan surat_id dan surat_no sesuai
        |--------------------------------------------------------------------------
        */

        if ((int) $surat->sura_no !== (int) $data['surat_no']) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Surat yang dipilih tidak sesuai dengan nomor surat.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Cari jumlah ayat maksimal surat
        |--------------------------------------------------------------------------
        */

        $maxAyat = Madina::where(
            'sura_no',
            $data['surat_no']
        )->max('aya_no');

        if (!$maxAyat) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Data ayat untuk surat tersebut tidak ditemukan.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Validasi ayat awal
        |--------------------------------------------------------------------------
        */

        if ($data['dariayat'] > $maxAyat) {
            return response()->json([
                'success' => false,
                'message' =>
                    "Ayat awal tidak valid. Surat {$surat->sura_name} memiliki {$maxAyat} ayat.",
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Validasi ayat akhir
        |--------------------------------------------------------------------------
        */

        if ($data['sampaiayat'] > $maxAyat) {
            return response()->json([
                'success' => false,
                'message' =>
                    "Ayat akhir tidak valid. Surat {$surat->sura_name} memiliki {$maxAyat} ayat.",
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 10. Update Sabqi
        |--------------------------------------------------------------------------
        */

        $sabqi->update([
            'surat_id' => $data['surat_id'],

            'surat_no' => $data['surat_no'],

            'dariayat' => $data['dariayat'],

            'sampaiayat' => $data['sampaiayat'],

            'tgl_sabqi' => $data['tgl_sabqi'],

            'nilai' => $data['nilai'],

            'keterangan' =>
                $data['keterangan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 11. Load relasi surat
        |--------------------------------------------------------------------------
        */

        $sabqi->load('surat');

        /*
        |--------------------------------------------------------------------------
        | 12. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Sabqi berhasil diperbarui.',

            'data' => [
                'id' => $sabqi->id,

                'siswa_id' => $sabqi->siswa_id,

                'ustadz_id' => $sabqi->ustadz_id,

                'sub_kelas_id' => $sabqi->sub_kelas_id,

                'surat_id' => $sabqi->surat_id,

                'surat_no' => $sabqi->surat_no,

                'surat' =>
                    $sabqi->surat?->sura_name ?? '',

                'dariayat' => $sabqi->dariayat,

                'sampaiayat' => $sabqi->sampaiayat,

                'tgl_sabqi' => $sabqi->tgl_sabqi,

                'nilai' => $sabqi->nilai,

                'keterangan' =>
                    $sabqi->keterangan,

                'updated_at' =>
                    $sabqi->updated_at,
            ],
        ]);
    }


    /**
     * Hapus Sabqi
     */
    public function destroy(
        Request $request,
        Siswa $siswa,
        Sabqi_history $sabqi
    ) {
        $user = $request->user();

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
        | 2. Pastikan ustadz memiliki akses ke sub kelas siswa
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
        | 3. Pastikan Sabqi milik siswa tersebut
        |--------------------------------------------------------------------------
        */

        if ((int) $sabqi->siswa_id !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sabqi tidak ditemukan untuk siswa ini.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Hapus Sabqi
        |--------------------------------------------------------------------------
        |
        | Sabqi_history menggunakan SoftDeletes.
        | Jadi data tidak benar-benar dihapus dari database.
        |
        */

        $sabqi->delete();

        /*
        |--------------------------------------------------------------------------
        | 5. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Sabqi berhasil dihapus.',
        ]);
    }
}