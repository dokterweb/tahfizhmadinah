<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUstadzRequest;
use App\Http\Requests\UpdateUstadzRequest;
use App\Models\Kelasnya;
use App\Models\SubKelas;
use App\Models\User;
use App\Models\Ustadz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Exports\UstadzExport;
use Maatwebsite\Excel\Facades\Excel;

class UstadzController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ustadzs = Ustadz::with([
            'user',
            'subKelas'
        ])
        ->orderBy('id', 'desc')
        ->get();

        return view('ustadzs.index', compact('ustadzs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $SubKelas = SubKelas::all();
        $subKelas = SubKelas::orderBy('nama_sub_kelas')->get();
        return view('ustadzs.create',compact('subKelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(StoreUstadzRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {

            // Simpan avatar
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // Buat user
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'avatar'    => $avatarPath ?? null,
                'password'  => Hash::make($validated['password']),
            ]);

            // Assign role ustadz
            $user->assignRole('ustadz');

            // Buat data ustadz
            $ustadz = Ustadz::create([
                'user_id'      => $user->id,
                'kelamin'      => $validated['kelamin'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tgl_lahir'    => $validated['tgl_lahir'],
                'no_hp'        => $validated['no_hp'],
            ]);

            // Simpan sub kelas yang diajar
            $ustadz->subKelas()->sync($validated['sub_kelas_ids']);
        });

        return redirect()
            ->route('ustadzs.index')
            ->with('success', 'Data Ustadz berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ustadz $ustadz)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(Ustadz $ustadz)
    {
        $subKelas = SubKelas::orderBy('nama_sub_kelas')->get();

        // Ambil ID sub kelas yang saat ini diajar oleh ustadz
        $selectedSubKelas = $ustadz->subKelas->pluck('id')->toArray();

        return view('ustadzs.edit', compact('ustadz','subKelas','selectedSubKelas'));
    }
    /**
     * Update the specified resource in storage.
     */
   public function update(UpdateUstadzRequest $request, Ustadz $ustadz)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $ustadz) {

            // Data user
            $userData = [
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ];

            // Update password jika diisi
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($validated['password']);
            }

            // Update avatar jika ada
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')
                    ->store('avatars', 'public');

                $userData['avatar'] = $avatarPath;
            }

            // Update user
            $ustadz->user->update($userData);

            // Update data ustadz
            $ustadz->update([
                'kelamin'      => $validated['kelamin'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tgl_lahir'   => $validated['tgl_lahir'],
                'no_hp'       => $validated['no_hp'],
            ]);

            // Update relasi sub kelas
            $ustadz->subKelas()->sync($validated['sub_kelas_ids']);
        });

        return redirect()
            ->route('ustadzs.index')
            ->with('success', 'Data ustadz berhasil diperbarui!');
    }

    public function destroy(Ustadz $ustadz)
    {
        $ustadz->delete();

        return redirect()
            ->route('ustadzs.index')
            ->with('success', 'Data ustadz berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new UstadzExport,'data_ustadz.xlsx');
    }
}
