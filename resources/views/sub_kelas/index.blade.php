@extends('layouts.app')
@section('content_title','Kelas')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title">Tambah Data</h3>
                </div>
                <form method="POST" action="{{route('sub_kelas.store')}}">
                    @csrf
                    <div class="card-body">
                       <div class="form-group">
                            <label >Nama Kelas</label>
                            <select name="kelas_id"
                            class="form-control @error('kelas_id') is-invalid @enderror">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasnyas as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                             @error('kelas_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                         <div class="form-group">
                              <label class="form-label">Nama Sub Kelas</label>
                            <input type="text" name="nama_sub_kelas" value="{{ old('nama_sub_kelas') }}"
                                class="form-control @error('nama_sub_kelas') is-invalid @enderror"
                                placeholder="Contoh: 7A">
                            @error('nama_sub_kelas')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('sub-kelas.export.excel') }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </a>
                </div>
                <div class="card-body">
                    <table id="paketTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ID</th>
                                <th>Nama Kelas</th>
                                <th>Nama Sub Kelas</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($subKelas as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->kelasnya->nama_kelas ?? '-' }}</td>
                                <td><strong>{{ $item->nama_sub_kelas }}</strong></td>
                                <td>
                                    <a href="{{ route('sub_kelas.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('sub_kelas.destroy', $item->id) }}"
                                        method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sub kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Belum ada data sub kelas.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection