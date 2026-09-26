@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @if (session('error'))

    <div class="alert alert-danger">

        <i class="fas fa-exclamation-triangle mr-1"></i>

        {{ session('error') }}

        @if (session('import_errors'))

            <ul class="mb-0 mt-2">

                @foreach (session('import_errors') as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        @endif

    </div>

@endif

@if (session('success'))

    <div class="alert alert-success">

        <i class="fas fa-check-circle mr-1"></i>

        {{ session('success') }}

    </div>

@endif

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-import mr-1"></i>
                Import Data Siswa
            </h3>
        </div>

        <div class="card-body">

            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Upload Excel --}}
            <form
                action="{{ route('siswas.import.preview') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="form-group">

                    <label>
                        File Excel
                    </label>

                    <div class="custom-file">

                        <input
                            type="file"
                            name="file"
                            class="custom-file-input"
                            id="file"
                            accept=".xlsx,.xls"
                            required
                        >

                        <label
                            class="custom-file-label"
                            for="file"
                        >
                            Pilih file Excel
                        </label>

                    </div>

                    <small class="form-text text-muted">
                        Format yang diperbolehkan: XLSX atau XLS.
                        Maksimal 5 MB.
                    </small>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-search mr-1"></i>
                    Baca & Preview Excel
                </button>

                <a
                    href="{{ route('siswas.index') }}"
                    class="btn btn-secondary"
                >
                    Kembali
                </a>

            </form>

        </div>

    </div>


    {{-- Preview --}}
@isset($validatedRows)

    <div class="card mt-4">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-table mr-1"></i>
                Preview Data Excel
            </h3>
        </div>

        <div class="card-body">

            {{-- Ringkasan --}}
            @if ($allValid)

                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-1"></i>

                    Semua data valid.

                    Total:
                    <strong>{{ $totalRows }}</strong>
                    data siap diimport.
                </div>

            @else

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-triangle mr-1"></i>

                    Ditemukan
                    <strong>{{ $invalidRows }}</strong>
                    data bermasalah dari
                    <strong>{{ $totalRows }}</strong>
                    data.

                    <br>

                    Data belum dapat diimport.
                    Silakan perbaiki Excel terlebih dahulu.

                </div>

            @endif


            {{-- Statistik --}}
            <div class="row mb-3">

                <div class="col-md-4">

                    <div class="info-box">

                        <span class="info-box-icon bg-info">
                            <i class="fas fa-file-excel"></i>
                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Total Data
                            </span>

                            <span class="info-box-number">
                                {{ $totalRows }}
                            </span>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="info-box">

                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check"></i>
                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Data Valid
                            </span>

                            <span class="info-box-number">
                                {{ $validRows }}
                            </span>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="info-box">

                        <span class="info-box-icon bg-danger">
                            <i class="fas fa-times"></i>
                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Data Bermasalah
                            </span>

                            <span class="info-box-number">
                                {{ $invalidRows }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Tabel --}}
            <div class="table-responsive">

                <table class="table table-bordered table-striped table-sm">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Email</th>
                            <th>ID Ustadz</th>
                            <th>ID Sub Kelas</th>
                            <th>Kelamin</th>
                            <th>Tempat Lahir</th>
                            <th>Tgl Lahir</th>
                            <th>Alamat</th>
                            <th>No HP</th>
                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($validatedRows as $row)

                            <tr>

                                <td>
                                    {{ $row['excel_row'] }}
                                </td>

                                <td>
                                    {{ $row['nama_siswa'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['email'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['id_ustadz'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['id_sub_kelas'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['kelamin'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['tempat_lahir'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['tgl_lahir'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['alamat'] ?? '-' }}
                                </td>

                                <td>
                                    {{ $row['no_hp'] ?? '-' }}
                                </td>

                                <td style="min-width: 250px;">

                                    @if ($row['is_valid'])

                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>
                                            Valid
                                        </span>

                                    @else

                                        <span class="badge badge-danger">
                                            <i class="fas fa-times mr-1"></i>
                                            Tidak Valid
                                        </span>

                                        <ul class="text-danger mt-2 mb-0 pl-3">

                                            @foreach ($row['errors'] as $error)

                                                <li>
                                                    {{ $error }}
                                                </li>

                                            @endforeach

                                        </ul>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="11"
                                    class="text-center text-muted"
                                >
                                    Tidak ada data.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Tombol import --}}
            @if ($allValid)

                <div class="mt-3">

                    <div class="alert alert-success">

                        <i class="fas fa-check-circle mr-1"></i>

                        Semua data sudah lolos validasi dan siap
                        dimasukkan ke database.

                    </div>

                    {{-- Tombol import akan kita aktifkan di tahap berikutnya --}}

                 @if ($allValid)

    <div class="mt-3">

        <div class="alert alert-success">

            <i class="fas fa-check-circle mr-1"></i>

            Semua data sudah lolos validasi dan siap
            dimasukkan ke database.

        </div>

        <form
            action="{{ route('siswas.import.store') }}"
            method="POST"
        >

            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ session('siswa_import.token') }}"
            >

            <button
                type="submit"
                class="btn btn-success"
                onclick="return confirm('Apakah Anda yakin ingin mengimport {{ $totalRows }} data siswa?')"
            >

                <i class="fas fa-database mr-1"></i>

                Import {{ $totalRows }} Data

            </button>

        </form>

    </div>

@endif

                </div>

            @endif

        </div>

    </div>

@endisset

</div>

@endsection


@push('scripts')

<script>

$(document).ready(function () {

    $('.custom-file-input').on('change', function () {

        let fileName = $(this).val().split('\\').pop();

        $(this)
            .next('.custom-file-label')
            .html(fileName);

    });

});

</script>

@endpush