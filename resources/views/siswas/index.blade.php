@extends('layouts.app')
@section('content_title','Siswa')

@section('content')
    
    <div class="card">
        <div class="card-header">
            <a href="{{route('siswas.create')}}" class="btn btn-info">
                <i class="fas fa-plus-circle"></i> Tambah
            </a>
            <a href="{{ route('siswas.export.excel') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i>Export Excel
            </a>
            <a href="{{ route('siswas.import.form') }}" class="btn btn-primary">
                <i class="fas fa-file-import"></i>Import Excel
            </a>
        </div>
        <div class="card-body table-responsive p-2">
            <table id="paketTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Siswa</th>
                        <th>Nama Kelas</th>
                        <th>Nama Ustadz</th>
                        <th>Kelamin</th>
                        <th>No. HP</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($siswas as $p)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$p->user->name}} </td>
                        <td>{{$p->subKelas->nama_sub_kelas}} </td>
                        <td>{{$p->ustadz->user->name}} </td>
                        <td>{{$p->kelamin}} </td>
                        <td>{{$p->no_hp}} </td>
                        <td class="d-flex align-items-center" style="gap: 5px;">
                            <a href="{{route('siswas.edit',$p->id)}}" class="btn btn-sm btn-info"><i class="far fa-edit"></i></a>
                            <form method="POST" action="{{ route('siswas.destroy', $p->id) }}" style="display: inline;" id="delete-form-{{ $p->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteConfirmation({{ $p->id }})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="8">No Data</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
@endsection


<!-- SweetAlert2 Script -->
@section('scripts')

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
   <script>
        $(document).ready(function () {
            $('#paketTable').DataTable();
        });

        function deleteConfirmation(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data siswa akan dipindahkan ke data terhapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection