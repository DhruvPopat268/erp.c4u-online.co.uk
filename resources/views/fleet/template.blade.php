<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forward Planner</title>
    <style>
        @page {
            margin: 8mm 8mm;
            size: a4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table {
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0 4px;
        }
        .legend-table {
            width: 100%;
            margin-bottom: 8px;
            font-size: 7.5px;
        }
        .legend-table td {
            border: none;
            text-align: center;
            padding: 2px 0;
        }
        .legend-badge {
            display: inline-block;
            padding: 2px 5px;
            color: #ffffff;
            font-weight: bold;
            border-radius: 2px;
            margin-right: 6px;
        }
        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
            margin: 6px 0 4px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #e2e8f0;
        }
        .calendar-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 12px;
        }
        .calendar-table th, .calendar-table td {
            border: 1px solid #cbd5e1;
            text-align: center;
            padding: 2px 1px;
            font-size: 7.5px;
            word-wrap: break-word;
            overflow: hidden;
        }
        .calendar-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
        }
        .reg-col {
            width: 65px;
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 7.5px;
            text-align: left !important;
            padding-left: 3px !important;
        }
        .week-box {
            display: block;
            margin: 1px 0;
            padding: 1px;
            font-size: 6.5px;
            font-weight: bold;
            color: #ffffff;
            border-radius: 2px;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
        .reminder-summary-table {
            width: 100%;
            margin-top: 6px;
        }
        .reminder-summary-table th, .reminder-summary-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            font-size: 8px;
            text-align: left;
        }
        .reminder-summary-table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: left;">
                @if(!empty($img))
                    <img src="{{ $img }}" style="max-height: 40px; max-width: 150px;" alt="Logo" />
                @endif
            </td>
            <td style="width: 50%; text-align: center;">
                <div style="font-size: 13px; font-weight: bold; color: #0f172a;">Forward Planner</div>
                <div style="font-size: 10px; font-weight: bold; color: #334155; margin-top: 2px;">
                    @if (!empty($fromDate) && !empty($toDate))
                        {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                    @else
                        Year {{ $selectedYear }}
                    @endif
                </div>
                <div style="font-size: 9px; color: #64748b; margin-top: 1px;">
                    {{ strtoupper($companyName) }}
                </div>
            </td>
            <td style="width: 25%; text-align: right; font-size: 8px; color: #64748b;">
                Exported: {{ date('d/m/Y') }}
            </td>
        </tr>
    </table>

    <!-- Color Legend -->
    <table class="legend-table">
        <tr>
            <td>
                <span class="legend-badge" style="background-color: #05b794;">PMI</span>
                <span class="legend-badge" style="background-color: #b47bbf;">Brake Test</span>
                <span class="legend-badge" style="background-color: #10b981;">Tacho</span>
                <span class="legend-badge" style="background-color: #c0c102;">Insurance</span>
                <span class="legend-badge" style="background-color: #d59436;">Road Tax</span>
                <span class="legend-badge" style="background-color: #9e9ee5;">DVS/PSS</span>
                <span class="legend-badge" style="background-color: #6c757d;">MOT</span>
            </td>
        </tr>
    </table>

    <!-- Calendar Tables in Chunks -->
    @foreach($groupedDates as $chunkIndex => $dates)
        @if(count($dates) > 0)
            <div class="section-title">
                Schedule: Week {{ collect($dates)->first()->format('d/m/Y') }} &mdash; {{ collect($dates)->last()->copy()->endOfWeek()->format('d/m/Y') }}
            </div>

            <table class="calendar-table">
                <thead>
                    <tr>
                        <th class="reg-col">Reg No.</th>
                        @foreach($dates as $date)
                            <th>{{ $date->format('d/m') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedFleets as $vehicle_id => $fleets)
                        @php
                            $firstFleet = collect($fleets)->first();
                            $vehicle = $firstFleet ? $firstFleet->vehicle : null;
                            $regNo = $vehicle ? ($vehicle->vehicle_type == 'Trailer' ? ($vehicle->vehicle_nick_name ?: $vehicle->registrationNumber) : $vehicle->registrationNumber) : 'N/A';
                        @endphp
                        <tr>
                            <td class="reg-col">{{ $regNo }}</td>
                            @foreach($dates as $date)
                                <td>
                                    @php
                                        $weekStart = $date->copy();
                                        $weekEnd = $date->copy()->endOfWeek();

                                        $matchingReminders = collect($fleets)->pluck('reminders')->flatten()->filter(function ($reminder) use ($weekStart, $weekEnd) {
                                            if (empty($reminder->next_reminder_date)) {
                                                return false;
                                            }
                                            return \Carbon\Carbon::parse($reminder->next_reminder_date)->between($weekStart, $weekEnd, true);
                                        });
                                    @endphp

                                    @if($matchingReminders->isNotEmpty())
                                        @foreach($matchingReminders as $reminder)
                                            @php
                                                $plannerType = $reminder->fleet ? $reminder->fleet->planner_type : '';
                                                $bgColor = '#3788d8';
                                                $shortTitle = $plannerType;

                                                switch ($plannerType) {
                                                    case 'PMI Due':
                                                        $bgColor = '#05b794';
                                                        $shortTitle = 'PMI';
                                                        break;
                                                    case 'Brake Test Due':
                                                        $bgColor = '#b47bbf';
                                                        $shortTitle = 'BT';
                                                        break;
                                                    case 'Tacho Calibration':
                                                        $bgColor = '#10b981';
                                                        $shortTitle = 'TACHO';
                                                        break;
                                                    case 'Insurance':
                                                        $bgColor = '#c0c102';
                                                        $shortTitle = 'INS';
                                                        break;
                                                    case 'Road Tax':
                                                        $bgColor = '#d59436';
                                                        $shortTitle = 'TAX';
                                                        break;
                                                    case 'DVS/PSS Permit Expiry':
                                                        $bgColor = '#9e9ee5';
                                                        $shortTitle = 'DVS';
                                                        break;
                                                    case 'MOT':
                                                        $bgColor = '#6c757d';
                                                        $shortTitle = 'MOT';
                                                        break;
                                                }
                                            @endphp
                                            <div class="week-box" style="background-color: {{ $bgColor }};">
                                                {{ $shortTitle }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span style="color: #cbd5e1;">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endif
    @endforeach

    <!-- Reminder Summary Section -->
    @php
        $allReminders = collect();
        foreach ($groupedFleets as $vehicle_id => $fleets) {
            foreach ($fleets as $fleet) {
                if ($fleet->reminders) {
                    foreach ($fleet->reminders as $rem) {
                        $allReminders->push($rem);
                    }
                }
            }
        }
        $sortedReminders = $allReminders->sortBy('next_reminder_date');
    @endphp

    @if($sortedReminders->isNotEmpty())
        <div class="page-break"></div>
        <div class="section-title" style="font-size: 11px;">
            Reminder Summary Schedule
        </div>

        <table class="reminder-summary-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Vehicle Reg</th>
                    <th style="width: 25%;">Planner Type</th>
                    <th style="width: 20%;">Reminder Date</th>
                    <th style="width: 20%;">Status</th>
                    <th style="width: 20%;">Next Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sortedReminders as $reminder)
                    @php
                        $veh = $reminder->fleet && $reminder->fleet->vehicle ? $reminder->fleet->vehicle : null;
                        $rReg = $veh ? ($veh->vehicle_type == 'Trailer' ? ($veh->vehicle_nick_name ?: $veh->registrationNumber) : $veh->registrationNumber) : 'N/A';
                    @endphp
                    <tr>
                        <td><b>{{ $rReg }}</b></td>
                        <td>{{ $reminder->fleet ? $reminder->fleet->planner_type : '-' }}</td>
                        <td>{{ !empty($reminder->next_reminder_date) ? \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($reminder->status === 'Completed')
                                <span style="color: #16a34a; font-weight: bold;">Completed</span>
                            @elseif($reminder->status === 'Pending')
                                <span style="color: #dc2626; font-weight: bold;">Pending</span>
                            @else
                                {{ $reminder->status }}
                            @endif
                        </td>
                        <td>{{ $reminder->vehicle_status ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
