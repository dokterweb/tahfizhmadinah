@extends('layouts.app')
@section('content_title','Profile Sekolah')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title">Tambah Data</h3>
                </div>
                <form action="{{ route('settings.school.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label >Nama Sekolah</label>
                            <input type="text" name="nama_sekolah" class="form-control"  value="{{ old('name', $profile->nama_sekolah ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label >alamat</label>
                            <input type="text" name="alamat" class="form-control"  value="{{ old('address', $profile->alamat ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label >Telepon</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile->phone ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label >Logo</label>
                            <input type="file" name="logo" class="form-control-file">
                        </div>
                    </div>
                    
                    <!-- /.card-body -->

                    <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                <h3 class="card-title">Logo</h3>
                </div>
                <div class="card-body">
                    @if(!empty($profile->logo_url))
                    <div>
                        <img src="{{ $profile->logo_url }}" alt="logo" style="max-width:200px">
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>


@endsection