<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Sabqi;
use App\Models\Siswa;
use App\Exports\SabqiExport;
use Illuminate\Http\Request;
use App\Models\Sabqi_history;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class SabqiController extends Controller
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

            $sabqis = Siswa::with([
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

            $sabqis = Siswa::with([
                'user',
                'kelasnya',
                'subKelas',
                'ustadz.user',
            ])
            ->orderBy('user_id')
            ->get();
        }

        return view(
            'sabqis.index',
            compact('sabqis')
        );
    }
    
    public function showSabqiHistory(Request $request, $siswa_id)
    {
        $siswa = Siswa::with(['user','kelasnya',])->findOrFail($siswa_id);

        $startDate = $request->input('start_date',Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date',Carbon::now()->toDateString());

        $sabqiHistories = Sabqi_history::with(['surat','ustadz.user','subKelas',])
        ->where('siswa_id', $siswa_id)->whereBetween('tgl_sabqi', [$startDate,$endDate])
        ->orderByDesc('tgl_sabqi')
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

        return view('sabqis.history', compact('siswa','siswa_id','sabqiHistories','surat','startDate','endDate'));
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

            'tgl_sabqi'     => 'required|date',

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
        | Simpan Sabqi History
        |--------------------------------------------------------------------------
        */

        $sabqiHistory = new Sabqi_history();

        $sabqiHistory->siswa_id = $validated['siswa_id'];
        $sabqiHistory->ustadz_id = $validated['ustadz_id'];
        $sabqiHistory->sub_kelas_id = $validated['sub_kelas_id'];

        // Tahun ajaran boleh NULL untuk sementara
        $sabqiHistory->tahun_ajaran_id = $validated['tahun_ajaran_id'] ?? null;

        $sabqiHistory->surat_id = $surat->id;
        $sabqiHistory->surat_no = $surat->sura_no;

        $sabqiHistory->dariayat = $validated['dariayat'];
        $sabqiHistory->sampaiayat = $validated['sampaiayat'];

        $sabqiHistory->tgl_sabqi = $validated['tgl_sabqi'];

        $sabqiHistory->nilai = $validated['nilai'];
        $sabqiHistory->keterangan = $validated['keterangan'] ?? null;

        $sabqiHistory->save();

        /*
        |--------------------------------------------------------------------------
        | Kembali ke halaman history siswa
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('sabqi-history.show', [
                'siswa_id' => $validated['siswa_id']
            ])
            ->with('success', 'Data Sabqi berhasil disimpan.');
    }

    public function destroy($siswa_id, $id)
    {
        $sabqiHistory = Sabqi_history::where('id', $id)
            ->where('siswa_id', $siswa_id)
            ->firstOrFail();

        $sabqiHistory->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Sabqi berhasil dihapus.'
        ]);
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
        | History sabqi
        |--------------------------------------------------------------------------
        */

        $sabqiHistories = Sabqi_history::with([
            'surat',
            'ustadz.user',
            'subKelas',
        ])
        ->where('siswa_id', $siswa->id)
        ->whereBetween('tgl_sabqi', [
            $startDate,
            $endDate
        ])
        ->orderByDesc('tgl_sabqi')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();


        return view(
            'sabqis.siswa_history',
            compact(
                'siswa',
                'sabqiHistories',
                'startDate',
                'endDate'
            )
        );
    }

     public function getHistory($id)
    {
        // Ambil data history berdasarkan ID
        $history = Sabqi_history::find($id);
    
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
        $sabqiHistory = Sabqi_history::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Pastikan history memang milik siswa yang sedang dibuka
        |--------------------------------------------------------------------------
        */

        if ((int) $sabqiHistory->siswa_id !== (int) $siswa_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Sabqi tidak sesuai dengan siswa.'
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
            ->where('id', $sabqiHistory->surat_id)
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
                'id'          => $sabqiHistory->id,
                'siswa_id'    => $sabqiHistory->siswa_id,
                'ustadz_id'   => $sabqiHistory->ustadz_id,
                'sub_kelas_id'=> $sabqiHistory->sub_kelas_id,
                'tgl_sabqi'   => $sabqiHistory->tgl_sabqi,
                'surat_id'    => $sabqiHistory->surat_id,
                'surat_no'    => $sabqiHistory->surat_no,
                'dariayat'    => $sabqiHistory->dariayat,
                'sampaiayat'  => $sabqiHistory->sampaiayat,
                'nilai'       => $sabqiHistory->nilai,
                'keterangan'  => $sabqiHistory->keterangan,
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
        Log::info('Data update Sabqi History:', $request->all());

        try {

            /*
            |--------------------------------------------------------------------------
            | Validasi
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'tgl_sabqi' => [
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

            $history = Sabqi_history::findOrFail($id);


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

            $history->tgl_sabqi = $validated['tgl_sabqi'];

            $history->save();


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 'success',
                'message' => 'Data Sabqi berhasil diperbarui.',

                'redirect_url' => route(
                    'sabqi-history.show',
                    [
                        'siswa_id' => $history->siswa_id
                    ]
                )
            ]);

        } catch (\Throwable $e) {

            Log::error('Gagal update Sabqi History', [
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
        | Ambil data Sabqi
        |--------------------------------------------------------------------------
        */

        $sabqis = Sabqi_history::with([
            'surat',
            'siswa.user',
            'ustadz.user',
            'subKelas',
        ])
        ->whereBetween('tgl_sabqi', [
            $start_date->toDateString(),
            $end_date->toDateString()
        ])
        ->orderBy('tgl_sabqi')
        ->orderBy('id')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Export PDF
        |--------------------------------------------------------------------------
        */

        if ($request->has('pdf')) {

            $pdf = PDF::loadView(
                'sabqis.laporan_pdf',
                compact(
                    'sabqis',
                    'start_date',
                    'end_date'
                )
            )->setPaper('a4', 'landscape');

            return $pdf->download(
                'laporan_sabqi_' .
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

        return view('sabqis.laporan', compact(
            'sabqis',
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
            return redirect()->route('sabqis.laporan')->with('error', 'Tanggal harus dipilih.');
        }
    
        // Export to Excel
        return Excel::download(new SabqiExport($start_date, $end_date), 'laporan_sabqi_' . $start_date . '_to_' . $end_date . '.xlsx');
    }
}
