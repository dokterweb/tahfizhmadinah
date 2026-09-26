@extends('layouts.app')
@section('content_title','Dashboard')

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
        </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
            <h3 class="card-title">History Sabaq Terbaru</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                          <th>Tanggal</th>
                          <th>Surat</th>
                          <th>Ayat</th>
                          <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($sabaqs as $history)
                        <tr>
                          <td>{{ \Carbon\Carbon::parse($history->tgl_sabaq)->format('d/m/Y') }}</td>
                          <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                          <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                          <td>{{ $history->nilai }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-center text-muted">
                              Belum ada history Sabaq.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                </table>
              </div>
            </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
            <h3 class="card-title">History Sabqi Terbaru</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                          <th>Tanggal</th>
                          <th>Surat</th>
                          <th>Ayat</th>
                          <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($sabqis as $history)
                        <tr>
                          <td>{{ \Carbon\Carbon::parse($history->tgl_sabqi)->format('d/m/Y') }}</td>
                          <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                          <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                          <td>{{ $history->nilai }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-center text-muted">
                              Belum ada history Sabaq.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                </table>
              </div>
            </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
            <h3 class="card-title">History Manzil Terbaru</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                          <th>Tanggal</th>
                          <th>Surat</th>
                          <th>Ayat</th>
                          <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($manzils as $history)
                        <tr>
                          <td>{{ \Carbon\Carbon::parse($history->tgl_manzil)->format('d/m/Y') }}</td>
                          <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                          <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                          <td>{{ $history->nilai }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-center text-muted">
                            Belum ada history Sabaq.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                </table>
              </div>
            </div>
        </div>
      </div>
    </div>    
@endsection