<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UstadzProfileController extends Controller
{
    /**
     * Menampilkan profile ustadz yang sedang login.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $user->load('ustadz.subKelas');

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,

                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : null,

                'ustadz' => $user->ustadz
                    ? [
                        'id' => $user->ustadz->id,
                        'nama_ustadz' => $user->ustadz->nama_ustadz,
                        'kelamin' => $user->ustadz->kelamin,
                        'tempat_lahir' => $user->ustadz->tempat_lahir,
                        'tgl_lahir' => $user->ustadz->tgl_lahir,
                        'no_hp' => $user->ustadz->no_hp,

                        'sub_kelas' => $user->ustadz->subKelas
                            ->map(function ($subKelas) {
                                return [
                                    'id' => $subKelas->id,
                                    'nama_sub_kelas' =>
                                        $subKelas->nama_sub_kelas,
                                ];
                            })
                            ->values(),
                    ]
                    : null,
            ],
        ]);
    }

    /**
     * Update nama dan email.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : null,
            ],
        ]);
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    /**
     * Upload / update avatar.
     */
    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if (
            $user->avatar &&
            Storage::disk('public')->exists($user->avatar)
        ) {
            Storage::disk('public')->delete(
                $user->avatar
            );
        }

        $path = $request->file('avatar')
            ->store('avatars', 'public');

        $user->update([
            'avatar' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar berhasil diperbarui.',
            'data' => [
                'avatar' => asset('storage/' . $path),
            ],
        ]);
    }
}