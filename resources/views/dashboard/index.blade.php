@extends('layouts.app')
@section('content_title','Dashboard')

@section('content')
    <div class="card">
        <div class="card-body">
            Welcome to Tahfizh <strong class="capitilize">{{auth()->user()->name}}</strong>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-6">
          <!-- small card -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$totalSiswa}}</h3>
    
              <p>Total Siswa</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{route('siswas.index')}}" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-4 col-6">
          <!-- small card -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{$totalUstadz}}</h3>
    
              <p>Total Ustadz</p>
            </div>
            <div class="icon">
                <i class="fas fa-house-user"></i>
            </div>
            <a href="{{route('ustadzs.index')}}" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-4 col-6">
          <!-- small card -->
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{$totalKelas}}</h3>
              
              <p>Total Kelas</p>
            </div>
            <div class="icon">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <a href="{{route('kelasnyas')}}" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <!-- ./col -->
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
            <h3 class="card-title">History Sabaq Terbaru</h3>
            </div>
            <div class="card-body table-responsive p-2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                          <th>Siswa</th>
                          <th>Tanggal</th>
                          <th>Surat</th>
                          <th>Ayat</th>
                          <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($sabaqs as $history)
                        <tr>
                          <td>{{ $history->siswa?->user?->name ?? '-' }}</td>
                          <td>{{ \Carbon\Carbon::parse($history->tgl_sabaq)->format('d/m/Y') }}</td>
                          <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                          <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                          <td>{{ $history->nilai }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center text-muted">
                              Belum ada history Sabaq.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                </table>
            </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
            <h3 class="card-title">History Sabqi Terbaru</h3>
            </div>
            <div class="card-body table-responsive p-2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                          <th>Siswa</th>
                          <th>Tanggal</th>
                          <th>Surat</th>
                          <th>Ayat</th>
                          <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($sabqis as $history)
                        <tr>
                          <td>{{ $history->siswa?->user?->name ?? '-' }}</td>
                          <td>{{ \Carbon\Carbon::parse($history->tgl_sabqi)->format('d/m/Y') }}</td>
                          <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                          <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                          <td>{{ $history->nilai }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center text-muted">
                              Belum ada history Sabaq.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                </table>
            </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
            <h3 class="card-title">History Manzil Terbaru</h3>
            </div>
            <div class="card-body table-responsive p-2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                          <th>Siswa</th>
                          <th>Tanggal</th>
                          <th>Surat</th>
                          <th>Ayat</th>
                          <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse($manzils as $history)
                        <tr>
                          <td>{{ $history->siswa?->user?->name ?? '-' }}</td>
                          <td>{{ \Carbon\Carbon::parse($history->tgl_manzil)->format('d/m/Y') }}</td>
                          <td>{{ $history->surat?->sura_name ?? '-' }}</td>
                          <td>{{ $history->dariayat }}-{{ $history->sampaiayat }}</td>
                          <td>{{ $history->nilai }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center text-muted">
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
@endsection