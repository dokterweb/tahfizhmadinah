<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Cetak</title>
    <!-- <link rel="stylesheet" href="style.css" media="all" /> -->
    <link rel="stylesheet" href="{{asset('adminlte')}}/dist/css/invoice.css">
</head>

<body>
    <header class="clearfix">

        <table width="100%">
            <tr>
                <td class=sekolah width="90px" align="left"></td>
                <td class=sekolah align="center">
                    <h3 style="margin:0px; font-size: 150%; text-align: center;">
                        {{$profile->nama_sekolah}}
                    </h3>
                    <small style="font-size: 100%; text-align: center;">{{$profile->alamat}}</small><br>
                    <small style="font-size: 100%; text-align: center;">{{$profile->phone}}</small>
                </td>
                <td class=sekolah width="90px" align="right"></td>
            </tr>
        </table>
        <hr>
        <h3 style="font-size: 120%; text-align: center;"></h3>
        <hr>
        <table>
            <tr>
                <td width="100px" class="profile">Nama</td>
                <td width="2px">:</td>
                <td class="profile">{{ $siswa->user->name }}<br></td>
            </tr>
            <tr>
                <td width="100px" class="profile">Kelas</td>
                <td width="2px">:</td>
                <td class="profile">{{ $siswa->kelasnya->nama_kelas }}<br></td>
            </tr>


        </table>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>BULAN</th>
                    <th>TANGGAL BAYAR</th>
                    <th>TAGIHAN</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="rinci">{{ $bulanan->bulan->nama_bulan }}</td>
                    <td class="rinci"> {{ $bulanan->bulan_date_pay 
                        ? \Carbon\Carbon::parse($bulanan->bulan_date_pay)->format('d M Y') 
                        : '-' }}</td>
                    <td class="rinci">{{ number_format($bulanan->bulan_bill, 0, ',', '.') }}</td>
                    <td class="rinci">{{ $bulanan->bulan_status == 1 ? 'Lunas' : 'Belum Bayar' }}</td>
                </tr>

            </tbody>
        </table>
        <table width="100%">
            <tr>
                <td class="profile" valign="top">
                    <b>Terbilang :</b><br>
                    <em>{{ ucwords(\App\Helpers\Terbilang::make($bulanan->bulan_number_pay)) }} rupiah</em>
                </td>
                <td class="sekolah" align="center"></td>
                <td class="sekolah" align="center" width="200px">
                    Medan, {{ now()->format('d M Y') }} <br />Bendahara,<br /><br /><br /><br />
                    <b><u>NAMA BENDAHARA</u><br />-</b>
                </td>
            </tr>
        </table>
    </main>
    <footer>
        Invoice was created on a computer and is valid without the signature and seal.
    </footer>
</body>

</html>