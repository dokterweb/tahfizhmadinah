@extends('layouts.app')
@section('content_title','Siswa')

@section('content')
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    <div class="card">
        <div class="card-body">
            <form id="formAbsensi" action="{{ route('absensis.store') }}" method="POST">
                @csrf  
                <!-- Dropdown Pilih Kelas -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="kelas">Pilih Kelas</label>
                        <select name="sub_kelas_id" id="subkelas" class="form-control">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($SubKelas as $kelasItem)
                                <option value="{{ $kelasItem->id }}">{{ $kelasItem->nama_sub_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tgl_absen">Tanggal Absensi</label>
                        <input type="date" name="tgl_absen" id="tgl_absen" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <hr>
                    <!-- Daftar Siswa yang Muncul Berdasarkan Kelas -->
                    <div id="siswa_list" class="form-group" style="display:none;">
                        <label for="siswa">Data Siswa</label>
                        <hr>
                        <div id="siswa_container">
                            <!-- Siswa akan ditambahkan secara dinamis dengan jQuery -->
                        </div>
                    </div>
            
                    <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                </div>
            </form>
            
        </div>
    </div>
    
@endsection


<!-- SweetAlert2 Script -->
@section('scripts')
<script>
$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Ambil siswa berdasarkan Sub Kelas
    |--------------------------------------------------------------------------
    */
    function getSiswa() {

        var subKelasId = $('#subkelas').val();

        if (!subKelasId) {
            $('#siswa_container').empty();
            $('#siswa_list').hide();
            return;
        }

        $.ajax({

            url: "{{ url('/get-siswa') }}/" + subKelasId,

            type: 'GET',

            dataType: 'json',

            beforeSend: function () {

                $('#siswa_container').html(
                    '<div class="text-muted">Memuat data siswa...</div>'
                );

                $('#siswa_list').show();
            },

            success: function (response) {

                $('#siswa_container').empty();

                if (!response.siswa || response.siswa.length === 0) {

                    $('#siswa_container').html(`
                        <div class="alert alert-warning">
                            Tidak ada siswa pada sub kelas ini.
                        </div>
                    `);

                    return;
                }

                var siswaList = '';

                response.siswa.forEach(function (siswa) {

                    var siswaName = siswa.user
                        ? siswa.user.name
                        : 'Nama siswa tidak ditemukan';

                    siswaList += `
                        <div class="form-group mb-3">

                            <label for="status_${siswa.id}">
                                ${siswaName}
                            </label>

                            <select
                                class="form-control"
                                name="status[${siswa.id}]"
                                id="status_${siswa.id}"
                                required
                            >
                                <option value="hadir">Hadir</option>
                                <option value="absen">Absen</option>
                                <option value="izin">Izin</option>
                            </select>

                        </div>
                    `;
                });

                $('#siswa_container').html(siswaList);
                $('#siswa_list').show();
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                var message = 'Terjadi kesalahan saat mengambil data siswa.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: message
                });

                $('#siswa_list').hide();
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Ketika Sub Kelas berubah
    |--------------------------------------------------------------------------
    */
    $('#subkelas').on('change', function () {

        getSiswa();

    });


    /*
    |--------------------------------------------------------------------------
    | Submit Form
    |--------------------------------------------------------------------------
    */
    $('#formAbsensi').on('submit', function (event) {

        event.preventDefault();

        var form = $(this);

        var subKelasId = $('#subkelas').val();
        var tglAbsen = $('#tgl_absen').val();

        /*
        |--------------------------------------------------------------------------
        | Validasi awal
        |--------------------------------------------------------------------------
        */

        if (!subKelasId) {

            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih sub kelas terlebih dahulu.'
            });

            return;
        }

        if (!tglAbsen) {

            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih tanggal absensi.'
            });

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Cek apakah absensi sudah ada
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url: "{{ route('check-absensi') }}",

            type: 'POST',

            data: {
                _token: "{{ csrf_token() }}",
                sub_kelas_id: subKelasId,
                tgl_absen: tglAbsen
            },

            beforeSend: function () {

                form.find('button[type="submit"]')
                    .prop('disabled', true);
            },

            success: function (response) {

                if (response.exists) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Absensi Sudah Ada',
                        text: 'Absensi untuk sub kelas dan tanggal tersebut sudah dibuat.',
                        confirmButtonText: 'OK'
                    });

                    form.find('button[type="submit"]')
                        .prop('disabled', false);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Simpan Absensi
                |--------------------------------------------------------------------------
                */

                $.ajax({

                    url: "{{ route('absensis.store') }}",

                    type: 'POST',

                    data: form.serialize(),

                    dataType: 'json',

                    success: function (response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Absensi berhasil disimpan.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function () {

                            window.location.href =
                                "{{ route('absensis') }}";

                        });
                    },

                    error: function (xhr) {

                        console.error(xhr.responseText);

                        var message =
                            'Terjadi kesalahan saat menyimpan absensi.';

                        if (xhr.responseJSON) {

                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            if (xhr.responseJSON.errors) {

                                message = Object.values(
                                    xhr.responseJSON.errors
                                )
                                .flat()
                                .join('<br>');
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: message
                        });

                        form.find('button[type="submit"]')
                            .prop('disabled', false);
                    }
                });
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                var message =
                    'Terjadi kesalahan saat memeriksa absensi.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: message
                });

                form.find('button[type="submit"]')
                    .prop('disabled', false);
            }
        });

    });

});
</script>

@endsection