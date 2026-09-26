<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows->map(function ($row) {

            // Konversi tanggal lahir Excel ke format Y-m-d
            if (!empty($row['tgl_lahir'])) {

                try {

                    if (is_numeric($row['tgl_lahir'])) {

                        $row['tgl_lahir'] = Date::excelToDateTimeObject(
                            $row['tgl_lahir']
                        )->format('Y-m-d');

                    } else {

                        $row['tgl_lahir'] = date(
                            'Y-m-d',
                            strtotime($row['tgl_lahir'])
                        );

                    }

                } catch (\Throwable $e) {

                    // Biarkan nilai aslinya jika gagal dikonversi
                    $row['tgl_lahir'] = $row['tgl_lahir'];
                }
            }

            return $row;

        });
    }
}