@extends('layouts.app')
@section('content_title','Absensi')

@section('content')

    <div class="card">
        <div class="card-header">
            <a href="{{route('absensis.ustadzCreate')}}" class="btn btn-info"><i class="fas fa-plus-circle"></i> Tambah
            </a>
        </div>
        <div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('absensis.ustadzIndex') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Tampilkan
                            </button>
                            <a href="{{ route('absensis.ustadzIndex') }}" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse  ($absensi as $absen)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $absen->siswa->user->name }}</td>
                        <td>{{ $absen->siswa->kelasnya->nama_kelas }}</td>
                        <td>{{ \Carbon\Carbon::parse( $absen->tgl_absen)->format('d F Y') }}</td>
                        <td>{{ ucfirst($absen->status) }}</td>
                        <td>
                            <!-- Tombol Hapus -->
                            <form action="{{ route('absensis.destroy', $absen->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE') <!-- Method spoofing untuk DELETE -->
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Anda yakin ingin menghapus absensi ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data absensi.</td>
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
@endsection