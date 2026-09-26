<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madina;
use Illuminate\Http\Request;

class MadinaController extends Controller
{
    /**
     * Daftar surat Al-Qur'an
     */
   public function index(Request $request)
    {
        $surat = Madina::query()
            ->selectRaw('MIN(id) as id, sura_no, sura_name')
            ->groupBy('sura_no', 'sura_name')
            ->orderBy('sura_no')
            ->get();

        return response()->json([
            'success' => true,
            'surat' => $surat,
        ]);
    }
}