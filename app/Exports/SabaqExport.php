<?php

namespace App\Exports;

use App\Models\Sabaq_history;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SabaqExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        return Sabaq_history::whereBetween('tgl_sabaq', [
                $this->start_date,
                $this->end_date
            ])
            ->with([
                'surat',
                'siswa.user',
                'ustadz.user',
                'subKelas',
            ])
            ->orderBy('tgl_sabaq')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {

                return [
                    'Tanggal' => \Carbon\Carbon::parse(
                        $item->tgl_sabaq
                    )->format('d-M-Y'),

                    'Nama Siswa' => $item->siswa?->user?->name ?? '-',

                    'Nama Surat' => $item->surat?->sura_name ?? '-',

                    'Dari Ayat' => $item->dariayat,

                    'Sampai Ayat' => $item->sampaiayat,

                    'Ustadz Ustadzah' => $item->ustadz?->user?->name ?? '-',

                    'Keterangan' => $item->keterangan ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Siswa',
            'Nama Surat',
            'Dari Ayat',
            'Sampai Ayat',
            'Ustadz Ustadzah',
            'Keterangan',
        ];
    }

    public function styles($sheet)
    {
        // Border seluruh tabel
        $sheet->getStyle(
            'A1:G' . $sheet->getHighestRow()
        )->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Header bold
        $sheet->getStyle('A1:G1')
            ->getFont()
            ->setBold(true);

        // Header center
        $sheet->getStyle('A1:G1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        // Auto size
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)
                ->setAutoSize(true);
        }
    }

    public function columnFormats(): array
    {
        return [
            'A' => 'dd-mmm-yy',
        ];
    }
}