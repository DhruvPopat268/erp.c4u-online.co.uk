<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin-left: 15mm !important;
            margin-right: 15mm !important;
            margin-top: 3mm !important;
            margin-bottom: 20mm !important;
            @top-center {
                content: element(header);
            }
        }
        body {
            font-family: Arial, sans-serif;
            margin-top: 15%;
            padding: 0;
        }
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }
        .header-logo {
            width: 130px;
        }
        .company-name {
            flex-grow: 1;
            text-align: center;
            font-weight: bold;
            margin-top: -5%;
            margin-bottom: 5%;
        }
        .content {
            margin-top: 10px; /* Adjust based on header height */
            text-align:justify;
        }
        .page-break {
            page-break-before: always; /* Forces a page break */
            margin-top: 20px; /* Optional margin before the break */
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            padding: 5px;
            text-align: left;
            border: 0.1px solid #E5E4E2; /* Add border to table cells */
        }
        .table th {
            background-color: #f2f2f2;
        }
        .last-page-table {
            position: absolute;
            bottom: 20mm; /* Position at the bottom with a margin */
            left: 0;
            width: 100%;
        }
        .background-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 3em;
            color: #d0d0d0;
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="background-text">{{ $companyName }}</div> <!-- Background text -->

    <div class="header">
        <div class="header-logo">
            <img src="{{ $img }}" style="max-width: 130px;" alt="Logo"/>
        </div>
        <div class="company-name">
            {{ $companyName }}
        </div>
    </div>
    <div class="content">
        @foreach ($groupedPolicyAssignments as $policyId => $assignments)
            @if ($assignments->isNotEmpty())
                <div>
                    <h1 style="text-align: center">{{ $assignments->first()->policy_name }}</h1>
                    <p><strong>Version:</strong> {{ $assignments->first()->policy_version }}</p> <!-- Display policy version -->
                    <div>{!! $assignments->first()->description !!}</div>
                </div>
            @endif
        @endforeach
    </div>



    <div class="page-break"></div> <!-- Page break added here -->
    
        <!-- Display Policy Versions -->
        <div class="table-responsive mt-3">
            <table class="table">
                <thead>
                <tr>
                    <th>Version</th>
                    <th>Policy reviewed on:</th>
                    <th>Next Review:</th>
                    <th>Reviewed by:</th> <!-- New column -->
                </tr>
                </thead>
                <tbody>
                    @foreach($policyVersions as $version)
                    <tr>
                        <td>{{ $version->policy_version }}</td>
                        <td>{{ $version->reviewed_on }}</td>
                        <td>{{ $version->next_review_date }}</td>
                        <td>{{ $version->creator->username ?? 'N/A' }}</td> <!-- Display assigned by -->
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    <div class="content">
        <h2 style="text-align: center;">Drivers Details</h2>
        <div class="table-responsive mt-3">
            <table class="table datatable">
                <thead>
                <tr>
                    <th>Driver Name</th>
                    <th>Signature</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($policyAssignments as $assignment)
                    <tr>
                        <td>{{  $assignment->driver->name ?? '-' }}</td>
                        <td>
                            @if($assignment->signature)
                                <img src="{{ asset('storage/' . $assignment->signature) }}" alt="Signature" style="width: 100px; height: auto;">
                            @endif
                        </td>
                        <td>{{  $assignment->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
