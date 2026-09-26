<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Manzil_history;
use App\Models\Madina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UstadzManzilController extends Controller
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
        | 3. Ambil history manzil siswa
        |--------------------------------------------------------------------------
        */

        $manzil = Manzil_history::with('surat')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tgl_manzil')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Data manzil berhasil diambil.',
            'data' => $manzil->map(function ($item) {
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

                    'tgl_manzil' => $item->tgl_manzil,

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

                'tgl_manzil' => [
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

                'tgl_manzil.required' =>
                    'Tanggal manzil wajib diisi.',

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
        | 9. Simpan manzil
        |--------------------------------------------------------------------------
        */

        $manzil = Manzil_history::create([
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

            'tgl_manzil' => $data['tgl_manzil'],

            'nilai' => $data['nilai'],

            'keterangan' =>
                $data['keterangan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 10. Load relasi surat
        |--------------------------------------------------------------------------
        */

        $manzil->load('surat');

        /*
        |--------------------------------------------------------------------------
        | 11. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'manzil berhasil disimpan.',

            'data' => [
                'id' => $manzil->id,

                'siswa_id' => $manzil->siswa_id,

                'ustadz_id' => $manzil->ustadz_id,

                'sub_kelas_id' => $manzil->sub_kelas_id,

                'surat_id' => $manzil->surat_id,

                'surat_no' => $manzil->surat_no,

                'surat' => $manzil->surat?->sura_name ?? '',

                'dariayat' => $manzil->dariayat,

                'sampaiayat' => $manzil->sampaiayat,

                'tgl_manzil' => $manzil->tgl_manzil,

                'nilai' => $manzil->nilai,

                'keterangan' => $manzil->keterangan,

                'created_at' => $manzil->created_at,
            ],
        ], 201);
    }


    /**
     * Update manzil
     */
    public function update(
        Request $request,
        Siswa $siswa,
        Manzil_history $manzil
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
        | 3. Pastikan manzil memang milik siswa tersebut
        |--------------------------------------------------------------------------
        */

        if ((int) $manzil->siswa_id !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data manzil tidak ditemukan untuk siswa ini.',
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

                'tgl_manzil' => [
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

                'tgl_manzil.required' =>
                    'Tanggal manzil wajib diisi.',

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
        | 10. Update manzil
        |--------------------------------------------------------------------------
        */

        $manzil->update([
            'surat_id' => $data['surat_id'],

            'surat_no' => $data['surat_no'],

            'dariayat' => $data['dariayat'],

            'sampaiayat' => $data['sampaiayat'],

            'tgl_manzil' => $data['tgl_manzil'],

            'nilai' => $data['nilai'],

            'keterangan' =>
                $data['keterangan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 11. Load relasi surat
        |--------------------------------------------------------------------------
        */

        $manzil->load('surat');

        /*
        |--------------------------------------------------------------------------
        | 12. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'manzil berhasil diperbarui.',

            'data' => [
                'id' => $manzil->id,

                'siswa_id' => $manzil->siswa_id,

                'ustadz_id' => $manzil->ustadz_id,

                'sub_kelas_id' => $manzil->sub_kelas_id,

                'surat_id' => $manzil->surat_id,

                'surat_no' => $manzil->surat_no,

                'surat' =>
                    $manzil->surat?->sura_name ?? '',

                'dariayat' => $manzil->dariayat,

                'sampaiayat' => $manzil->sampaiayat,

                'tgl_manzil' => $manzil->tgl_manzil,

                'nilai' => $manzil->nilai,

                'keterangan' =>
                    $manzil->keterangan,

                'updated_at' =>
                    $manzil->updated_at,
            ],
        ]);
    }


    /**
     * Hapus manzil
     */
    public function destroy(
        Request $request,
        Siswa $siswa,
        Manzil_history $manzil
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
        | 3. Pastikan manzil milik siswa tersebut
        |--------------------------------------------------------------------------
        */

        if ((int) $manzil->siswa_id !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data manzil tidak ditemukan untuk siswa ini.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Hapus manzil
        |--------------------------------------------------------------------------
        |
        | Manzil_history menggunakan SoftDeletes.
        | Jadi data tidak benar-benar dihapus dari database.
        |
        */

        $manzil->delete();

        /*
        |--------------------------------------------------------------------------
        | 5. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'manzil berhasil dihapus.',
        ]);
    }
}