<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceAnalyticsExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $mostRegular;
    protected $mostPunctual;
    protected $startDate;
    protected $endDate;

    public function __construct($mostRegular, $mostPunctual, $startDate, $endDate)
    {
        $this->mostRegular = $mostRegular;
        $this->mostPunctual = $mostPunctual;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('attendance.analytics.export', [
            'mostRegular' => $this->mostRegular,
            'mostPunctual' => $this->mostPunctual,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'size' => 14]],
            // We'll rely on HTML styling mapped to Excel via Maatwebsite
        ];
    }
}