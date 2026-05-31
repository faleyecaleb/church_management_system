<?php

namespace App\Imports;

use App\Models\OrderOfService;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class OrderOfServiceImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $serviceId;
    protected $churchId;
    protected $currentOrder;

    public function __construct($serviceId, $churchId)
    {
        $this->serviceId = $serviceId;
        $this->churchId = $churchId;
        
        // Find the current max order for this service to append correctly
        $this->currentOrder = OrderOfService::where('service_id', $serviceId)->max('order') ?? 0;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->currentOrder++;

        // Handle time parsing carefully. Excel might format it weirdly.
        $startTime = $this->parseTime($row['start_time'] ?? null);
        $endTime = $this->parseTime($row['end_time'] ?? null);

        return new OrderOfService([
            'service_id'       => $this->serviceId,
            'church_id'        => $this->churchId,
            'program'          => $row['program'],
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'order'            => $row['order'] ?? $this->currentOrder,
            'duration_minutes' => $row['duration_minutes'] ?? null,
            'leader'           => $row['leader'] ?? null,
            'description'      => $row['description'] ?? null,
            'notes'            => $row['notes'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'program' => 'required|string|max:255',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'order' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'leader' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];
    }
    
    private function parseTime($timeString)
    {
        if (empty($timeString)) {
            return null;
        }

        try {
            // Check if it's an Excel fraction of a day (e.g., 0.5 for 12:00 PM)
            if (is_numeric($timeString)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($timeString)->format('H:i');
            }
            
            // Otherwise, attempt standard carbon parsing
            return Carbon::parse($timeString)->format('H:i');
        } catch (\Exception $e) {
            return null; // Return null if it fails to parse
        }
    }
}