@extends('layouts.app')
@section('content_title','Kelas')

@section('content')
    
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tambah Data</h3>
        </div>
        <form method="POST" action="{{route('siswas.store')}}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Siswa</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sub Kelas</label>
                            <select class="form-control" name="sub_kelas_id" id="sub_kelas_id">
                                <option value="">Pilih Sub Kelas</option>
                                @foreach ($SubKelas as $p)
                                    <option value="{{ $p->id }}" {{ old('sub_kelas_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_sub_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sub_kelas_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                   <div class="col-md-6">
                        <div class="form-group">
                            <label>Ustadz / Ustadzah</label>
                            <select class="form-control" name="ustadz_id" id="ustadz_id">
                                <option value="">Pilih Ustadz</option>
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
                                <option value="laki-laki" {{ old('kelamin') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="perempuan" {{ old('kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('kelamin')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                            @error('tempat_lahir')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Tgl Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" value="{{ old('tgl_lahir') }}">
                            @error('tgl_lahir')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Alamat</label>
                            <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}">
                            @error('alamat')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >No HP</label>
                            <input type="number" name="no_hp" class="form-control" value="{{ old('no_hp') }}">
                            @error('no_hp')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >Email</label>
                            <input type="text" name="email" class="form-control" value="{{ old('email') }}">
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

        $('#sub_kelas_id').change(function () {

            let subKelasId = $(this).val();

            // Kosongkan dropdown ustadz
            $('#ustadz_id').empty();

            // Default option
            $('#ustadz_id').append(
                '<option value="">Pilih Ustadz</option>'
            );

            if (!subKelasId) {
                return;
            }

            $.ajax({
                url: "{{ url('/get-ustadz') }}/" + subKelasId,
                type: "GET",
                dataType: "json",

                success: function (data) {

                    if (data.length === 0) {
                        $('#ustadz_id').append(
                            '<option value="">Belum ada ustadz untuk sub kelas ini</option>'
                        );

                        return;
                    }

                    $.each(data, function (key, ustadz) {

                        $('#ustadz_id').append(
                            '<option value="' + ustadz.id + '">' +
                            ustadz.user.name +
                            '</option>'
                        );

                    });
                },

                error: function (xhr) {

                    console.log(xhr.responseText);

                    $('#ustadz_id').empty();

                    $('#ustadz_id').append(
                        '<option value="">Gagal mengambil data ustadz</option>'
                    );
                }
            });
        });

    });
</script>
@endsection