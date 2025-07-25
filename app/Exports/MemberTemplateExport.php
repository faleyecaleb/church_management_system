<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class MemberTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        return [
            [
                'John',
                'Doe', 
                'john.doe@example.com',
                '+1234567890',
                '123 Main St, City, State',
                '1990-01-15',
                '2010-05-20',
                'active',
                'male',
                'choir,youth'
            ],
            [
                'Jane',
                'Smith',
                'jane.smith@example.com', 
                '+1234567891',
                '456 Oak Ave, City, State',
                '1985-03-22',
                '2008-12-10',
                'active',
                'female',
                'women_ministry'
            ],
            [
                'Michael',
                'Johnson',
                'michael.j@example.com',
                '+1234567892', 
                '789 Pine St, City, State',
                '1975-07-08',
                '2005-09-15',
                'active',
                'male',
                'men_ministry,ushering'
            ],
            [
                'Sarah',
                'Williams',
                'sarah.w@example.com',
                '+1234567893',
                '321 Elm Dr, City, State', 
                '1992-11-30',
                '2015-03-25',
                'active',
                'female',
                'choir,children_ministry'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'First Name *',
            'Last Name *', 
            'Email *',
            'Phone',
            'Address',
            'Date of Birth (YYYY-MM-DD)',
            'Baptism Date (YYYY-MM-DD)',
            'Membership Status (active/inactive/pending)',
            'Gender (male/female/other)',
            'Departments (comma-separated)'
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
            '2:5' => [
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
            'A' => 15, // First Name
            'B' => 15, // Last Name
            'C' => 25, // Email
            'D' => 15, // Phone
            'E' => 30, // Address
            'F' => 20, // Date of Birth
            'G' => 20, // Baptism Date
            'H' => 25, // Membership Status
            'I' => 15, // Gender
            'J' => 30, // Departments
        ];
    }

    public function title(): string
    {
        return 'Member Import Template';
    }
}