<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Bulanan;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Carbon\Carbon;

class PayoutsiswaController extends Controller
{
    public function index()
    {
        // $periods    = Period::all();
        $periodes   = Periode::where('periode_status', '1')->get();
        return  view('payoutsiswas.index',compact('periodes'));
    }

    public function filter_bulanans(Request $request)
    {
        $userId = Auth::id();

        // Cari siswa_id berdasarkan user_id
        $siswaId = Siswa::where('user_id', $userId)->value('id');
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
        return view('payoutsiswas.index', compact('bulanans', 'totBill', 'telahDibayar','periodes','siswanya'));

    }

    public function tagihan_bulan($paymentId, $siswaId)
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

        return view('payoutsiswas.tagihan_bulan', compact('bulanans','totBill','telahDibayar','siswa'));
    }

    public function bayarbulanan(Request $request)
    {
        \Log::info('BayarBulanan called', $request->all());
        
        try {
            $bulanan = Bulanan::findOrFail($request->id);
        } catch (\Exception $e) {
            \Log::error('Error finding Bulanan: ' . $e->getMessage());
            return back()->with('error', 'Data not found');
        }
    
        // set midtrans config
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    
        // buat transaction details
        $params = [
            'transaction_details' => [
                'order_id' => 'BULANAN-' . $bulanan->id . '-' . time(),
                'gross_amount' => (int) $bulanan->bulan_bill,
            ],
            'customer_details' => [
                'first_name' => $bulanan->siswa->user->name ?? 'Siswa',
                'email' => $bulanan->siswa->user->email ?? 'noemail@test.com',
                'phone' => $bulanan->siswa->no_hp ?? '080000000',
            ],
        ];
    
        // NON-AKTIFKAN SSL VERIFICATION SEMENTARA
        Config::$curlOptions = [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ];
    
        // generate snap token
        try {
            $snapToken = Snap::getSnapToken($params);
            \Log::info('Snap token generated successfully');
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            \Log::error('Midtrans Error Trace: ' . $e->getTraceAsString());
            
            // Tampilkan error lebih detail
            return back()->with('error', 'Payment gateway error: ' . $e->getMessage());
        }
    
        return view('payoutsiswas.pay', compact('bulanan', 'snapToken'));
    }

/* 
    public function bayarbulanan(Request $request)
    {
        // ambil data bulanan
        $bulanan = Bulanan::findOrFail($request->id);

        // set midtrans config
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        // buat transaction details
        $params = [
            'transaction_details' => [
                'order_id' => 'BULANAN-' . $bulanan->id . '-' . time(),
                'gross_amount' => (int) $bulanan->bulan_bill,
            ],
            'customer_details' => [
                'first_name' => $bulanan->siswa->user->name ?? 'Siswa',
                'email' => $bulanan->siswa->user->email ?? 'noemail@test.com',
                'phone' => $bulanan->siswa->no_hp ?? '080000000',
            ],
        ];

        // generate snap token
        $snapToken = Snap::getSnapToken($params);

        return view('payoutsiswas.pay', compact('bulanan', 'snapToken'));
    } */

     // dipanggil dari fetch() setelah pembayaran sukses
     public function updateStatus(Request $request)
     {
         $request->validate([
             'id'     => 'required|exists:bulanans,id',
             'status' => 'required|string',
         ]);
 
         $bulanan = Bulanan::findOrFail($request->id);
 
         if ($request->status === 'paid') {
             $bulanan->bulan_status     = '1'; // sudah bayar
             $bulanan->bulan_number_pay = $bulanan->bulan_bill;
             $bulanan->bulan_date_pay   = Carbon::now();
             $bulanan->save();
         }
 
         return response()->json(['success' => true]);
     }
}
