<!DOCTYPE html>
<html>
<head>
        <title>Forward Planner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20mm 15mm;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .calendar-table th, .calendar-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 5px;
            font-size: 9px;
        }
        .calendar-table th {
            background-color: #f4f4f4;
        }
        h4 {
            text-align: left;
            margin-bottom: 10px;
        }
        .week-box {
            display: inline-block;
            padding: 3px;
            background-color: #e6e6e6;
            margin-top: 2px;
            margin-bottom: 2px;
            font-size: 8px;
            border-radius: 3px;
        }

        /* Color key on top right */
        .color-key {
            position: absolute;
            top: 20mm;
            right: 15mm;
            white-space: nowrap;
        }
        .color-key div {
            display: inline-block;
            margin-right: 10px;
        }
        .color-box {
            width: 10px;
            height: 10px;
            display: inline-block;
            margin-right: 5px;
        }

        /* New Reminder section styling */
        .reminder-list-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .reminder-list {
            font-size: 9px;
            margin-top: 20px;
            page-break-before: always;
        }
        .header {
            text-align: left;
            position: fixed;
            top: 5px;
            left: 15mm;
            right: 15mm;
            width: auto;
            background-color: transparent;
            border-bottom: 1px solid #ddd;
            z-index: 1000;
            margin-top: -2%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1px;
        }

        .header-logo {
            display: flex;
            align-items: center;
        }

        .header-info {
            flex-grow: 1;
            text-align: right;
            margin-top: -30px;
            margin-bottom: -5px;
        }

        .h6.text-sm {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            font-weight: bold;
            left: 1000px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-logo">
            <img src="{{ $img }}" style="max-width: 100px;" alt="Logo" />
        </div>
        <div class="header-info">
            <div class="h6 text-sm">
                <span class="text-sm" style="font-family: Arial, Helvetica, sans-serif;">
                    <b>
                        Forward Planner -
                        @if (!empty($fromDate) && !empty($toDate))
                            {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                        @else
                            {{ $selectedYear }}
                        @endif
                    </b>
                </span>
            </div>
            <div class="h6 text-sm" style="margin-bottom: 25px;">
                <span class="text-sm" style="font-family: Arial, Helvetica, sans-serif;">
                    <b>{{ $companyName }}</b>
                </span>
            </div>
        </div>
    </div>

    <!-- Color key at the top right in a single row -->
    <div class="color-key">
        <div><span class="color-box" style="background-color: #67a8e9;"></span> PMI Due</div>
        <div><span class="color-box" style="background-color: #b47bbf;"></span> Brake Test Due</div>
        <div><span class="color-box" style="background-color: green;"></span> Tacho Calibration</div>
        <div><span class="color-box" style="background-color: #c0c102;"></span> Insurance</div>
        <div><span class="color-box" style="background-color: #d59436;"></span> Road Tax</div>
        <div><span class="color-box" style="background-color: #9e9ee5;"></span> DVS/PSS Permit Expiry</div>
        <div><span class="color-box" style="background-color: #6c757d;"></span> MOT</div>
    </div>

    @php
        $counter = 1;
    @endphp

    <!-- Calendar Tables -->
    @foreach($groupedDates as $chunkIndex => $chunk)
        @php $dates = $chunk['dates'] ?? $chunk; @endphp
        @if(count($dates) > 0)
            <h4>Schedule: {{ collect($dates)->first()->format('d M Y') }} &ndash; {{ collect($dates)->last()->copy()->endOfWeek()->format('d M Y') }}</h4>
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Vehicle ID</th>
                        @foreach($dates as $date)
                            <th>{{ $date->format('d/m/Y') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedFleets as $vehicle_id => $fleets)
                        @php
                            $firstFleet = collect($fleets)->first();
                            $vehicle = $firstFleet ? $firstFleet->vehicle : null;
                            $regNo = $vehicle ? ($vehicle->vehicle_nick_name ?? '') : 'N/A';
                        @endphp
                        <tr>
                            <td>{{ $regNo }}</td>
                            @foreach($dates as $date)
                                <td>
                                    @php
                                        $weekStartDate = $date->copy()->startOfWeek();
                                        $weekEndDate = $date->copy()->endOfWeek();

                                        $matchingReminders = collect($fleets)->pluck('reminders')->flatten()->filter(function ($reminder) use ($weekStartDate, $weekEndDate) {
                                            if (empty($reminder->next_reminder_date)) {
                                                return false;
                                            }
                                            return \Carbon\Carbon::parse($reminder->next_reminder_date)->between($weekStartDate, $weekEndDate, true);
                                        });
                                    @endphp

                                    @if($matchingReminders->isNotEmpty())
                                        @foreach($matchingReminders as $reminder)
                                            <div class="week-box"
                                                @if($reminder->fleet->planner_type == 'PMI Due')
                                                    style="background-color: #67a8e9;"
                                                @elseif($reminder->fleet->planner_type == 'Brake Test Due')
                                                    style="background-color: #b47bbf;"
                                                @elseif($reminder->fleet->planner_type == 'Tacho Calibration')
                                                    style="background-color: green; color:white;"
                                                @elseif($reminder->fleet->planner_type == 'Insurance')
                                                    style="background-color: #c0c102;"
                                                @elseif($reminder->fleet->planner_type == 'Road Tax')
                                                    style="background-color: #d59436;"
                                                @elseif($reminder->fleet->planner_type == 'DVS/PSS Permit Expiry')
                                                    style="background-color: #9e9ee5;"
                                                @else
                                                    style="background-color: #6c757d; color:white;"
                                                @endif
                                            >
                                                @if($reminder->fleet->planner_type == 'PMI Due')
                                                    PMI
                                                @elseif($reminder->fleet->planner_type == 'Brake Test Due')
                                                    BT
                                                @elseif($reminder->fleet->planner_type == 'Tacho Calibration')
                                                    TACHO
                                                @elseif($reminder->fleet->planner_type == 'Insurance')
                                                    INS
                                                @elseif($reminder->fleet->planner_type == 'Road Tax')
                                                    RT
                                                @elseif($reminder->fleet->planner_type == 'DVS/PSS Permit Expiry')
                                                    DVS/PSS
                                                @else
                                                    {{ $reminder->fleet->planner_type }}
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <!-- Reminder Summary -->
    @php
        $allReminders = collect();
        foreach ($groupedFleets as $vehicle_id => $fleets) {
            foreach ($fleets as $fleet) {
                foreach ($fleet->reminders as $reminder) {
                    $allReminders->push($reminder);
                }
            }
        }
        $remindersByPlannerType = $allReminders->groupBy('fleet.planner_type');
    @endphp

    <div class="reminder-list-container">
        @foreach($remindersByPlannerType as $plannerType => $reminders)
            <div class="reminder-list">
                <h1 style="
                    @if($plannerType == 'PMI Due') color: #67a8e9;
                    @elseif($plannerType == 'Brake Test Due') color: #b47bbf;
                    @elseif($plannerType == 'Tacho Calibration') color: green;
                    @elseif($plannerType == 'Insurance') color: #c0c102;
                    @elseif($plannerType == 'Road Tax') color: #d59436;
                    @elseif($plannerType == 'DVS/PSS Permit Expiry') color: #9e9ee5;
                    @elseif($plannerType == 'MOT') color: #6c757d;
                    @else color: #000;
                    @endif">
                    {{ $plannerType }} Reminders
                </h1>
                <table class="calendar-table">
                    <thead>
                        <tr>
                            <th>Planner Type</th>
                            <th>Vehicle ID</th>
                            <th>Reminder Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reminders as $reminder)
                            @php
                                $veh = $reminder->fleet && $reminder->fleet->vehicle ? $reminder->fleet->vehicle : null;
                                $rReg = $veh ? ($veh->vehicle_nick_name ?? '') : 'N/A';
                            @endphp
                            <tr>
                                <td>{{ $reminder->fleet->planner_type }}</td>
                                <td>{{ $rReg }}</td>
                                <td>{{ \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d/m/Y') }}</td>
                                <td>
                                    @if($reminder->status === 'Completed')
                                        <span style="color: green; font-weight:bold;">{{ $reminder->status }}</span>
                                    @elseif($reminder->status === 'Pending')
                                        <span style="color: red; font-weight:bold;">{{ $reminder->status }}</span>
                                    @else
                                        {{ $reminder->status }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

</body>
</html>
