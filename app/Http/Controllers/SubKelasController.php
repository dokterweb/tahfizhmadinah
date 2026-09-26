<?php

namespace App\Http\Controllers;

use App\Models\Kelasnya;
use App\Models\SubKelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\SubKelasExport;
use Maatwebsite\Excel\Facades\Excel;

class SubKelasController extends Controller
{
    public function index()
    {
        $subKelas = SubKelas::with('kelasnya')
            ->orderBy('kelas_id')
            ->orderBy('nama_sub_kelas')
            ->get();
        $kelasnyas = Kelasnya::all();
        return view('sub_kelas.index',compact('subKelas','kelasnyas'));
    }

     public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => [
                'required',
                'integer',
                'exists:kelasnyas,id',
            ],
            'nama_sub_kelas' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_kelas', 'nama_sub_kelas')
                    ->where(fn ($query) => $query->where('kelas_id', $request->kelas_id)),
            ],
        ], [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas tidak ditemukan.',
            'nama_sub_kelas.required' => 'Nama sub kelas wajib diisi.',
            'nama_sub_kelas.unique' => 'Sub kelas tersebut sudah ada pada kelas yang dipilih.',
        ]);

        SubKelas::create($validated);

        return redirect()
            ->route('sub_kelas.index')
            ->with('success', 'Sub kelas berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $editSubKelas = SubKelas::findOrFail($id);

        $subKelas = SubKelas::with('kelasnya')
            ->orderBy('kelas_id')
            ->orderBy('nama_sub_kelas')
            ->get();

        $kelasnyas = Kelasnya::orderBy('nama_kelas')->get();

        return view('sub_kelas.edit', compact(
            'editSubKelas',
            'subKelas',
            'kelasnyas'
        ));
    }

    public function update(Request $request, $id)
    {
        $subKelas = SubKelas::findOrFail($id);

        $validated = $request->validate([
            'kelas_id' => [
                'required',
                'integer',
                'exists:kelasnyas,id',
            ],
            'nama_sub_kelas' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_kelas', 'nama_sub_kelas')
                    ->where(fn ($query) => $query->where('kelas_id', $request->kelas_id))
                    ->ignore($subKelas->id),
            ],
        ], [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas tidak ditemukan.',
            'nama_sub_kelas.required' => 'Nama sub kelas wajib diisi.',
            'nama_sub_kelas.unique' => 'Sub kelas tersebut sudah ada pada kelas yang dipilih.',
        ]);

        $subKelas->update($validated);

        return redirect()
            ->route('sub_kelas.index')
            ->with('success', 'Sub kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $subKelas = SubKelas::findOrFail($id);

        $subKelas->delete();

        return redirect()
            ->route('sub_kelas.index')
            ->with('success', 'Sub kelas berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new SubKelasExport,'data_sub_kelas.xlsx');
    }
}
