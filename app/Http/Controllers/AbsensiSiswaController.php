<?php

namespace App\Http\Controllers;
use App\Models\Absensi_siswa;
use App\Models\Kelasnya;
use App\Models\Siswa;
use App\Models\SubKelas;
use App\Models\Ustadz;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
     // Fungsi untuk melihat absensi semua siswa (admin)
   public function index(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

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
        | Ambil semua absensi siswa
        |--------------------------------------------------------------------------
        */

        $absensi = Absensi_siswa::with([
            'siswa.user',
            'siswa.kelasnya',
            'siswa.ustadz.user',
            'siswa.subKelas',
        ])
        ->whereBetween('tgl_absen', [
            $startDate,
            $endDate
        ])
        ->orderByDesc('tgl_absen')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();


        return view('absensi.index', compact(
            'absensi',
            'startDate',
            'endDate'
        ));
    }
 
     public function create()
    {
         $SubKelas = SubKelas::with('kelasnya')
        ->orderBy('kelas_id')
        ->orderBy('nama_sub_kelas')
        ->get();
        
        return view('absensi.create', compact('SubKelas'));
    }

    public function getSiswaBySubKelas($sub_kelas_id)
    {
        // Ambil semua siswa berdasarkan sub_kelas_id dan termasuk user (untuk mendapatkan nama)
        $siswa = Siswa::with('user')  // Mengambil relasi user untuk nama siswa
                      ->where('sub_kelas_id', $sub_kelas_id)
                      ->get();
    
        // Kirimkan data siswa dalam bentuk JSON
        return response()->json(['siswa' => $siswa]);
    }
    

   public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_kelas_id' => ['required', 'integer', 'exists:sub_kelas,id'],
            'tgl_absen' => ['required', 'date'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:hadir,absen,izin'],
        ]);

        try {

            // Cek apakah absensi sudah dibuat pada tanggal tersebut
            $existingAbsensi = Absensi_siswa::whereHas('siswa', function ($query) use ($request) {
                $query->where('sub_kelas_id', $request->sub_kelas_id);
            })
            ->whereDate('tgl_absen', $request->tgl_absen)
            ->exists();

            if ($existingAbsensi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Absensi untuk kelas tersebut pada tanggal ini sudah dibuat.'
                ], 422);
            }

            foreach ($request->status as $siswa_id => $status) {

                Absensi_siswa::create([
                    'siswa_id' => $siswa_id,
                    'tgl_absen' => $request->tgl_absen,
                    'status' => $status,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil disimpan.'
            ]);

        } catch (\Throwable $e) {

            \Log::error('Gagal menyimpan absensi', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Absensi gagal disimpan. Silakan coba lagi.'
            ], 500);
        }
    }
    

   public function checkAbsensi(Request $request)
    {
        $request->validate([
            'sub_kelas_id' => ['required', 'integer', 'exists:sub_kelas,id'],
            'tgl_absen' => ['required', 'date'],
        ]);

        $existingAbsensi = Absensi_siswa::whereHas('siswa', function ($query) use ($request) {
            $query->where('sub_kelas_id', $request->sub_kelas_id);
        })
        ->whereDate('tgl_absen', $request->tgl_absen)
        ->exists();

        return response()->json([
            'exists' => $existingAbsensi
        ]);
    }

    public function destroy($id)
    {
        // Cari data absensi berdasarkan ID
        $absensi = Absensi_siswa::findOrFail($id);
    
        // Hapus absensi
        $absensi->delete();
    
        // Kembali ke halaman absensi dengan pesan sukses
        return redirect()->route('absensis')->with('success', 'Absensi berhasil dihapus');
    }
    
    public function ustadzIndex(Request $request)
    {
        // Ambil data ustadz yang sedang login
        $ustadz = Ustadz::where('user_id', auth()->id())->first();

        if (!$ustadz) {
            return redirect()
                ->back()
                ->with('error', 'Ustadz tidak ditemukan.');
        }

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
        | Ambil absensi siswa
        |--------------------------------------------------------------------------
        */

        $absensi = Absensi_siswa::whereHas('siswa', function ($query) use ($ustadz) {
            $query->where('ustadz_id', $ustadz->id);
        })
        ->with([
            'siswa.user',
            'siswa.kelasnya',
        ])
        ->whereBetween('tgl_absen', [
            $startDate,
            $endDate
        ])
        ->orderByDesc('tgl_absen')
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();


        return view('absensi.ustadz_index', compact(
            'absensi',
            'startDate',
            'endDate'
        ));
    }

    public function ustadzCreate()
    {
        // Ambil data ustadz yang sedang login
        $ustadz = Ustadz::where('user_id', auth()->id())->first();

        if (!$ustadz) {
            return redirect()->back()->with('error', 'Ustadz tidak ditemukan.');
        }

        // Ambil siswa yang diampu oleh ustadz
        $siswas = Siswa::where('ustadz_id', $ustadz->id)->get();

        return view('absensi.ustadzcreate', compact('siswas'));
    }

    public function ustadzStore(Request $request)
    {
        // Validasi input
        $request->validate([
            'tgl_absen' => 'required|date',
            'status' => 'required|array',  // Pastikan setiap siswa memiliki status
        ]);
    
        // Periksa apakah absensi sudah ada untuk siswa pada tanggal yang sama
        foreach ($request->status as $siswa_id => $status) {
            $existingAbsensi = Absensi_siswa::where('siswa_id', $siswa_id)
                                            ->where('tgl_absen', $request->tgl_absen)
                                            ->exists();
    
            if ($existingAbsensi) {
                return back()->with('error', 'Absensi untuk siswa dengan tanggal yang sama sudah ada.');
            }
        }
    
        // Simpan absensi untuk setiap siswa yang dipilih
        foreach ($request->status as $siswa_id => $status) {
            Absensi_siswa::create([
                'siswa_id' => $siswa_id,
                'tgl_absen' => $request->tgl_absen,
                'status' => $status,  // Status absensi per siswa
            ]);
        }
    
        // Redirect ke halaman absensi dengan pesan sukses
        return redirect()->route('absensis.ustadzIndex')->with('success', 'Absensi berhasil disimpan.');
    }
    

}
