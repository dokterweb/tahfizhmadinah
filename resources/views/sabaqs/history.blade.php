@extends('layouts.app')
@section('content_title','Data Sabaq')

@section('content')
    
    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nama Murid</th>
                            <td>{{ $siswa->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Tgl Lahir</th>
                            <td>{{ \Carbon\Carbon::parse($siswa->tgl_lahir)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $siswa->no_hp }}</td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>{{ $siswa->subKelas->nama_sub_kelas }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if ($siswa->user->avatar)
                        <img src="{{Storage::url($siswa->user->avatar)}}" width="200">
                    @endif
                </div>
            </div>
            <hr>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahHafalanModal">
                Tambah Sabaq
            </button>
            <div class="card mb-3">
                <div class="card-body">

                    <form method="GET"
                        action="{{ route('sabaq-history.show', ['siswa_id' => $siswa_id]) }}">

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">
                                        Dari Tanggal
                                    </label>

                                    <input
                                        type="date"
                                        name="start_date"
                                        id="start_date"
                                        class="form-control"
                                        value="{{ $startDate }}"
                                    >
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">
                                        Sampai Tanggal
                                    </label>

                                    <input
                                        type="date"
                                        name="end_date"
                                        id="end_date"
                                        class="form-control"
                                        value="{{ $endDate }}"
                                    >
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>&nbsp;</label>

                                    <div>
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            <i class="fas fa-search"></i>
                                            Tampilkan
                                        </button>

                                        <a
                                            href="{{ route('sabaq-history.show', [
                                                'siswa_id' => $siswa_id
                                            ]) }}"
                                            class="btn btn-secondary"
                                        >
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
            <div class="table-responsive p-2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tgl Sabaq</th>
                            <th>Nama Surat</th>
                            <th>Dari dan ke Ayat</th>
                            <th>Nilai</th>
                            <th>Keterangan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sabaqHistories as $index => $history)
                            <tr>

                                <td>
                                    {{ $sabaqHistories->firstItem() + $index }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($history->tgl_sabaq)->format('d F Y') }}
                                </td>

                                <td>
                                    {{ $history->surat?->sura_name ?? 'Surat Tidak Ditemukan' }}
                                </td>

                                <td>
                                    {{ $history->dariayat }} - {{ $history->sampaiayat }}
                                </td>

                                <td>
                                    {{ $history->nilai }}
                                </td>

                                <td>
                                    {{ $history->keterangan }}
                                </td>

                                <td>

                                    <button
                                        class="btn btn-sm btn-warning btn-edit"
                                        data-id="{{ $history->id }}"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger delete-button"
                                        data-id="{{ $history->id }}"
                                        data-url="{{ route('sabaq-history.destroy', [
                                            'siswa_id' => $siswa_id,
                                            'id' => $history->id
                                        ]) }}"
                                    >
                                        Hapus
                                    </button>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Tidak ada data Sabaq pada periode
                                    {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }}
                                    sampai
                                    {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
 
<!-- Modal create -->
<div class="modal fade" id="tambahHafalanModal" tabindex="-1" role="dialog"
    aria-labelledby="tambahHafalanModalLabel">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="tambahHafalanModalLabel">
                    Tambah Sabaq
                </h4>

                <button type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">

                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formTambahHafalan"
                method="POST"
                action="{{ route('sabaq.store') }}">

                @csrf

                <div class="modal-body">

                    {{-- ID Siswa --}}
                    <input type="hidden"
                        name="siswa_id"
                        value="{{ $siswa->id }}">

                    {{-- Ustadz yang menangani siswa saat ini --}}
                    <input type="hidden"
                        name="ustadz_id"
                        value="{{ $siswa->ustadz_id }}">

                    {{-- Sub Kelas siswa --}}
                    <input type="hidden"
                        name="sub_kelas_id"
                        value="{{ $siswa->sub_kelas_id }}">

                    <div class="row">

                        {{-- Tanggal --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Muroja'ah</label>

                                <input type="date"
                                    name="tgl_sabaq"
                                    class="form-control"
                                    value="{{ date('Y-m-d') }}"
                                    required>
                            </div>
                        </div>

                        {{-- Surat --}}
                        <div class="col-md-8">
                            <div class="form-group">

                                <label>Nama Surat</label>

                                <select name="surat_no"
                                    id="selectSurat"
                                    class="form-control"
                                    required>

                                    <option value="">
                                        Pilih Surat
                                    </option>

                                    @foreach($surat as $s)
                                        <option value="{{ $s->sura_no }}">
                                            {{ $s->sura_name }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>
                        </div>

                        {{-- Nomor Surat --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>No. Surat</label>

                                <input type="text"
                                    id="noSurat"
                                    class="form-control"
                                    readonly>

                            </div>
                        </div>

                        {{-- Juz --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Juz</label>

                                <input type="text"
                                    id="juz"
                                    class="form-control"
                                    readonly>

                            </div>
                        </div>

                        {{-- Mulai Hal --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Mulai Hal</label>

                                <input type="text"
                                    id="mulaiHal"
                                    class="form-control"
                                    readonly>

                            </div>
                        </div>

                        {{-- Akhir Hal --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Akhir Hal</label>

                                <input type="text"
                                    id="akhirHal"
                                    class="form-control"
                                    readonly>

                            </div>
                        </div>

                        {{-- Dari Ayat --}}
                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Dari Ayat</label>

                                <input type="number"
                                    name="dariayat"
                                    class="form-control"
                                    min="1"
                                    required>

                            </div>
                        </div>

                        {{-- Sampai Ayat --}}
                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Sampai Ayat</label>

                                <input type="number"
                                    name="sampaiayat"
                                    class="form-control"
                                    min="1"
                                    required>

                            </div>
                        </div>

                        {{-- Nilai --}}
                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Nilai</label>

                                <input type="number"
                                    name="nilai"
                                    class="form-control"
                                    min="0"
                                    required>

                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-md-12">
                            <div class="form-group">

                                <label>Keterangan</label>

                                <textarea name="keterangan"
                                    class="form-control"
                                    rows="3"></textarea>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                        Tutup
                    </button>

                    <button type="submit"
                        class="btn btn-primary">
                        Simpan
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<!-- Modal Edit -->
@if(isset($history))
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="sabaq_history_id" id="sabaq_history_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Sabaq</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_tgl_sabaq">Tanggal Muroja'ah</label>
                                <input type="date" name="tgl_sabaq" id="edit_tgl_sabaq" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit_sura_no">Nama Surat</label>
                                <select name="surat_no" class="form-control" id="edit_sura_no" required>
                                    <option value="">Pilih Surat</option>
                                    <!-- Surat akan diisi oleh jQuery -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>No. Surat</label>
                                <input type="text" class="form-control" id="edit_no_surat" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Juz</label>
                                <input type="text" class="form-control" id="edit_jozz" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Mulai Hal</label>
                                <input type="text" class="form-control" id="edit_start_page" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Akhir Hal</label>
                                <input type="text" class="form-control" id="edit_end_page" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dari Ayat</label>
                                <input type="number" name="dariayat" id="edit_dariayat" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sampai Ayat</label>
                                <input type="number" name="sampaiayat" id="edit_sampaiayat" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nilai</label>
                                <input type="number" name="nilai" id="edit_nilai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan" id="edit_keterangan" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
    <p>Data tidak ditemukan.</p>
@endif
 
@endsection


<!-- SweetAlert2 Script -->
@section('scripts')

<script>
    
$(document).ready(function() {

    $('#selectSurat').change(function() {

        var sura_no = $(this).val();

        // Reset informasi surat
        $('#noSurat').val('');
        $('#juz').val('');
        $('#mulaiHal').val('');
        $('#akhirHal').val('');

        if (!sura_no) {
            return;
        }

        $.ajax({
            url: '/get-surat-details/' + sura_no,
            type: 'GET',
            dataType: 'json',

            success: function(data) {

                if (data.status === 'success') {

                    $('#noSurat').val(data.data.no_surat);
                    $('#juz').val(data.data.jozz);
                    $('#mulaiHal').val(data.data.start_page);
                    $('#akhirHal').val(data.data.end_page);

                } else {

                    alert(data.message);

                }
            },

            error: function(xhr) {

                console.log(xhr.responseText);

                alert('Terjadi kesalahan saat memuat data surat.');

            }
        });

    });

});

$(document).ready(function () {
 
    $(document).on('click', '.btn-edit', function () {

        var historyId = $(this).data('id');

        $.ajax({
            url: '/sabaq/history/' + historyId + '/edit',
            type: 'GET',
            dataType: 'json',

            beforeSend: function () {
                $('#editForm button[type="submit"]').prop('disabled', true);
            },

            success: function (response) {

                if (response.status !== 'success') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Gagal memuat data.'
                    });

                    return;
                }

                var history = response.data;
                var suratList = response.suratList;
                var surat = response.surat;

                /*
                |--------------------------------------------------------------------------
                | Set action form
                |--------------------------------------------------------------------------
                */
                $('#editForm').attr(
                    'action',
                    '/sabaq/history/' + historyId + '/update'
                );

                /*
                |--------------------------------------------------------------------------
                | Isi data history
                |--------------------------------------------------------------------------
                */
                $('#sabaq_history_id').val(history.id);

                $('#edit_tgl_sabaq').val(history.tgl_sabaq);

                $('#edit_dariayat').val(history.dariayat);
                $('#edit_sampaiayat').val(history.sampaiayat);
                $('#edit_nilai').val(history.nilai);
                $('#edit_keterangan').val(history.keterangan);

                /*
                |--------------------------------------------------------------------------
                | Isi dropdown surat
                |--------------------------------------------------------------------------
                */
                $('#edit_sura_no').html(
                    '<option value="">Pilih Surat</option>'
                );

                suratList.forEach(function (item) {

                    $('#edit_sura_no').append(
                        $('<option>', {
                            value: item.sura_no,
                            text: item.sura_name
                        })
                    );

                });

                /*
                |--------------------------------------------------------------------------
                | Pilih surat yang sedang diedit
                |--------------------------------------------------------------------------
                */
                $('#edit_sura_no').val(surat.sura_no);

                /*
                |--------------------------------------------------------------------------
                | LANGSUNG isi readonly fields
                |--------------------------------------------------------------------------
                */
                $('#edit_no_surat').val('');
                $('#edit_jozz').val('');
                $('#edit_start_page').val('');
                $('#edit_end_page').val('');

                /*
                | start_page dan end_page kita ambil dari AJAX
                */
                loadSuratDetails(surat.sura_no);

                /*
                |--------------------------------------------------------------------------
                | Tampilkan modal
                |--------------------------------------------------------------------------
                */
                $('#editModal').modal('show');

                $('#editForm button[type="submit"]').prop('disabled', false);
            },

            error: function (xhr) {

                var message = 'Terjadi kesalahan saat memuat data.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: message
                });

                $('#editForm button[type="submit"]').prop('disabled', false);
            }
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Ketika surat diganti
    |--------------------------------------------------------------------------
    */
    $(document).on('change', '#edit_sura_no', function () {

        var selectedSuraNo = $(this).val();

        if (selectedSuraNo) {
            loadSuratDetails(selectedSuraNo);
        } else {

            $('#edit_no_surat').val('');
            $('#edit_jozz').val('');
            $('#edit_start_page').val('');
            $('#edit_end_page').val('');
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Function mengambil detail surat
    |--------------------------------------------------------------------------
    */
    function loadSuratDetails(suraNo) {

        $.ajax({
            url: '/get-surat-details/' + suraNo,
            type: 'GET',
            dataType: 'json',

            success: function (response) {

                if (response.status === 'success') {

                    $('#edit_no_surat')
                        .val(response.data.no_surat);

                    $('#edit_jozz')
                        .val(response.data.jozz);

                    $('#edit_start_page')
                        .val(response.data.start_page);

                    $('#edit_end_page')
                        .val(response.data.end_page);
                }
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Data detail surat tidak dapat dimuat.'
                });
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Submit Form Edit
    |--------------------------------------------------------------------------
    */
    $('#editForm').on('submit', function (event) {

        event.preventDefault();

        var form = $(this);
        var url = form.attr('action');

        var submitButton = form.find('button[type="submit"]');

        submitButton.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'PUT',
            data: form.serialize(),
            dataType: 'json',

            success: function (response) {

                if (response.status === 'success') {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Data berhasil diperbarui.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function () {

                        window.location.href = response.redirect_url;

                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Data gagal diperbarui.'
                    });

                    submitButton.prop('disabled', false);
                }
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                var message = 'Terjadi kesalahan saat menyimpan data.';

                /*
                |--------------------------------------------------------------------------
                | Validation Laravel
                |--------------------------------------------------------------------------
                */
                if (xhr.responseJSON) {

                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    if (xhr.responseJSON.errors) {

                        var errors = xhr.responseJSON.errors;

                        message = Object.values(errors)
                            .flat()
                            .join('<br>');
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: message,
                    confirmButtonText: 'OK'
                });

                submitButton.prop('disabled', false);
            }
        });
    });

});


$(document).on('click', '.delete-button', function() {
var url = $(this).data('url');  // URL untuk edit

// Tampilkan SweetAlert2 untuk konfirmasi hapus
Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: "Data ini akan dihapus permanen!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true
}).then((result) => {
    if (result.isConfirmed) {
        // Jika pengguna mengkonfirmasi hapus, kirim permintaan DELETE
        $.ajax({
            url: url, // Ambil URL dari data-url
            type: 'DELETE',  // Pastikan menggunakan metode DELETE
            data: {
                _token: '{{ csrf_token() }}'  // Kirim CSRF token untuk permintaan DELETE
            },
            success: function(response) {
                // Jika berhasil, tampilkan pesan sukses dan hapus baris di tabel
                Swal.fire(
                    'Dihapus!',
                    'Data berhasil dihapus.',
                    'success'
                ).then(function() {
                    location.reload(); // Reload halaman untuk memperbarui tampilan
                });
            },
            error: function() {
                Swal.fire(
                    'Gagal!',
                    'Terjadi kesalahan saat menghapus data.',
                    'error'
                );
            }
        });
    }
});
});


</script>

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