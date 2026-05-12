<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="4" style="font-size: 16px; font-weight: bold; text-align: center;">
                    Attendance Analytics Report ({{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }})
                </th>
            </tr>
            <tr>
                <th></th>
            </tr>
            <tr>
                <th colspan="2" style="font-size: 14px; font-weight: bold; background-color: #4f46e5; color: #ffffff;">Most Regular Members</th>
                <th colspan="2" style="font-size: 14px; font-weight: bold; background-color: #10b981; color: #ffffff;">Most Punctual Members</th>
            </tr>
            <tr>
                <th style="font-weight: bold; border-bottom: 1px solid #000000;">Member Name</th>
                <th style="font-weight: bold; border-bottom: 1px solid #000000;">Total Services Attended</th>
                <th style="font-weight: bold; border-bottom: 1px solid #000000;">Member Name</th>
                <th style="font-weight: bold; border-bottom: 1px solid #000000;">Average Punctuality</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maxRows = max($mostRegular->count(), $mostPunctual->count());      
            @endphp

            @for($i = 0; $i < $maxRows; $i++)
                <tr>
                    {{-- Regular --}}
                    @if(isset($mostRegular[$i]))
                        <td>{{ $mostRegular[$i]->first_name }} {{ $mostRegular[$i]->last_name }}</td>
                        <td>{{ $mostRegular[$i]->attendance_count }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif

                    {{-- Punctual --}}
                    @if(isset($mostPunctual[$i]))
                        <td>{{ $mostPunctual[$i]->first_name }} {{ $mostPunctual[$i]->last_name }}</td>
                        @if($mostPunctual[$i]->avg_minutes_late < 0)
                            <td>{{ abs(round($mostPunctual[$i]->avg_minutes_late)) }} mins early</td>
                        @elseif($mostPunctual[$i]->avg_minutes_late == 0)
                            <td>Exact Time</td>
                        @else
                            <td>{{ round($mostPunctual[$i]->avg_minutes_late) }} mins late</td>
                        @endif
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>