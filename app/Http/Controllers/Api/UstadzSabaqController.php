<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Sabaq_history;
use App\Models\Madina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UstadzSabaqController extends Controller
{
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

                'tgl_sabaq' => [
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

                'tgl_sabaq.required' =>
                    'Tanggal Sabaq wajib diisi.',

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
        | 4. Ambil data surat berdasarkan surat_id
        |--------------------------------------------------------------------------
        |
        | surat_id menunjuk ke:
        |
        | madina.id
        |
        | Sedangkan surat_no menunjuk ke:
        |
        | madina.sura_no
        |
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
        |
        | Contoh:
        |
        | Al-Fatihah
        | madina.id      = 1
        | madina.sura_no = 1
        |
        | Al-Baqarah
        | madina.id      = 8
        | madina.sura_no = 2
        |
        | Jadi kita TIDAK membandingkan:
        |
        | surat_id == surat_no
        |
        | Tetapi:
        |
        | madina.id -> surat_id
        | madina.sura_no -> surat_no
        |
        */

        if ((int) $surat->sura_no !== (int) $data['surat_no']) {
            return response()->json([
                'success' => false,
                'message' => 'Surat yang dipilih tidak sesuai dengan nomor surat.',
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
                'message' => 'Data ayat untuk surat tersebut tidak ditemukan.',
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
        | 9. Simpan Sabaq
        |--------------------------------------------------------------------------
        */

        $sabaq = Sabaq_history::create([
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

            'tgl_sabaq' => $data['tgl_sabaq'],

            'nilai' => $data['nilai'],

            'keterangan' =>
                $data['keterangan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 10. Load relasi surat
        |--------------------------------------------------------------------------
        */

        $sabaq->load('surat');

        /*
        |--------------------------------------------------------------------------
        | 11. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Sabaq berhasil disimpan.',

            'data' => [
                'id' => $sabaq->id,

                'siswa_id' => $sabaq->siswa_id,

                'ustadz_id' => $sabaq->ustadz_id,

                'sub_kelas_id' => $sabaq->sub_kelas_id,

                'surat_id' => $sabaq->surat_id,

                'surat_no' => $sabaq->surat_no,

                'surat' => $sabaq->surat?->sura_name ?? '',

                'dariayat' => $sabaq->dariayat,

                'sampaiayat' => $sabaq->sampaiayat,

                'tgl_sabaq' => $sabaq->tgl_sabaq,

                'nilai' => $sabaq->nilai,

                'keterangan' => $sabaq->keterangan,

                'created_at' => $sabaq->created_at,
            ],
        ], 201);
    }

    public function update(Request $request, Siswa $siswa,Sabaq_history $sabaq) 
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
        | 3. Pastikan Sabaq memang milik siswa tersebut
        |--------------------------------------------------------------------------
        */

        if ((int) $sabaq->siswa_id !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sabaq tidak ditemukan untuk siswa ini.',
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

                'tgl_sabaq' => [
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

                'tgl_sabaq.required' =>
                    'Tanggal Sabaq wajib diisi.',

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
        | 5. Ambil surat berdasarkan madina.id
        |--------------------------------------------------------------------------
        |
        | surat_id = madina.id
        | surat_no = madina.sura_no
        |
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
        | 10. Update Sabaq
        |--------------------------------------------------------------------------
        */

        $sabaq->update([
            'surat_id' => $data['surat_id'],

            'surat_no' => $data['surat_no'],

            'dariayat' => $data['dariayat'],

            'sampaiayat' => $data['sampaiayat'],

            'tgl_sabaq' => $data['tgl_sabaq'],

            'nilai' => $data['nilai'],

            'keterangan' =>
                $data['keterangan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 11. Load relasi surat
        |--------------------------------------------------------------------------
        */

        $sabaq->load('surat');

        /*
        |--------------------------------------------------------------------------
        | 12. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Sabaq berhasil diperbarui.',

            'data' => [
                'id' => $sabaq->id,

                'siswa_id' => $sabaq->siswa_id,

                'ustadz_id' => $sabaq->ustadz_id,

                'sub_kelas_id' => $sabaq->sub_kelas_id,

                'surat_id' => $sabaq->surat_id,

                'surat_no' => $sabaq->surat_no,

                'surat' =>
                    $sabaq->surat?->sura_name ?? '',

                'dariayat' => $sabaq->dariayat,

                'sampaiayat' => $sabaq->sampaiayat,

                'tgl_sabaq' => $sabaq->tgl_sabaq,

                'nilai' => $sabaq->nilai,

                'keterangan' =>
                    $sabaq->keterangan,

                'updated_at' =>
                    $sabaq->updated_at,
            ],
        ]);
    }

    public function destroy(Request $request,Siswa $siswa,Sabaq_history $sabaq)
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
        | 3. Pastikan Sabaq milik siswa tersebut
        |--------------------------------------------------------------------------
        */

        if ((int) $sabaq->siswa_id !== (int) $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sabaq tidak ditemukan untuk siswa ini.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Hapus Sabaq
        |--------------------------------------------------------------------------
        |
        | Sabaq_history menggunakan SoftDeletes.
        | Jadi data tidak benar-benar dihapus dari database.
        |
        */

        $sabaq->delete();

        /*
        |--------------------------------------------------------------------------
        | 5. Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Sabaq berhasil dihapus.',
        ]);
    }
}