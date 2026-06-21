<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MembersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithEvents
{
    protected $filters;
    protected $format;

    public function __construct($filters = [], $format = 'xlsx')
    {
        $this->filters = $filters;
        $this->format = $format;
    }

    public function query()
    {
        $query = Member::with(['departments']);

        // Apply filters
        if (!empty($this->filters['membership_status'])) {
            $query->where('membership_status', $this->filters['membership_status']);
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        if (!empty($this->filters['department'])) {
            $query->whereHas('departments', function($q) {
                $q->where('department', $this->filters['department']);
            });
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('first_name')->orderBy('last_name');
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

    public function map($member): array
    {
        return [
            $member->email,
            $member->last_name,
            $member->first_name,
            $member->other_names,
            $member->birth_day,
            $member->birth_month,
            $member->gender ? strtoupper($member->gender) : '',
            $member->emergency_contact_details,
            $member->marital_status ? strtoupper($member->marital_status) : '',
            $member->partner_name,
            $member->phone,
            $member->state_of_origin,
            $member->lga_of_origin,
            $member->state_of_residence,
            $member->city_of_residence,
            $member->address,
            $member->profession,
            $member->church_group,
            $member->departments->pluck('department')->join(', '),
            $member->is_baptized ? strtoupper($member->is_baptized) : '',
            $member->baptism_year_and_place,
            $member->baptism_church_name,
            $member->spiritual_gifts
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'] // Indigo color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
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
        return 'Church Members Export';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Add borders to all data
                $highestRow = $event->sheet->getHighestRow();
                $highestColumn = $event->sheet->getHighestColumn();
                
                $event->sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Alternate row colors
                for ($i = 2; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $event->sheet->getStyle('A' . $i . ':' . $highestColumn . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }
                }

                // Freeze the header row
                $event->sheet->freezePane('A2');
            },
        ];
    }
}
