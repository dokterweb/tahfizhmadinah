<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SiswaDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $siswa = $user->siswa()
            ->with([
                'subKelas.kelasnya',
            ])
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

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
            'siswa' => [
                'id' => $siswa->id,
                'nama_siswa' => $user->name,
                'kelamin' => $siswa->kelamin,
                'tempat_lahir' => $siswa->tempat_lahir,
                'tgl_lahir' => $siswa->tgl_lahir,
                'alamat' => $siswa->alamat,
                'no_hp' => $siswa->no_hp,
                'sub_kelas' => $siswa->subKelas ? [
                    'id' => $siswa->subKelas->id,
                    'nama_sub_kelas' => $siswa->subKelas->nama_sub_kelas,
                    'kelas' => $siswa->subKelas->kelasnya?->nama_kelas,
                ] : null,
            ],
        ]);
    }
}