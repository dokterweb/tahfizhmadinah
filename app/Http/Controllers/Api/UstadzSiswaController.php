<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubKelas;
use Illuminate\Http\Request;

class UstadzSiswaController extends Controller
{
    public function index(Request $request, SubKelas $subKelas)
    {
        $user = $request->user();

        // Ambil data ustadz dari user yang sedang login
        $ustadz = $user->ustadz;

        if (!$ustadz) {
            return response()->json([
                'success' => false,
                'message' => 'Data ustadz tidak ditemukan.',
            ], 404);
        }

        // Cek apakah ustadz memang mengajar sub kelas ini
        $hasAccess = $ustadz->subKelas()
            ->where('sub_kelas.id', $subKelas->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke sub kelas ini.',
            ], 403);
        }

        // Ambil semua siswa pada sub kelas
        $siswas = $subKelas->siswas()
            ->with('user:id,name,email')
            ->get();

        return response()->json([
            'success' => true,

            'sub_kelas' => [
                'id' => $subKelas->id,
                'nama_sub_kelas' => $subKelas->nama_sub_kelas,
            ],

            'siswas' => $siswas->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'user_id' => $siswa->user_id,
                    'nama_siswa' => $siswa->user?->name ?? '',
                    'email' => $siswa->user?->email ?? '',
                    'kelamin' => $siswa->kelamin,
                    'tempat_lahir' => $siswa->tempat_lahir,
                    'tgl_lahir' => $siswa->tgl_lahir,
                    'alamat' => $siswa->alamat,
                    'no_hp' => $siswa->no_hp,
                ];
            }),
        ]);
    }
}