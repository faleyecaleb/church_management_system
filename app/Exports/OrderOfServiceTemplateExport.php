<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderOfServiceTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'Opening Prayer',
                '09:00',
                '09:15',
                '15',
                '1',
                'Pastor John',
                'Opening prayers and declaration',
                'Read Psalm 91'
            ],
            [
                'Praise and Worship',
                '09:15',
                '09:45',
                '30',
                '2',
                'Choir',
                'High praise session',
                ''
            ],
            [
                'Sermon',
                '09:45',
                '10:45',
                '60',
                '3',
                'Guest Minister',
                'Topic: The Grace of God',
                ''
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Program',
            'Start Time',
            'End Time',
            'Duration Minutes',
            'Order',
            'Leader',
            'Description',
            'Notes'
        ];
    }
}