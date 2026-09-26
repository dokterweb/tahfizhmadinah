@extends('layouts.app')
@section('content_title','History Sabqi Siswa')

@section('content')
    
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-1"></i>
            Filter History sabqi
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('sabqis.sabqisiswa') }}">
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
                                <i class="fas fa-search mr-1"></i>Tampilkan
                            </button>
                            <a href="{{ route('sabqis.sabqisiswa') }}" class="btn btn-secondary">
                                <i class="fas fa-sync-alt mr-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-1"></i>
            History sabqi
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Surat</th>
                        <th>Ayat</th>
                        <th>Nilai</th>
                        <th>Ustadz/Ustadzah</th>
                        <th>Sub Kelas</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sabqiHistories as $index => $history)
                        <tr>
                            <td>{{ $sabqiHistories->firstItem() + $index }}</td>
                            <td>{{ \Carbon\Carbon::parse($history->tgl_sabqi)->format('d-m-Y') }}</td>
                            <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                            <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                            <td>{{ $history->nilai }}</td>
                            <td>{{ $history->ustadz?->user?->name ?? '-' }}</td>
                            <td>{{ $history->subKelas?->nama_sub_kelas ?? '-' }}</td>
                            <td>{{ $history->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Tidak ada history sabqi pada periode
                                yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($sabqiHistories->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Menampilkan
                        {{ $sabqiHistories->firstItem() }}
                        -
                        {{ $sabqiHistories->lastItem() }}
                        dari
                        {{ $sabqiHistories->total() }}
                        data
                    </small>
                </div>
                <div>
                    {{ $sabqiHistories->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

