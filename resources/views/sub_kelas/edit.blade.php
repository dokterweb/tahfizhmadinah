@extends('layouts.app')
@section('content_title','Kelas')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title">Tambah Data</h3>
                </div>
                <form action="{{ route('sub_kelas.update', $editSubKelas->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        {{-- KELAS --}}
                        <div class="form-group">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id"
                                    class="form-control @error('kelas_id') is-invalid @enderror">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasnyas as $kelas)
                                    <option value="{{ $kelas->id }}" {{ old( 'kelas_id', $editSubKelas->kelas_id ) == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- NAMA SUB KELAS --}}
                        <div class="form-group">
                            <label class="form-label">Nama Sub Kelas</label>
                            <input type="text" name="nama_sub_kelas" value="{{ old('nama_sub_kelas', $editSubKelas->nama_sub_kelas) }}"
                                class="form-control @error('nama_sub_kelas') is-invalid @enderror">
                            @error('nama_sub_kelas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- BUTTON --}}
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                <h3 class="card-title">List Kelas</h3>
                </div>
                <div class="card-body">
                    <table id="paketTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Kelas</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                          @forelse($subKelas as $item)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$item->nama_kelas}} </td>
                                <td><strong>{{ $item->nama_sub_kelas }}</strong></td>
                                <td>
                                    <a href="{{ route('sub_kelas.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('sub_kelas.destroy', $item->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sub kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

     <!-- SweetAlert2 Script -->
     @if (session('success'))
     <script>
         Swal.fire({
             icon: 'success',
             title: 'Berhasil',
             text: "{{ session('success') }}",
             position: 'top-end',
             showConfirmButton: false,
             timer: 1500
         });
     </script>
    @elseif (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}",
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif
@endsection