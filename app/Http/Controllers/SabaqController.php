<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sabaq;
use App\Models\Siswa;
use App\Models\Madina;
use Illuminate\Http\Request;
use App\Models\Sabaq_history;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Exports\SabaqExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class SabaqController extends Controller
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

            $sabaqs = Siswa::with([
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

            $sabaqs = Siswa::with([
                'user',
                'kelasnya',
                'subKelas',
                'ustadz.user',
            ])
            ->orderBy('user_id')
            ->get();
        }

        return view(
            'sabaqs.index',
            compact('sabaqs')
        );
    }

    public function showSabaqHistory(Request $request, $siswa_id)
    {
        $siswa = Siswa::with(['user','kelasnya',])->findOrFail($siswa_id);

        $startDate = $request->input('start_date',Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date',Carbon::now()->toDateString());

        $sabaqHistories = Sabaq_history::with(['surat','ustadz.user','subKelas',])
        ->where('siswa_id', $siswa_id)->whereBetween('tgl_sabaq', [$startDate,$endDate])
        ->orderByDesc('tgl_sabaq')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();

        $surat = DB::table('madina')
            ->select('sura_no','sura_name',
                DB::raw('MIN(id) as id'),
                DB::raw('COUNT(sura_no) as qty_sura')
            )
            ->groupBy('sura_no','sura_name')
            ->orderBy('sura_no')
            ->get();

        return view('sabaqs.history', compact('siswa','siswa_id','sabaqHistories','surat','startDate','endDate'));
    }

    public function getSuratDetails($sura_no)
    {
        $surat = DB::table('madina')
            ->select(
                'sura_no',
                'jozz',
                'sura_name'
            )
            ->selectRaw('COUNT(*) AS qty_sura')
            ->selectRaw('MIN(page) AS start_page')
            ->selectRaw('MAX(page) AS end_page')
            ->where('sura_no', $sura_no)
            ->groupBy(
                'sura_no',
                'jozz',
                'sura_name'
            )
            ->first();

        if ($surat) {

            return response()->json([
                'status' => 'success',
                'data' => [
                    'no_surat' => $surat->sura_no,
                    'jozz' => $surat->jozz,
                    'sura_name' => $surat->sura_name,
                    'qty_sura' => $surat->qty_sura,
                    'start_page' => $surat->start_page,
                    'end_page' => $surat->end_page,
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Surat tidak ditemukan.'
        ], 404);
    }

  

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'      => 'required|integer|exists:siswas,id',
            'ustadz_id'     => 'required|integer|exists:ustadzs,id',
            'sub_kelas_id'  => 'required|integer|exists:sub_kelas,id',

            'tgl_sabaq'     => 'required|date',

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
        | Simpan Sabaq History
        |--------------------------------------------------------------------------
        */

        $sabaqHistory = new Sabaq_history();

        $sabaqHistory->siswa_id = $validated['siswa_id'];
        $sabaqHistory->ustadz_id = $validated['ustadz_id'];
        $sabaqHistory->sub_kelas_id = $validated['sub_kelas_id'];

        $sabaqHistory->surat_id = $surat->id;
        $sabaqHistory->surat_no = $surat->sura_no;

        $sabaqHistory->dariayat = $validated['dariayat'];
        $sabaqHistory->sampaiayat = $validated['sampaiayat'];

        $sabaqHistory->tgl_sabaq = $validated['tgl_sabaq'];

        $sabaqHistory->nilai = $validated['nilai'];
        $sabaqHistory->keterangan = $validated['keterangan'] ?? null;

        $sabaqHistory->save();

        /*
        |--------------------------------------------------------------------------
        | Kembali ke halaman history siswa
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('sabaq-history.show', [
                'siswa_id' => $validated['siswa_id']
            ])
            ->with('success', 'Data Sabaq berhasil disimpan.');
    }

    public function getHistory($id)
    {
        // Ambil data history berdasarkan ID
        $history = Sabaq_history::find($id);
    
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
    
    public function edit($siswa_id, $id)
    {
        $sabaqHistory = Sabaq_history::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Pastikan history memang milik siswa yang sedang dibuka
        |--------------------------------------------------------------------------
        */

        if ((int) $sabaqHistory->siswa_id !== (int) $siswa_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Sabaq tidak sesuai dengan siswa.'
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
            ->where('id', $sabaqHistory->surat_id)
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
                'id'          => $sabaqHistory->id,
                'siswa_id'    => $sabaqHistory->siswa_id,
                'ustadz_id'   => $sabaqHistory->ustadz_id,
                'sub_kelas_id'=> $sabaqHistory->sub_kelas_id,
                'tgl_sabaq'   => $sabaqHistory->tgl_sabaq,
                'surat_id'    => $sabaqHistory->surat_id,
                'surat_no'    => $sabaqHistory->surat_no,
                'dariayat'    => $sabaqHistory->dariayat,
                'sampaiayat'  => $sabaqHistory->sampaiayat,
                'nilai'       => $sabaqHistory->nilai,
                'keterangan'  => $sabaqHistory->keterangan,
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
        Log::info('Data update Sabaq History:', $request->all());

        try {

            /*
            |--------------------------------------------------------------------------
            | Validasi
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'tgl_sabaq' => [
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

            $history = Sabaq_history::findOrFail($id);


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

            $history->tgl_sabaq = $validated['tgl_sabaq'];

            $history->save();


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 'success',
                'message' => 'Data Sabaq berhasil diperbarui.',

                'redirect_url' => route(
                    'sabaq-history.show',
                    [
                        'siswa_id' => $history->siswa_id
                    ]
                )
            ]);

        } catch (\Throwable $e) {

            Log::error('Gagal update Sabaq History', [
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

    public function destroy($siswa_id, $id)
    {
        // Temukan history berdasarkan id
        $sabaqHistory = Sabaq_history::findOrFail($id);

        // Hapus data
        $sabaqHistory->delete();

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
        | History Sabaq
        |--------------------------------------------------------------------------
        */

        $sabaqHistories = Sabaq_history::with([
            'surat',
            'ustadz.user',
            'subKelas',
        ])
        ->where('siswa_id', $siswa->id)
        ->whereBetween('tgl_sabaq', [
            $startDate,
            $endDate
        ])
        ->orderByDesc('tgl_sabaq')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();


        return view(
            'sabaqs.siswa_history',
            compact(
                'siswa',
                'sabaqHistories',
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
        | Ambil data Sabaq
        |--------------------------------------------------------------------------
        */

        $sabaqs = Sabaq_history::with([
            'surat',
            'siswa.user',
            'ustadz.user',
            'subKelas',
        ])
        ->whereBetween('tgl_sabaq', [
            $start_date->toDateString(),
            $end_date->toDateString()
        ])
        ->orderBy('tgl_sabaq')
        ->orderBy('id')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Export PDF
        |--------------------------------------------------------------------------
        */

        if ($request->has('pdf')) {

            $pdf = PDF::loadView(
                'sabaqs.laporan_pdf',
                compact(
                    'sabaqs',
                    'start_date',
                    'end_date'
                )
            )->setPaper('a4', 'landscape');

            return $pdf->download(
                'laporan_sabaq_' .
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

        return view('sabaqs.laporan', compact(
            'sabaqs',
            'start_date',
            'end_date'
        ));
    }

    public function exportToExcel(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Validasi tanggal
        if (!$start_date || !$end_date) {
            return redirect()
                ->route('sabaqs.laporan')
                ->with('error', 'Tanggal harus dipilih.');
        }

        return Excel::download(
            new SabaqExport($start_date, $end_date),
            'laporan_sabaq_' . $start_date . '_to_' . $end_date . '.xlsx'
        );
    }
    
}
