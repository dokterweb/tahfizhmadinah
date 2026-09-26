@extends('layouts.app')
@section('content_title','Bayar Bulanan')

@section('content')

<div class="card card-danger">
    <div class="card-header">
      <h3 class="card-title">List dan Pemabayaran</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-4">Nama Siswa</dt>
                    <dd class="col-sm-8">{{ $siswa->user->name }}</dd>
                    <dt class="col-sm-4">Kelas</dt>
                    <dd class="col-sm-8">{{ $siswa->kelasnya->nama_kelas }}</dd>
                    <dt class="col-sm-4">Tanggal Lahir</dt>
                    <dd class="col-sm-8">{{ $siswa->tgl_lahir }}</dd>
                    <dt class="col-sm-4">Total Tagihan</dt>
                    <dd class="col-sm-8">{{ number_format($totBill) }}</dd>
                    <dt class="col-sm-4">Sisa Tagihan</dt>
                    <dd class="col-sm-8">{{ number_format($telahDibayar) }}</dd>
                </dl>
            </div>
            <div class="col-md-6">
                @if ($siswa->user->avatar =='')
                <img src="{{ asset('storage/default.png' ) }}" alt="Siswa" width="200" class="rounded mx-auto d-block">
                @else
                <img src="{{ asset('storage/' . $siswa->user->avatar) }}" alt="Siswa" width="200" class="rounded mx-auto d-block">
                @endif
            </div>
        </div>

        @if(count($bulanans) > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bulan</th>
                        <th>Tagihan</th>
                        <th>Status</th>
                        <th>Bill Amount</th>
                        <th>Date</th>
                        <th>Bukti</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulanans as $b)
                        <tr>
                            @if ($b->bulan_status == '1')
                                <td>{{ $b->id }}</td>
                                <td>{{ $b->bulan->nama_bulan }}</td>
                                <td>{{ $b->bulan_bill }}</td>
                                <td><span class="badge badge-success">Lunas</span></td>
                                <td>{{ $b->bulan_number_pay }}</td>
                                <td>{{ $b->bulan_date_pay }}</td>
                                <td>
                                @if ($b->bukti_bulan)
                                    <a href="{{ asset('storage/' . $b->bukti_bulan) }}" target="_blank">Bukti</a> 
                                @endif
                                </td>
                                <td>
                                    <a href="{{ route('payouts.invoice', [$b->payment_id, $b->bulan_id]) }}" target="_blank">Bukti</a>


                                    {{-- untuk cetak pdf ,mengirimkan bulan_id dan siswa_id--}}
                                </td>
                            @else
                                <form action="{{ route('payouts.updatebulanans') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $b->id }}">
                                    <td>{{ $b->id }}</td>
                                    <td>{{ $b->bulan->nama_bulan }}</td>
                                    <td>{{ $b->bulan_bill }}</td>
                                    <td><span class="badge badge-danger">Belum Bayar</span></td>
                                    <td><input type="number" name="bulan_number_pay" class="form-control" value="{{ $b->bulan_number_pay }}"></td>
                                    <td><input type="date" name="bulan_date_pay" class="form-control" value="{{ $b->bulan_date_pay }}"></td>
                                    <td><input type="file" id="bukti_bulan" name="bukti_bulan" accept=".jpg,.jpeg,.png" class="form-control"></td>
                                    <td><button type="submit" class="btn btn-primary btn-sm">Bayar</button></td>
                                </form>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                
            </table>
        @else
            <p>Data tidak ditemukan.</p>
        @endif
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->

@endsection