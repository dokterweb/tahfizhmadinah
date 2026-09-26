<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UstadzDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $ustadz = $user->ustadz;

        if (!$ustadz) {
            return response()->json([
                'success' => false,
                'message' => 'Data ustadz tidak ditemukan.',
            ], 404);
        }

        $subKelas = $ustadz->subKelas()
            ->select('sub_kelas.id', 'sub_kelas.nama_sub_kelas')
            ->get();

        return response()->json([
            'success' => true,

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : null,
            ],

            'ustadz' => [
                'id' => $ustadz->id,
            ],

            'sub_kelas' => $subKelas,
        ]);
    }
}