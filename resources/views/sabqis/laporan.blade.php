@extends('layouts.app')
@section('content_title','Laporan sabqi')

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('sabqis.laporan') }}">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="start_date">Dari Tanggal:</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="end_date">Sampai Tanggal:</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <button type="button" class="btn btn-success" id="openPdf">Buka PDF</button>
                    <a id="exportExcel" href="#" class="btn btn-info">Export to Excel</a>
                </div>
            </form>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    @if($sabqis->isNotEmpty())
                    <table id="paketTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Sabqi</th>
                                <th>Nama Siswa</th>
                                <th>Nama Surat</th>
                                <th>Ayat</th>
                                <th>Ustadz/Ustadzah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sabqis as $index => $history)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($history->tgl_sabqi)->format('d M Y') }}</td>
                                    <td>{{ $history->siswa->user->name }}</td>
                                    <td>{{ $history->surat->sura_name }}</td>
                                    <td>{{ $history->dariayat }} - {{ $history->sampaiayat }}</td>
                                    <td>{{ $history->ustadz->user->name }}</td>
                                    <td>{{ $history->keterangan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p>Data tidak ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
@endsection


<!-- SweetAlert2 Script -->
@section('scripts')
<script>
    $(document).ready(function () {
        // Handle Filter Form submission
        $('#filterForm').submit(function (e) {
            e.preventDefault();  // Prevent the default form submission

            // Ambil nilai start_date dan end_date
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();

            // Membuat query string baru tanpa duplikasi
            var queryString = '?start_date=' + start_date + '&end_date=' + end_date;

            // Redirect ke halaman filter dengan query yang benar
            window.location.href = $(this).attr('action') + queryString;
        });

        // Handle PDF button click
        $('#openPdf').on('click', function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            
            // Periksa apakah tanggal valid
            if (!start_date || !end_date) {
                alert('Harap pilih tanggal terlebih dahulu');
                return;
            }
            
            // Buat URL untuk membuka PDF
            var url = "{{ route('sabqis.laporan') }}?start_date=" + start_date + "&end_date=" + end_date + "&pdf=true";
            
            // Buka URL di tab baru
            window.open(url, '_blank');
        });

        // Update the Export to Excel link
        $('#exportExcel').on('click', function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            
            // Ensure start_date and end_date are present
            if (!start_date || !end_date) {
                alert('Harap pilih tanggal terlebih dahulu');
                return;
            }
            
            // Construct the URL with the selected dates
            var url = "{{ route('sabqis.export') }}?start_date=" + start_date + "&end_date=" + end_date;
            
            // Redirect to the URL to download Excel
            window.location.href = url;
        });
    });
</script>
@endsection