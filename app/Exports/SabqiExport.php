<?php

namespace App\Exports;

use App\Models\Sabqi_history;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SabqiExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $start_date;
    protected $end_date;

    // Constructor untuk menerima start_date dan end_date
    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

        public function collection()
    {
        return Sabqi_history::whereBetween('tgl_sabqi', [
                $this->start_date,
                $this->end_date
            ])
            ->with([
                'surat',
                'siswa.user',
                'ustadz.user',
                'subKelas',
            ])
            ->orderBy('tgl_sabqi')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {

                return [
                    'Tanggal' => \Carbon\Carbon::parse(
                        $item->tgl_sabqi
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

    // Menentukan headings di Excel
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
        // Set border for the entire table
        $sheet->getStyle('A1:G' . $sheet->getHighestRow())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Make the headings bold
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        // Align text to center for headings
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    public function columnFormats(): array
    {
        // Format the date column (Tanggal) to d-M-Y
        return [
            'A' => 'dd-mmm-yy',
        ];
    }
}
