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
            'ID',
            'First Name',
            'Last Name',
            'Full Name',
            'Email',
            'Phone',
            'Address',
            'Date of Birth',
            'Age',
            'Baptism Date',
            'Membership Status',
            'Gender',
            'Departments',
            'Member Since',
            'Last Updated'
        ];
    }

    public function map($member): array
    {
        return [
            $member->id,
            $member->first_name,
            $member->last_name,
            $member->full_name,
            $member->email,
            $member->phone,
            $member->address,
            $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '',
            $member->date_of_birth ? $member->date_of_birth->age : '',
            $member->baptism_date ? $member->baptism_date->format('Y-m-d') : '',
            ucfirst($member->membership_status),
            ucfirst($member->gender ?? ''),
            $member->departments->pluck('department')->join(', '),
            $member->created_at->format('Y-m-d'),
            $member->updated_at->format('Y-m-d H:i:s')
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
            'A' => 8,  // ID
            'B' => 15, // First Name
            'C' => 15, // Last Name
            'D' => 25, // Full Name
            'E' => 30, // Email
            'F' => 15, // Phone
            'G' => 35, // Address
            'H' => 15, // Date of Birth
            'I' => 8,  // Age
            'J' => 15, // Baptism Date
            'K' => 18, // Membership Status
            'L' => 10, // Gender
            'M' => 30, // Departments
            'N' => 15, // Member Since
            'O' => 20, // Last Updated
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