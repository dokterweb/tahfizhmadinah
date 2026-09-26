@extends('layouts.app')
@section('content_title','Siswa')

@section('content')
    
    <div class="card">
        <div class="card-body">
            <table id="paketTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID Siswa</th>
                        <th>Nama Siswa</th>
                        <th>ID SubKelas</th>
                        <th>Nama Kelas</th>
                        <th>Nama Ustadz</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($sabaqs as $p)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$p->id}} </td>
                        <td>{{$p->user->name}} </td>
                        <td>{{$p->subKelas->id}} </td>
                        <td>{{$p->subKelas->nama_sub_kelas}} </td>
                        <td>{{$p->ustadz->user->name}} </td>
                        <td class="d-flex align-items-center" style="gap: 5px;">
                           <a href="{{route('sabaq-history.show',$p->id)}}" class="btn btn-sm btn-info"><i class="far fa-edit"></i></a>
                           {{--   <form method="POST" action="{{ route('siswas.destroy', $p->id) }}" style="display: inline;" id="delete-form-{{ $p->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteConfirmation({{ $p->id }})">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form> --}}
                            
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="5">No Data</td>
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
                text: "Data ini akan dihapus dan tidak dapat dipulihkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika tombol "Ya, Hapus!" ditekan, kirim form untuk menghapus data
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection