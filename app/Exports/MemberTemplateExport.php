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
                'Software Engineer',
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
            'Email *',
            'LASTNAME/SURNAME *',
            'FIRSTNAME *',
            'OTHERS',
            'DAY OF BIRTH *',
            'MONTH OF BIRTH *',
            'GENDER *',
            'EMERGENCY CONTACT NAME & PHONE NUMBER',
            'MARITAL STATUS *',
            'NAME OF PARTNER (If married)',
            'PHONE NUMBER (primary) *',
            'STATE OF ORIGIN *',
            'LOCAL GOVERNMENT OF ORIGIN *',
            'STATE OF RESIDENCE *',
            'CITY OF RESIDENCE *',
            'STREET NO AND NAME (eg: 2, Korogboji) *',
            'PROFESSION/OCCUPATION *',
            'GROUP IN CHURCH',
            'DEPARTMENT IN CHURCH *',
            'ARE YOU BAPTIZED ? *',
            'WHAT YEAR AND WHERE ?',
            'NAME OF THE CHURCH',
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
            'A' => 25, // Email
            'B' => 20, // Last Name
            'C' => 20, // First Name
            'D' => 20, // Others
            'E' => 15, // Day of Birth
            'F' => 15, // Month of Birth
            'G' => 15, // Gender
            'H' => 40, // Emergency Contact
            'I' => 20, // Marital Status
            'J' => 25, // Name of Partner
            'K' => 20, // Phone
            'L' => 20, // State of Origin
            'M' => 25, // LGA
            'N' => 20, // State of Res
            'O' => 20, // City of Res
            'P' => 35, // Street No
            'Q' => 25, // Profession
            'R' => 20, // Group in Church
            'S' => 25, // Department
            'T' => 20, // Baptized
            'U' => 25, // Year & Where
            'V' => 25, // Name of Church
            'W' => 30, // Spiritual Gifts
        ];
    }

    public function title(): string
    {
        return 'Adult Member Import Template';
    }
}