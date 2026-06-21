<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MemberTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $isYouthChurch;

    public function __construct()
    {
        $this->isYouthChurch = auth()->check() && auth()->user()->church && auth()->user()->church->type === 'youth';
    }

    public function array(): array
    {
        return [
            [
                'john.doe@example.com',
                'Doe',
                'John',
                'David',
                '15',
                'January',
                'MALE',
                'Jane Doe: 09012345678',
                'MARRIED',
                'Jane Doe',
                '08012345678',
                'Lagos',
                'Ikeja',
                'Lagos',
                'Ikeja',
                '123 Main St, Ikeja',
                $this->isYouthChurch ? 'Student' : 'Software Engineer',
                'Men Fellowship',
                'CHOIR',
                'YES',
                '2010 - Lagos',
                'RCCG',
                'Teaching, Healing'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'EMAIL',
            'SURNAME',
            'FIRSTNAME',
            'OTHER NAME',
            'DAY OF BIRTH',
            'MONTH OF BIRTH',
            'GENDER',
            'EMERGENCY CONTACT NAME & PHONE NUMBER',
            'MARITAL STATUS',
            'NAME OF PARTNER (if married)',
            'PHONE NUMBER (primary)',
            'STATE OF ORIGIN',
            'LOCAL GOVERNMENT',
            'STATE OF RESIDENCE',
            'CITY OF RESIDENCE',
            'STREET NAME & NUMBER',
            'PROFESSION/OCCUPATION',
            'GROUP IN CHURCH',
            'DEPARTMENT IN CHURCH',
            'BAPTIZED',
            'LOCATION & YEAR OF BAPTISM',
            'CHURCH OF BAPTISM',
            'SPIRITUAL GIFTS'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'] // Indigo color
                ]
            ],
            // Style data rows with alternating colors
            '2:2' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'] // Light gray
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // EMAIL
            'B' => 20, // SURNAME
            'C' => 20, // FIRSTNAME
            'D' => 20, // OTHER NAME
            'E' => 15, // DAY OF BIRTH
            'F' => 15, // MONTH OF BIRTH
            'G' => 15, // GENDER
            'H' => 40, // EMERGENCY CONTACT NAME & PHONE NUMBER
            'I' => 20, // MARITAL STATUS
            'J' => 25, // NAME OF PARTNER (if married)
            'K' => 20, // PHONE NUMBER (primary)
            'L' => 20, // STATE OF ORIGIN
            'M' => 25, // LOCAL GOVERNMENT
            'N' => 20, // STATE OF RESIDENCE
            'O' => 20, // CITY OF RESIDENCE
            'P' => 35, // STREET NAME & NUMBER
            'Q' => 25, // PROFESSION/OCCUPATION
            'R' => 20, // GROUP IN CHURCH
            'S' => 25, // DEPARTMENT IN CHURCH
            'T' => 20, // BAPTIZED
            'U' => 25, // LOCATION & YEAR OF BAPTISM
            'V' => 25, // CHURCH OF BAPTISM
            'W' => 30, // SPIRITUAL GIFTS
        ];
    }

    public function title(): string
    {
        return $this->isYouthChurch ? 'Youth Member Import Template' : 'Adult Member Import Template';
    }
}
