<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan manzil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .heading {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <h2 class="heading">Laporan Manzil</h2>
    <p>Tanggal: {{ \Carbon\Carbon::parse($start_date)->format('d F Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal manzil</th>
                <th>Nama Siswa</th>
                <th>Nama Surat</th>
                <th>Ayat</th>
                <th>Ustadz/Ustadzah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($manzils as $index => $history)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($history->tgl_manzil)->format('d M Y') }}</td>
                    <td>{{ $history->siswa->user->name }}</td>
                    <td>{{ $history->surat->sura_name }}</td>
                    <td>{{ $history->dariayat }} - {{ $history->sampaiayat }}</td>
                    <td>{{ $history->ustadz->user->name }}</td>
                    <td>{{ $history->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
