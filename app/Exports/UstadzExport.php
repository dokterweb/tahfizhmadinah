<?php

namespace App\Exports;

use App\Models\Ustadz;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UstadzExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        $ustadzs = Ustadz::with([
            'user',
            'subKelas'
        ])
        ->orderBy('id')
        ->get();

        $data = [];
        $no = 1;

        foreach ($ustadzs as $ustadz) {

            foreach ($ustadz->subKelas as $subKelas) {

                $data[] = [
                    'No' => $no++,

                    'ID' => $ustadz->id,

                    'Nama Ustadz/Ustadzah' =>
                        $ustadz->user?->name ?? '-',

                    'Sub Kelas yang Diajar' =>
                        $subKelas->nama_sub_kelas,

                    'Kelamin' =>
                        $ustadz->kelamin ?? '-',

                    'No. HP' =>
                        $ustadz->no_hp ?? '-',
                ];
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No',
            'ID',
            'Nama Ustadz/Ustadzah',
            'Sub Kelas yang Diajar',
            'Kelamin',
            'No. HP',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        /*
        |--------------------------------------------------------------------------
        | Border seluruh tabel
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle(
            'A1:F' . $sheet->getHighestRow()
        )
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(
            Border::BORDER_THIN
        );


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A1:F1')
            ->getFont()
            ->setBold(true);

        $sheet->getStyle('A1:F1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet->getStyle('A1:F1')
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );


        /*
        |--------------------------------------------------------------------------
        | Kolom No dan ID
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle(
            'A2:B' . $sheet->getHighestRow()
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );
    }
}