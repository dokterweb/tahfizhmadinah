<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Siswa;
use App\Models\Bulanan;
use App\Models\Periode;
use Illuminate\Http\Request;
use App\Models\Profilesekolah;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        // $periods    = Period::all();
        $periodes   = Periode::where('periode_status', '1')->get();
        $siswas     = Siswa::all();
        return  view('payouts.index',compact('periodes','siswas'));
    }

    public function filter_bulanans(Request $request)
    {
        $siswaId = $request->input('siswa_id');
        $periodId = $request->input('periode_id');
        
        $bulanans = DB::table('bulanans as b')
            ->select(
                'b.*',
                'pr.periode_start',
                'pr.periode_end',
                'pos.pos_name'
            )
            ->leftJoin('siswas as sis', 'sis.id', '=', 'b.siswa_id')
            ->leftJoin('users as u', 'u.id', '=', 'sis.user_id')
            ->leftJoin('payments as p', 'p.id', '=', 'b.payment_id')
            ->leftJoin('periodes as pr', 'pr.id', '=', 'p.periode_id')
            ->leftJoin('posnyas as pos', 'pos.id', '=', 'p.posnya_id')
            ->where('sis.id', $siswaId)
            ->where('pr.id', $periodId)
            ->get();
            // dd($bulanans);
        $totBill = $bulanans->sum('bulan_bill');
        $telahDibayar = $bulanans->sum('bulan_number_pay');

        $siswanya = Siswa::with(['kelasnya', 'user'])
        ->where('id', $siswaId)
        ->first();

       
        $periodes = Periode::where('periode_status', '1')->get();
        $siswas     = Siswa::all();
        return view('payouts.index', compact('bulanans', 'totBill', 'telahDibayar','periodes','siswas','siswanya','siswaId'));

    }

    public function bayar_bulan($paymentId, $siswaId)
    {
        $bulanans = Bulanan::with(['bulan', 'payment.periode', 'payment.posnya'])
            ->where('siswa_id', $siswaId)
            ->where('payment_id', $paymentId)
            ->get();
        // dd($bulanans);
        $totBill = $bulanans->sum('bulan_bill');
        $telahDibayar = $bulanans->sum('bulan_number_pay');

        // Ambil data siswa beserta informasi kelas
        $siswa = Siswa::with('Kelasnya')->findOrFail($siswaId);

        return view('payouts.bayar_bulan', compact('bulanans','totBill','telahDibayar','siswa'));
    }

    public function updateBulanans(Request $request)
    {
        // Validasi input
        $request->validate([
            'id' => 'required|integer', // ID dari tabel bulanans
            'bulan_number_pay' => 'required|numeric|min:0',
            'bulan_date_pay' => 'required|date',
            'bukti_bulan' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validasi file gambar
        ]);
    
        // Ambil data dari request
        $bulanansId = $request->input('id');
        $bulanNumberPay = $request->input('bulan_number_pay');
        $bulanDatePay = $request->input('bulan_date_pay');
    
        // Cari data di tabel bulanans berdasarkan ID
        $bulanans = DB::table('bulanans')->where('id', $bulanansId)->first();
    
        if (!$bulanans) {
            return redirect()->back()->with('error', 'Data bulanans tidak ditemukan.');
        }
    
        // Handle upload file jika ada
        $buktiBulanPath = null;
        if ($request->hasFile('bukti_bulan')) {
            $buktiBulanPath = $request->file('bukti_bulan')->store('bukti_bulan', 'public'); // Simpan di folder 'storage/app/public/bukti_bulan'
        }
        
        // Update data di tabel bulanans
        DB::table('bulanans')
            ->where('id', $bulanansId)
            ->update([
                'bulan_status' => '1',
                'bulan_number_pay' => $bulanNumberPay,
                'bulan_date_pay' => $bulanDatePay,
                'bukti_bulan' => $buktiBulanPath, // Simpan path file jika ada
                'updated_at' => now(),
            ]);
    
        // Insert ke tabel bulanan_trxes
        DB::table('bulan_trks')->insert([
            'bulan_id' => $bulanans->bulan_id,
            'siswa_id' => $bulanans->siswa_id,
            'payment_id' => $bulanans->payment_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->back()->with('success', 'Data berhasil diperbarui dan transaksi dicatat.');
    }

    public function invoice($paymentId, $bulanId)
    {
        // Ambil data bulanan berdasarkan payment_id dan bulan_id
        $bulanan = Bulanan::with(['siswa.kelasnya', 'bulan', 'payment.periode', 'payment.posnya'])
            ->where('payment_id', $paymentId)
            ->where('bulan_id', $bulanId)
            ->firstOrFail();
    
        // ambil data siswa
        $siswa = $bulanan->siswa;
    
        // ambil setting sekolah
        $profile = Profilesekolah::first();

        return view('payouts.cetakperbayar', compact('siswa', 'bulanan', 'profile'));
    /*     // generate pdf
        $pdf = PDF::loadView('payouts.cetakperbayar', compact('siswa', 'bulanan', 'profile'))
                  ->setPaper('A4', 'portrait');
    
        // preview PDF di tab baru
        return $pdf->stream('cetakperbayar_'.$siswa->nama.'_'.$bulanan->bulan->nama_bulan.'.pdf'); */
    }
    
}
