<?php

namespace App\Exports;

use App\Models\SubKelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SubKelasExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        return SubKelas::with('kelasnya')
            ->orderBy('kelas_id')
            ->orderBy('nama_sub_kelas')
            ->get()
            ->map(function ($item, $index) {

                return [
                    'No'        => $index + 1,
                    'Id'        => $item->id,
                    'Kelas'     => $item->kelasnya?->nama_kelas ?? '-',
                    'Sub Kelas' => $item->nama_sub_kelas,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Id',
            'Kelas',
            'Sub Kelas',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Border seluruh tabel
        $sheet->getStyle(
            'A1:D' . $sheet->getHighestRow()
        )
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

        // Header bold
        $sheet->getStyle('A1:D1')
            ->getFont()
            ->setBold(true);

        // Header center
        $sheet->getStyle('A1:D1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        // Kolom No center
        $sheet->getStyle(
            'A2:A' . $sheet->getHighestRow()
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );
    }
}