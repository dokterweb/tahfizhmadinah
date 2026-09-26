@extends('layouts.app')
@section('content_title','Siswa')

@section('content')
    
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Data</h3>
        </div>
        <form method="POST" action="{{route('siswas.update',$siswa->id)}}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Siswa</label>
                            <input type="text" name="name" class="form-control" value="{{ $siswa->user->name }}">
                        </div>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Kelas</label>
                            <select class="form-control" name="sub_kelas_id" id="sub_kelas_id">
                                <option value="">Pilih Kelas</option>
                                @foreach ($SubKelas as $p)
                                <option value="{{ $p->id }}" {{ $siswa->sub_kelas_id == $p->id ? 'selected' : '' }}>{{ $p->nama_sub_kelas }}</option>
                                @endforeach
                            </select>
                            @error('sub_kelas_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Ustadz / Ustadzah</label>
                            <select class="form-control" name="ustadz_id" id="ustadz_id">
                                @foreach ($ustadz as $p)
                                <option value="{{ $p->id }}" {{ $siswa->ustadz_id == $p->id ? 'selected' : '' }}>{{ $p->user->name }}</option>
                                @endforeach
                            </select>
                            @error('ustadz_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kelamin</label>
                            <select name="kelamin" class="form-control" style="width:100%">
                                <option value="laki-laki" {{ $siswa->kelamin == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="perempuan" {{ $siswa->kelamin == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('kelamin')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{$siswa->tempat_lahir }}">
                            @error('tempat_lahir')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Tgl Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" value="{{$siswa->tgl_lahir }}">
                            @error('tgl_lahir')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Alamat</label>
                            <input type="text" name="alamat" class="form-control" value="{{$siswa->alamat }}">
                            @error('alamat')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                   <div class="col-md-6">
                        <div class="form-group">
                            <label >No HP</label>
                            <input type="number" name="no_hp" class="form-control" value="{{$siswa->no_hp }}">
                            @error('no_hp')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Email</label>
                            <input type="text" name="email" class="form-control" value="{{ $siswa->user->email }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Password</label>
                            <input type="password" name="password" class="form-control">
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputFile">Gambar</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="avatar" class="custom-file-input">
                                    <label class="custom-file-label">Choose file</label>
                                </div>
                            </div>
                            @error('avatar')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if ($siswa->user->avatar)
                            <img src="{{Storage::url($siswa->user->avatar)}}" width="200">
                        @endif
                    </div>
                </div>
                
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Ketika Kelas dipilih
        $('#sub_kelas_id').change(function () {
            var sub_kelas_id = $(this).val();

            if (sub_kelas_id) {
                // Mengirim AJAX request untuk mendapatkan ustadz berdasarkan sub_kelas_id
                $.ajax({
                    url: '/get-ustadz/' + sub_kelas_id,  // URL AJAX
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // Kosongkan dropdown Ustadz
                        $('#ustadz_id').empty();
                        
                        // Tambahkan option default
                        $('#ustadz_id').append('<option value="">Pilih Ustadz</option>');

                        // Loop dan tambahkan ustadz ke dropdown
                        $.each(data, function (key, ustadz) {
                            $('#ustadz_id').append('<option value="' + ustadz.id + '">' + ustadz.user.name + '</option>');
                        });
                    },
                    error: function (xhr, status, error) {
                        console.log('Terjadi kesalahan saat mengambil data ustadz: ' + error);
                    }
                });
            } else {
                // Jika tidak ada kelas yang dipilih, kosongkan dropdown Ustadz
                $('#ustadz_id').empty();
                $('#ustadz_id').append('<option value="">Pilih Ustadz</option>');
            }
        });
    });
</script>
@endsection