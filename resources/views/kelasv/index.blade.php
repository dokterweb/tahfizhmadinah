@extends('layouts.app')
@section('content_title','Kelas')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title">Tambah Data</h3>
                </div>
                <form method="POST" action="{{route('kelasnyas.store')}}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label >Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control">
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
            <div class="card card-success">
                <div class="card-header">
                <h3 class="card-title">List Kelas</h3>
                </div>
                <div class="card-body">
                    <table id="paketTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ID</th>
                                <th>Nama Kelas</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($kelasnya as $p)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$p->id}} </td>
                                <td>{{$p->nama_kelas}} </td>
                                <td class="d-flex align-items-center" style="gap: 5px;">
                                    <a href="{{route('kelasnyas.edit',$p->id)}}" class="btn btn-sm btn-info"><i class="far fa-edit"></i></a>
                                    <form method="POST" action="{{ route('kelasnyas.destroy', $p->id) }}" style="display: inline;" id="delete-form-{{ $p->id }}">
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
                            <td colspan="3">No Data</td>
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