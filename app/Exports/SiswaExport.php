<?php

namespace App\Exports;

use App\Models\Siswa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SiswaExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        return Siswa::with([
            'user',
            'ustadz.user',
            'subKelas',
        ])
        ->orderBy('id')
        ->get()
        ->map(function ($siswa) {

            return [
                'id'            => $siswa->id,
                'nama_siswa'    => $siswa->user?->name ?? '-',
                'nama_ustadz'   => $siswa->ustadz?->user?->name ?? '-',
                'nama_sub_kelas'=> $siswa->subKelas?->nama_sub_kelas ?? '-',
                'kelamin'       => $siswa->kelamin ?? '-',
                'tempat_lahir'  => $siswa->tempat_lahir ?? '-',
                'tgl_lahir'     => $siswa->tgl_lahir
                    ? Carbon::parse($siswa->tgl_lahir)->format('d-m-Y')
                    : '-',
                'alamat'        => $siswa->alamat ?? '-',
                'no_hp'         => $siswa->no_hp ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Siswa',
            'Nama Ustadz',
            'Nama Sub Kelas',
            'Kelamin',
            'Tempat Lahir',
            'Tgl Lahir',
            'Alamat',
            'No HP',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Border seluruh tabel
        $sheet->getStyle(
            'A1:I' . $sheet->getHighestRow()
        )
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

        // Header bold
        $sheet->getStyle('A1:I1')
            ->getFont()
            ->setBold(true);

        // Header rata tengah
        $sheet->getStyle('A1:I1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet->getStyle('A1:I1')
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        // ID rata tengah
        $sheet->getStyle(
            'A2:A' . $sheet->getHighestRow()
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );

        // Kelamin rata tengah
        $sheet->getStyle(
            'E2:E' . $sheet->getHighestRow()
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );

        // Tanggal rata tengah
        $sheet->getStyle(
            'G2:G' . $sheet->getHighestRow()
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );
    }
}