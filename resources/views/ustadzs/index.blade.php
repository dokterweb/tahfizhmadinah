@extends('layouts.app')
@section('content_title','Ustadz')

@section('content')
    
    <div class="card">
        <div class="card-header">
        <a href="{{route('ustadzs.create')}}" class="btn btn-info">
            <i class="fas fa-plus-circle"></i> Tambah
        </a>
        <a href="{{ route('ustadzs.export.excel') }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i>Export Excel
        </a>
        </div>
        <div class="card-body">
            <table id="paketTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Nama Ustadz</th>
                        <th>Nama Kelas</th>
                        <th>Kelamin</th>
                        <th>No. HP</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($ustadzs as $p)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$p->id}} </td>
                        <td>{{$p->user->name}} </td>
                        <td>
                            @forelse ($p->subKelas as $sub)
                                <span class="badge badge-primary mr-1 mb-1">
                                    {{ $sub->nama_sub_kelas }}
                                </span>
                            @empty
                                <span class="text-muted">
                                    Belum ada sub kelas
                                </span>
                            @endforelse
                        </td>
                        <td>{{$p->kelamin}} </td>
                        <td>{{$p->no_hp}} </td>
                        <td class="d-flex align-items-center" style="gap: 5px;">
                            <a href="{{route('ustadzs.edit',$p->id)}}" class="btn btn-sm btn-info"><i class="far fa-edit"></i></a>
                           <form method="POST" action="{{ route('ustadzs.destroy', $p->id) }}" style="display: inline;" id="delete-form-{{ $p->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteConfirmation({{ $p->id }})">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="6">No Data</td>
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
                text: "Data ustadz akan dipindahkan ke data terhapus.",
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