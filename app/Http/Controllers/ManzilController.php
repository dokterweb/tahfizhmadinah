<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Manzil;
use Illuminate\Http\Request;
use App\Exports\ManzilExport;
use App\Models\Manzil_history;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ManzilController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Jika yang login adalah ustadz
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->hasRole('ustadz')) {

            // Ambil data ustadz yang sedang login
            $ustadz = auth()->user()->ustadz;

            if (!$ustadz) {
                abort(403, 'Data ustadz tidak ditemukan.');
            }

            /*
            |--------------------------------------------------------------------------
            | Ambil siswa yang menjadi tanggung jawab ustadz
            |--------------------------------------------------------------------------
            */

            $manzils = Siswa::with([
                'user',
                'kelasnya',
                'subKelas',
            ])
            ->where('ustadz_id', $ustadz->id)
            ->orderBy('user_id')
            ->get();

        } else {

            /*
            |--------------------------------------------------------------------------
            | Admin melihat semua siswa
            |--------------------------------------------------------------------------
            */

            $manzils = Siswa::with([
                'user',
                'kelasnya',
                'subKelas',
                'ustadz.user',
            ])
            ->orderBy('user_id')
            ->get();
        }

        return view(
            'manzils.index',
            compact('manzils')
        );
    }

   public function showmanzilHistory($siswa_id)
    {
        $siswa = Siswa::with([
            'user',
            'kelasnya',
        ])->findOrFail($siswa_id);

        $manzilHistories = Manzil_history::with([
            'surat',
            'ustadz.user',
            'subKelas',
        ])
        ->where('siswa_id', $siswa_id)
        ->orderByDesc('tgl_manzil')
        ->orderByDesc('id')
        ->get();

        $surat = DB::table('madina')
            ->select(
                'sura_no',
                'sura_name',
                DB::raw('MIN(id) as id'),
                DB::raw('COUNT(sura_no) as qty_sura')
            )
            ->groupBy('sura_no', 'sura_name')
            ->orderBy('sura_no')
            ->get();

        return view('manzils.history', compact(
            'siswa',
            'siswa_id',
            'manzilHistories',
            'surat'
        ));
    }

    public function getSuratmanzil($sura_no)
    {
        // Mengambil data surat berdasarkan sura_no
        $suratData = DB::table('madina')
            ->where('sura_no', $sura_no)
            ->orderBy('page', 'asc')  // Mengurutkan berdasarkan halaman pertama (ascending)
            ->first();  // Ambil baris pertama dari hasil query
    
        // Mengambil halaman pertama (start_page) dan halaman terakhir (end_page)
        $pages = DB::table('madina')
            ->where('sura_no', $sura_no)
            ->selectRaw('MIN(page) AS start_page, MAX(page) AS end_page')  // Mengambil halaman pertama dan terakhir
            ->first();
    
        // Cek apakah data surat ditemukan
        if ($suratData && $pages) {
            // Membuat objek hasil dengan semua data yang diperlukan
            $result = (object)[
                'suratId' => $suratData->id, // ID dari surat
                'no_surat' => $suratData->sura_no, // Nomor surat
                'jozz' => $suratData->jozz, // Juz
                'sura_name' => $suratData->sura_name, // Nama surat
                'start_page' => $pages->start_page, // Halaman pertama
                'end_page' => $pages->end_page, // Halaman terakhir
            ];
    
            return response()->json(['status' => 'success', 'data' => $result]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Surat tidak ditemukan'], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'      => 'required|integer|exists:siswas,id',
            'ustadz_id'     => 'required|integer|exists:ustadzs,id',
            'sub_kelas_id'  => 'required|integer|exists:sub_kelas,id',

            'tgl_manzil'     => 'required|date',

            'surat_no'      => 'required|integer|exists:madina,sura_no',

            'dariayat'      => 'required|integer|min:1',
            'sampaiayat'    => 'required|integer|min:1',

            'nilai'         => 'required|integer|min:0',

            'keterangan'    => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pastikan siswa memang berada di sub kelas tersebut
        |--------------------------------------------------------------------------
        */

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        if ((int) $siswa->sub_kelas_id !== (int) $validated['sub_kelas_id']) {
            return redirect()
                ->back()
                ->withErrors([
                    'sub_kelas_id' => 'Sub kelas tidak sesuai dengan siswa.'
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan ustadz memang mengajar sub kelas tersebut
        |--------------------------------------------------------------------------
        */

        $ustadzMengajar = DB::table('ustadz_sub_kelas')
            ->where('ustadz_id', $validated['ustadz_id'])
            ->where('sub_kelas_id', $validated['sub_kelas_id'])
            ->exists();

        if (!$ustadzMengajar) {
            return redirect()
                ->back()
                ->withErrors([
                    'ustadz_id' => 'Ustadz tersebut tidak mengajar sub kelas ini.'
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil data surat
        |--------------------------------------------------------------------------
        */

        $surat = DB::table('madina')
            ->where('sura_no', $validated['surat_no'])
            ->first();

        if (!$surat) {
            return redirect()
                ->back()
                ->withErrors([
                    'surat_no' => 'Surat tidak ditemukan.'
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan manzil History
        |--------------------------------------------------------------------------
        */

        $manzilHistory = new Manzil_history();

        $manzilHistory->siswa_id = $validated['siswa_id'];
        $manzilHistory->ustadz_id = $validated['ustadz_id'];
        $manzilHistory->sub_kelas_id = $validated['sub_kelas_id'];

        $manzilHistory->surat_id = $surat->id;
        $manzilHistory->surat_no = $surat->sura_no;

        $manzilHistory->dariayat = $validated['dariayat'];
        $manzilHistory->sampaiayat = $validated['sampaiayat'];

        $manzilHistory->tgl_manzil = $validated['tgl_manzil'];

        $manzilHistory->nilai = $validated['nilai'];
        $manzilHistory->keterangan = $validated['keterangan'] ?? null;

        $manzilHistory->save();

        /*
        |--------------------------------------------------------------------------
        | Kembali ke halaman history siswa
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('manzil-history.show', [
                'siswa_id' => $validated['siswa_id']
            ])
            ->with('success', 'Data manzil berhasil disimpan.');
    }

    public function edit($siswa_id, $id)
    {
        $manzilHistory = Manzil_history::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Pastikan history memang milik siswa yang sedang dibuka
        |--------------------------------------------------------------------------
        */

        if ((int) $manzilHistory->siswa_id !== (int) $siswa_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data manzil tidak sesuai dengan siswa.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil data surat berdasarkan surat_id
        |--------------------------------------------------------------------------
        */

        $surat = DB::table('madina')
            ->select(
                'id',
                'sura_no',
                'sura_name',
                'jozz',
                'page'
            )
            ->where('id', $manzilHistory->surat_id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Daftar surat
        |--------------------------------------------------------------------------
        */

        $suratList = DB::table('madina')
            ->select(
                'sura_no',
                'sura_name'
            )
            ->groupBy(
                'sura_no',
                'sura_name'
            )
            ->orderBy('sura_no')
            ->get();

        if (!$surat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data surat tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',

            'data' => [
                'id'          => $manzilHistory->id,
                'siswa_id'    => $manzilHistory->siswa_id,
                'ustadz_id'   => $manzilHistory->ustadz_id,
                'sub_kelas_id'=> $manzilHistory->sub_kelas_id,
                'tgl_manzil'   => $manzilHistory->tgl_manzil,
                'surat_id'    => $manzilHistory->surat_id,
                'surat_no'    => $manzilHistory->surat_no,
                'dariayat'    => $manzilHistory->dariayat,
                'sampaiayat'  => $manzilHistory->sampaiayat,
                'nilai'       => $manzilHistory->nilai,
                'keterangan'  => $manzilHistory->keterangan,
            ],

            'surat' => [
                'id'          => $surat->id,
                'sura_no'     => $surat->sura_no,
                'sura_name'   => $surat->sura_name,
                'jozz'        => $surat->jozz,
                'page'        => $surat->page,
            ],

            'suratList' => $suratList,
        ]);
    }

        
    public function update(Request $request, $id)
    {
        Log::info('Data update manzil History:', $request->all());

        try {

            /*
            |--------------------------------------------------------------------------
            | Validasi
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'tgl_manzil' => [
                    'required',
                    'date'
                ],

                'surat_no' => [
                    'required',
                    'integer',
                    'exists:madina,sura_no'
                ],

                'dariayat' => [
                    'required',
                    'integer',
                    'min:1'
                ],

                'sampaiayat' => [
                    'required',
                    'integer',
                    'min:1'
                ],

                'nilai' => [
                    'required',
                    'integer',
                    'min:0'
                ],

                'keterangan' => [
                    'nullable',
                    'string'
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | Ambil history
            |--------------------------------------------------------------------------
            */

            $history = Manzil_history::findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Ambil surat berdasarkan sura_no
            |--------------------------------------------------------------------------
            */

            $surat = DB::table('madina')
                ->where('sura_no', $validated['surat_no'])
                ->first();

            if (!$surat) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Surat tidak ditemukan.'
                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | Update History
            |--------------------------------------------------------------------------
            */

            $history->surat_id = $surat->id;
            $history->surat_no = $surat->sura_no;

            $history->dariayat = $validated['dariayat'];
            $history->sampaiayat = $validated['sampaiayat'];

            $history->nilai = $validated['nilai'];

            $history->keterangan = $validated['keterangan'] ?? null;

            $history->tgl_manzil = $validated['tgl_manzil'];

            $history->save();


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 'success',
                'message' => 'Data manzil berhasil diperbarui.',

                'redirect_url' => route(
                    'manzil-history.show',
                    [
                        'siswa_id' => $history->siswa_id
                    ]
                )
            ]);

        } catch (\Throwable $e) {

            Log::error('Gagal update manzil History', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Data gagal diperbarui. Silakan coba lagi.'
            ], 500);
        }
    }

    public function getHistory($id)
    {
        // Ambil data history berdasarkan ID
        $history = Manzil_history::find($id);
    
        if (!$history) {
            return response()->json(['status' => 'error', 'message' => 'History tidak ditemukan'], 404);
        }
    
        // Ambil data surat berdasarkan surat_id yang ditemukan
        $surat = DB::table('madina')
                    ->where('id', $history->surat_id)  // Gunakan surat_id dari history untuk mencari surat
                    ->first();
    
        // Ambil semua surat dari madina, urutkan berdasarkan sura_no
        $suratList = DB::table('madina')
                       ->select('sura_no', 'sura_name', DB::raw('MIN(id) as id'), DB::raw('COUNT(sura_no) as qty_sura'))
                       ->groupBy('sura_no', 'sura_name')
                       ->orderBy('sura_no')  // Urutkan berdasarkan sura_no
                       ->get();  // Ambil semua surat dari tabel madina
    
        // Cek jika data ada
        if ($history && $surat) {
            // Mengembalikan data dalam format JSON
            return response()->json([
                'status' => 'success',
                'data' => $history,
                'surat' => $surat,  // Data surat yang terkait dengan history
                'suratList' => $suratList  // Mengirimkan surat list yang terurut
            ]);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Data tidak ditemukan'
        ]);
    }    


    public function destroy($siswa_id, $id)
    {
        // Temukan history berdasarkan id
        $manzilHistory = Manzil_history::findOrFail($id);

        // Hapus data
        $manzilHistory->delete();

        // Kembalikan response sukses
        return response()->json(['status' => 'success']);
    }

    public function showSiswaHistory(Request $request)
        {
            // Ambil siswa yang sedang login
            $siswa = Auth::user()->siswa;

            /*
            |--------------------------------------------------------------------------
            | Filter tanggal
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
            | History manzil
            |--------------------------------------------------------------------------
            */

            $manzilHistories = Manzil_history::with([
                'surat',
                'ustadz.user',
                'subKelas',
            ])
            ->where('siswa_id', $siswa->id)
            ->whereBetween('tgl_manzil', [
                $startDate,
                $endDate
            ])
            ->orderByDesc('tgl_manzil')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();


            return view(
                'manzils.siswa_history',
                compact(
                    'siswa',
                    'manzilHistories',
                    'startDate',
                    'endDate'
                )
            );
        }

    public function laporan(Request $request)
        {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');

            /*
            |--------------------------------------------------------------------------
            | Default tanggal: awal bulan sampai hari ini
            |--------------------------------------------------------------------------
            */

            if (!$start_date && !$end_date) {
                $start_date = Carbon::now()->startOfMonth()->toDateString();
                $end_date = Carbon::now()->toDateString();
            }

            /*
            |--------------------------------------------------------------------------
            | Jika hanya salah satu tanggal yang diisi
            |--------------------------------------------------------------------------
            */

            if (!$start_date) {
                $start_date = $end_date;
            }

            if (!$end_date) {
                $end_date = $start_date;
            }

            $start_date = Carbon::parse($start_date)->startOfDay();
            $end_date = Carbon::parse($end_date)->endOfDay();

            /*
            |--------------------------------------------------------------------------
            | Ambil data manzil
            |--------------------------------------------------------------------------
            */

            $manzils = Manzil_history::with([
                'surat',
                'siswa.user',
                'ustadz.user',
                'subKelas',
            ])
            ->whereBetween('tgl_manzil', [
                $start_date->toDateString(),
                $end_date->toDateString()
            ])
            ->orderBy('tgl_manzil')
            ->orderBy('id')
            ->get();

            /*
            |--------------------------------------------------------------------------
            | Export PDF
            |--------------------------------------------------------------------------
            */

            if ($request->has('pdf')) {

                $pdf = PDF::loadView(
                    'manzils.laporan_pdf',
                    compact(
                        'manzils',
                        'start_date',
                        'end_date'
                    )
                )->setPaper('a4', 'landscape');

                return $pdf->download(
                    'laporan_manzil_' .
                    $start_date->format('Y-m-d') .
                    '_to_' .
                    $end_date->format('Y-m-d') .
                    '.pdf'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Tampilan laporan
            |--------------------------------------------------------------------------
            */

            return view('manzils.laporan', compact(
                'manzils',
                'start_date',
                'end_date'
            ));
        }

    
    public function exportToExcel(Request $request)
    {
        // dd($request->all());
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
    
        // Validasi tanggal
        if (!$start_date || !$end_date) {
            return redirect()->route('manzils.laporan')->with('error', 'Tanggal harus dipilih.');
        }
    
        // Export to Excel
        return Excel::download(new ManzilExport($start_date, $end_date), 'laporan_manzil_' . $start_date . '_to_' . $end_date . '.xlsx');
    }
}
