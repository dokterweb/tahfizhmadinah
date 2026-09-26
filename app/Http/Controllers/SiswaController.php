<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Models\Siswa;
use App\Models\SubKelas;
use App\Models\User;
use App\Models\Ustadz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siswas = Siswa::all();
        return view('siswas.index',compact('siswas'));
    }

    /**
     * Show the form for creating a new resource.
     */
     public function create()
    {
        $SubKelas = SubKelas::orderBy('nama_sub_kelas')->get();

        return view('siswas.create', compact('SubKelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

        // Simpan avatar jika ada
        if($request->hasFile('avatar')){
            $avatarPath = $request->file('avatar')->store('siswa','public');
        }

        // Buat user baru
        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'avatar'    => $avatarPath ?? null, // Jika tidak ada avatar, nilainya null
            'password'  => Hash::make($validated['password']),
        ]);

        // Assign role 'ustadz' ke user
        $user->assignRole('siswa'); // Pastikan role 'ustadz' sudah ada di database

        // Simpan data ke tabel ustadzs
        $siswa = Siswa::create([
            'user_id'       => $user->id,
            'sub_kelas_id'  => $validated['sub_kelas_id'],
            'ustadz_id'     => $validated['ustadz_id'],
            'kelamin'       => $validated['kelamin'],
            'tempat_lahir'  => $validated['tempat_lahir'],
            'tgl_lahir'     => $validated['tgl_lahir'],
            'alamat'        => $validated['alamat'],
            'no_hp'         => $validated['no_hp'],
        ]);
        
        return redirect()->route('siswas.index')->with('success', 'Data Siswa berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        $SubKelas = SubKelas::all();
        $ustadz = Ustadz::all();
        return view('siswas.edit',compact('SubKelas','ustadz','siswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        // Data sudah divalidasi di UpdateustadzRequest
        $validated = $request->validated();

        // Update data User terkait
        $userData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        // Cek apakah password diisi, jika iya, update password
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($validated['password']);
        }

        // Cek apakah ada file avatar yang diunggah
        if ($request->hasFile('avatar')) {
            // Simpan avatar ke storage dan update path-nya
            $avatarPath = $request->file('avatar')->store('siswa', 'public');
            $userData['avatar'] = $avatarPath;
        }

        // Update data user yang terkait dengan ustadz
        $siswa->user->update($userData);

        // Simpan data ke tabel siswas
        $siswa->update([
            'sub_kelas_id'  => $validated['sub_kelas_id'],
            'ustadz_id'     => $validated['ustadz_id'],
            'kelamin'       => $validated['kelamin'],
            'tempat_lahir'  => $validated['tempat_lahir'],
            'tgl_lahir'     => $validated['tgl_lahir'],
            'alamat'        => $validated['alamat'],
            'no_hp'         => $validated['no_hp'],
        ]);

        return redirect()->route('siswas.index')->with('success', 'Data ustadz berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        try {
            $siswa->delete();

            return redirect()
                ->route('siswas.index')
                ->with('success', 'Data siswa berhasil dihapus.');

        } catch (\Throwable $e) {

            return redirect()
                ->route('siswas.index')
                ->with('error', 'Data siswa gagal dihapus. Silakan coba lagi.');
        }
    }

    public function getUstadz($subKelas)
    {
        $ustadz = Ustadz::with('user')
            ->whereHas('subKelas', function ($query) use ($subKelas) {
                $query->where('sub_kelas.id', $subKelas);
            })
            ->get();

        return response()->json($ustadz);
    }

    public function exportExcel()
    {
        return Excel::download(
            new SiswaExport,
            'data_siswa.xlsx'
        );
    }

public function importForm()
{
    return view('siswas.import');
}

private function validateImportRows($rows)
{
    /*
    |--------------------------------------------------------------------------
    | Email dari Excel
    |--------------------------------------------------------------------------
    */

    $emails = $rows
        ->pluck('email')
        ->filter()
        ->map(function ($email) {
            return strtolower(trim($email));
        });

    /*
    |--------------------------------------------------------------------------
    | Email duplikat dalam Excel
    |--------------------------------------------------------------------------
    */

    $emailCounts = $emails->countBy();

    $duplicateEmails = $emailCounts
        ->filter(function ($count) {
            return $count > 1;
        })
        ->keys();


    /*
    |--------------------------------------------------------------------------
    | Email yang sudah ada di users
    |--------------------------------------------------------------------------
    */

    $existingUsers = User::whereIn(
        'email',
        $emails->unique()->values()->toArray()
    )
    ->get(['id', 'email'])
    ->keyBy(function ($user) {
        return strtolower(trim($user->email));
    });


    /*
    |--------------------------------------------------------------------------
    | Ustadz
    |--------------------------------------------------------------------------
    */

    $ustadzIds = $rows
        ->pluck('id_ustadz')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $ustadzs = Ustadz::whereIn('id', $ustadzIds)
        ->pluck('id')
        ->flip();


    /*
    |--------------------------------------------------------------------------
    | Sub Kelas
    |--------------------------------------------------------------------------
    */

    $subKelasIds = $rows
        ->pluck('id_sub_kelas')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $subKelas = SubKelas::whereIn('id', $subKelasIds)
        ->pluck('id')
        ->flip();


    /*
    |--------------------------------------------------------------------------
    | Relasi Ustadz ↔ Sub Kelas
    |--------------------------------------------------------------------------
    */

    $ustadzSubKelas = DB::table('ustadz_sub_kelas')
        ->whereIn('ustadz_id', $ustadzIds)
        ->whereIn('sub_kelas_id', $subKelasIds)
        ->get()
        ->mapWithKeys(function ($item) {

            return [
                $item->ustadz_id . '-' . $item->sub_kelas_id => true
            ];

        });


    /*
    |--------------------------------------------------------------------------
    | Validasi setiap baris
    |--------------------------------------------------------------------------
    */

    $validatedRows = $rows->values()->map(
        function ($row, $index) use (
            $duplicateEmails,
            $existingUsers,
            $ustadzs,
            $subKelas,
            $ustadzSubKelas
        ) {

            $errors = [];

            $email = strtolower(
                trim($row['email'] ?? '')
            );

            $namaSiswa = trim(
                $row['nama_siswa'] ?? ''
            );

            $ustadzId = (int) (
                $row['id_ustadz'] ?? 0
            );

            $subKelasId = (int) (
                $row['id_sub_kelas'] ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | Nama
            |--------------------------------------------------------------------------
            */

            if ($namaSiswa === '') {

                $errors[] = 'Nama siswa wajib diisi.';
            }


            /*
            |--------------------------------------------------------------------------
            | Email
            |--------------------------------------------------------------------------
            */

            if ($email === '') {

                $errors[] = 'Email wajib diisi.';

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $errors[] = 'Format email tidak valid.';

            } else {

                if ($duplicateEmails->contains($email)) {

                    $errors[] =
                        'Email duplikat di dalam file Excel.';
                }

                if ($existingUsers->has($email)) {

                    $user = $existingUsers->get($email);

                    $errors[] =
                        'Email sudah terdaftar di users ' .
                        '(User ID: ' . $user->id . ').';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Ustadz
            |--------------------------------------------------------------------------
            */

            if (!$ustadzId) {

                $errors[] =
                    'ID Ustadz wajib diisi.';

            } elseif (!$ustadzs->has($ustadzId)) {

                $errors[] =
                    'ID Ustadz ' .
                    $ustadzId .
                    ' tidak ditemukan.';
            }


            /*
            |--------------------------------------------------------------------------
            | Sub Kelas
            |--------------------------------------------------------------------------
            */

            if (!$subKelasId) {

                $errors[] =
                    'ID Sub Kelas wajib diisi.';

            } elseif (!$subKelas->has($subKelasId)) {

                $errors[] =
                    'ID Sub Kelas ' .
                    $subKelasId .
                    ' tidak ditemukan.';
            }


            /*
            |--------------------------------------------------------------------------
            | Ustadz ↔ Sub Kelas
            |--------------------------------------------------------------------------
            */

            if (
                $ustadzId &&
                $subKelasId &&
                $ustadzs->has($ustadzId) &&
                $subKelas->has($subKelasId)
            ) {

                $key =
                    $ustadzId .
                    '-' .
                    $subKelasId;

                if (!$ustadzSubKelas->has($key)) {

                    $errors[] =
                        'Ustadz ID ' .
                        $ustadzId .
                        ' tidak mengajar Sub Kelas ID ' .
                        $subKelasId .
                        '.';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Kelamin
            |--------------------------------------------------------------------------
            */

            $kelamin = strtolower(
                trim($row['kelamin'] ?? '')
            );

            if (!in_array(
                $kelamin,
                ['laki-laki', 'perempuan'],
                true
            )) {

                $errors[] =
                    'Kelamin harus laki-laki atau perempuan.';
            }


            /*
            |--------------------------------------------------------------------------
            | Nomor baris Excel
            |--------------------------------------------------------------------------
            */

            $row['excel_row'] =
                $index + 2;

            $row['errors'] =
                $errors;

            $row['is_valid'] =
                empty($errors);

            return $row;
        }
    );

    return $validatedRows;
}


public function importPreview(Request $request)
{
    $request->validate([
        'file' => [
            'required',
            'file',
            'mimes:xlsx,xls',
            'max:5120',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Simpan file sementara
    |--------------------------------------------------------------------------
    */

    $token = Str::uuid()->toString();

    $filename =
        'siswa_' .
        $token .
        '.' .
        $request->file('file')->getClientOriginalExtension();

    $path = $request->file('file')->storeAs(
        'imports/siswa',
        $filename,
        'local'
    );


    /*
    |--------------------------------------------------------------------------
    | Simpan informasi file ke session
    |--------------------------------------------------------------------------
    */

    session([
        'siswa_import' => [
            'token' => $token,
            'path' => $path,
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | Baca Excel
    |--------------------------------------------------------------------------
    */

    $import = new SiswaImport();

    Excel::import(
        $import,
        $path,
        'local'
    );

    $rows = $import->rows;


    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    $validatedRows =
        $this->validateImportRows($rows);


    $totalRows =
        $validatedRows->count();

    $validRows =
        $validatedRows
            ->where('is_valid', true)
            ->count();

    $invalidRows =
        $validatedRows
            ->where('is_valid', false)
            ->count();

    $allValid =
        $invalidRows === 0;


    return view('siswas.import', compact(
        'rows',
        'validatedRows',
        'totalRows',
        'validRows',
        'invalidRows',
        'allValid'
    ));
}


public function importStore(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Ambil informasi import dari session
    |--------------------------------------------------------------------------
    */

    $importSession = session('siswa_import');

    if (!$importSession) {

        return redirect()
            ->route('siswas.import.form')
            ->with(
                'error',
                'Session import tidak ditemukan. Silakan upload Excel kembali.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Pastikan token sama
    |--------------------------------------------------------------------------
    */

    if (
        !$request->filled('token') ||
        $request->token !== $importSession['token']
    ) {

        return redirect()
            ->route('siswas.import.form')
            ->with(
                'error',
                'Token import tidak valid.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Pastikan file masih ada
    |--------------------------------------------------------------------------
    */

    $path = $importSession['path'];

    if (!Storage::disk('local')->exists($path)) {

        session()->forget('siswa_import');

        return redirect()
            ->route('siswas.import.form')
            ->with(
                'error',
                'File import sudah tidak tersedia. Silakan upload kembali.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Baca ulang Excel
    |--------------------------------------------------------------------------
    */

    $import = new SiswaImport();

    Excel::import(
        $import,
        $path,
        'local'
    );

    $rows = $import->rows;


    /*
    |--------------------------------------------------------------------------
    | VALIDASI ULANG
    |--------------------------------------------------------------------------
    */

    $validatedRows =
        $this->validateImportRows($rows);


    $invalidRows =
        $validatedRows
            ->where('is_valid', false);


    /*
    |--------------------------------------------------------------------------
    | Jangan import jika ada error
    |--------------------------------------------------------------------------
    */

    if ($invalidRows->isNotEmpty()) {

        $errorMessages = [];

        foreach ($invalidRows as $row) {

            foreach ($row['errors'] as $error) {

                $errorMessages[] =
                    'Baris ' .
                    $row['excel_row'] .
                    ': ' .
                    $error;
            }
        }

        return redirect()
            ->route('siswas.import.form')
            ->with(
                'error',
                'Data tidak dapat diimport karena masih terdapat data yang tidak valid.'
            )
            ->with('import_errors', $errorMessages);
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT DATABASE
    |--------------------------------------------------------------------------
    */

   try {

    $totalImported = 0;

    DB::transaction(function () use (
        $validatedRows,
        &$totalImported
    ) {

        foreach ($validatedRows as $row) {

            /*
            |--------------------------------------------------------------------------
            | Buat User
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'name' => trim($row['nama_siswa']),
                'email' => strtolower(
                    trim($row['email'])
                ),
                'password' => Hash::make('123123123'),
                'avatar' => null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Role Siswa
            |--------------------------------------------------------------------------
            */

            $user->assignRole('siswa');


            /*
            |--------------------------------------------------------------------------
            | Buat Siswa
            |--------------------------------------------------------------------------
            */

            Siswa::create([
                'user_id' => $user->id,

                'ustadz_id' => (int) $row['id_ustadz'],

                'sub_kelas_id' => (int) $row['id_sub_kelas'],

                'kelamin' => strtolower(
                    trim($row['kelamin'])
                ),

                'tempat_lahir' => trim(
                    $row['tempat_lahir'] ?? ''
                ),

                'tgl_lahir' => $row['tgl_lahir'] ?? null,

                'alamat' => trim(
                    $row['alamat'] ?? ''
                ),

                'no_hp' => trim(
                    (string) ($row['no_hp'] ?? '')
                ),
            ]);


            $totalImported++;
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Hapus file sementara
    |--------------------------------------------------------------------------
    */

    Storage::disk('local')->delete($path);

    session()->forget('siswa_import');


    return redirect()
        ->route('siswas.index')
        ->with(
            'success',
            $totalImported . ' data siswa berhasil diimport.'
        );


} catch (\Throwable $e) {

    report($e);

    Log::error('IMPORT SISWA GAGAL', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    return redirect()
        ->route('siswas.import.form')
        ->with(
            'error',
            'Import gagal: ' . $e->getMessage()
        );
}
}

}
