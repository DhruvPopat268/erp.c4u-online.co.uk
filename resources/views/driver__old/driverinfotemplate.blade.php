<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Information</title>
    <style>
        /* Add your CSS styles here */
        .header {

            text-align: left;
            position: fixed;
            top: 5px;
            width: 100%;
            background-color: transparent;
            border-bottom: 1px solid #ddd;
            z-index: 1000;
            margin-top: -25%;
        }
        .driver-info-box {
            border: 1.5px solid #cacaca;
            background-color: transparent;
            padding: 15px;

            width: 95%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #dce0e0;
        }
        .text-center {
            text-align: center;
        }
        .card {
            margin-bottom: 20px;
        }
        /* Additional styles for the PDF header */
        @page {
            margin-top: 15%;
        }
        .content {
            margin-top: 60px; /* Space for the header */
            font-family:Sans-serif;
        }
    </style>
</head>
<body>
    <!-- Header for PDF -->
    <div class="header">
        <img src="{{ $img }}" style="max-width: 150px;" alt="Logo"/>
    </div>

    <!-- Page Content -->
    <div class="content">
        <!-- Existing Content -->
        <div class="container"  style="margin-top: 5%;">
            <div class="row">
                <div class="col-xl-9">
                    <div id="useradd-1">
                        <div class="row">
                            <div class="col-xxl-5" style="width: 100%;">
                                <div class="card report_card total_amount_card">
                                    <div class="card-body pt-0">
                                        <!-- Driver Information Container -->
                                        <div class="driver-info-box" style="margin-top: -10%;">
                                            <h3>{{ __('Driver Information') }}</h3>
                                            <br>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="row">
                                                        <div class="col-sm-6">
    <dt class="h6 text-sm">
        <span style="font-weight: bold; font-size: 0.85rem !important;">
            {{ __('Driver Licence No') }}:
        </span>
        <span class="text-sm">
            @php
                $maskedLicenceNo = str_repeat('X', 8) . substr($driver->driver_licence_no, 8);
            @endphp
            {{ $maskedLicenceNo }}
        </span>
    </dt>
</div>

                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Issue Number') }}:</span>
                                                                <span class="text-sm">{{ $driver->token_issue_number }}</span>
                                                            </dt>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Licence Valid From') }}:</span>
                                                                <span class="text-sm">{{ $driver->token_valid_from_date }}</span>
                                                            </dt>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Licence Valid To') }}:</span>
                                                                <span class="text-sm">{{ $driver->driver_licence_expiry }}</span>
                                                            </dt>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Name') }}:</span>
                                                                <span class="text-sm">{{ $driver->name }}</span>
                                                            </dt>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Gender') }}:</span>
                                                                <span class="text-sm">{{ $driver->gender }}</span>
                                                            </dt>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Date Of Birth') }}:</span>
                                                                <span class="text-sm">{{ $driver->driver_dob }}</span>
                                                            </dt>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <!--<div class="col-sm-6">-->
                                                        <!--    <dt class="h6 text-sm">-->
                                                        <!--        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Address') }}:</span>-->
                                                        <!--        <span class="text-sm">{{ Str::limit($driver->driver_address, 45) }}</span>-->
                                                        <!--    </dt>-->
                                                        <!--</div>-->
                                                        <div class="col-sm-6">
                                                            <dt class="h6 text-sm">
                                                                <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Address') }}:</span>
                                                                <span class="text-sm">{{ $driver->driver_address }},{{ $driver->post_code }}</span>
                                                            </dt>
                                                        </div>
                                                    </div>
                                                    <!--<div class="row">-->
                                                    <!--    <div class="col-sm-6">-->
                                                    <!--        @if (strlen($driver->driver_address) > 45)-->
                                                    <!--            <span class="text-sm">{{ substr($driver->driver_address, 45) }}</span>-->
                                                    <!--        @endif-->
                                                    <!--    </div>-->
                                                    <!--</div>-->
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-xl-13">
                                                        <div class="row">
                                                            <div class="col-lg-2 col-6" style="margin-left: 53%; margin-top:-35%; margin-right:5%;">
                                                                <div class="card">
                                                                    <div class="card-body" style="min-height: 150px; background-color: #229183; color:white; border-radius: 5px; width:60%">
                                                                        <h4 style="text-align:center; padding-top:8px; font-family:Sans-serif;">{{ __('Licence Information') }}</h4>
                                                                        <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                                                                        <div class="text-center">
                                                                            <div style="font-size: 1.5rem;">{{ $driver->licence_type }}</div>
                                                                            <div style="border-bottom: 1px solid #000; margin: 10px 0;"></div>
                                                                            <div style="font-size: 1.5rem;">{{ $driver->driver_licence_status }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 col-6" style="margin-left: 80%; margin-top:-35%;margin-right:2%;">
    <div class="card">
        <div class="card-body" style="min-height: 150px; background-color: #229183; color:white; border-radius: 5px; width:110%;
        @if($firstPenaltyPoints >= 0 && $firstPenaltyPoints <= 8)
                background-color: #28a745; /* Green */
            @elseif($firstPenaltyPoints >= 9 && $firstPenaltyPoints <= 11)
                background-color: #fd7e14; /* Orange */
            @else
                background-color: #dc3545; /* Red */
            @endif">
            <h4 style="text-align:center; padding-top:8px; font-family:Sans-serif;">{{ __('Endorsements') }}</h4>
            <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
            <div class="d-flex justify-content-between">
                <!-- Left side: Penalty Points -->
                <div class="text-center" style="flex: 1; margin-left: -60px;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $firstPenaltyPoints }}</div>
                    <span style="font-size: 12px;">Points</span>
                </div>
                <!-- Right side: Unique Offence Codes -->
                <div class="text-center" style="flex: 1; margin-left: 60px; margin-top:-60px">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $uniqueOffenceCodeCount }}</div>
                    <span style="font-size: 12px;">Offences</span>
                </div>
            </div>
        </div>
    </div>
</div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Offences and Vehicle Entitlements -->
                                        <!--<div class="driver-info-box">-->
                                        <!--    <h3>{{ __('Offences') }}</h3>-->
                                        <!--    <br>-->
                                        <!--    <table>-->
                                        <!--        <tbody>-->
                                        <!--            <tr>-->
                                        <!--                <td style="font-weight: bold; width: 50%; border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ __('Offence Code') }}:-->
                                        <!--                </td>-->
                                        <!--                <td style="border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ $driver->endorsement_offence_code ?? 'NULL' }}-->
                                        <!--                </td>-->
                                        <!--            </tr>-->
                                        <!--            <tr>-->
                                        <!--                <td style="font-weight: bold; width: 50%; border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ __('Offence Legal Literal') }}:-->
                                        <!--                </td>-->
                                        <!--                <td style="border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ $driver->endorsement_offence_legal_literal ?? 'NULL' }}-->
                                        <!--                </td>-->
                                        <!--            </tr>-->
                                        <!--            <tr>-->
                                        <!--                <td style="font-weight: bold; width: 50%; border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ __('Offence Date') }}:-->
                                        <!--                </td>-->
                                        <!--                <td style="border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ $driver->endorsement_offence_date ?? 'NULL' }}-->
                                        <!--                </td>-->
                                        <!--            </tr>-->
                                        <!--            <tr>-->
                                        <!--                <td style="font-weight: bold; width: 50%; border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ __('Conviction Date') }}:-->
                                        <!--                </td>-->
                                        <!--                <td style="border: 1px solid #ddd; padding: 8px;">-->
                                        <!--                    {{ $driver->endorsement_conviction_date ?? 'NULL' }}-->
                                        <!--                </td>-->
                                        <!--            </tr>-->
                                        <!--        </tbody>-->
                                        <!--    </table>-->
                                        <!--</div>-->
                                        <div class="driver-info-box">
                                            <h3>{{ __('Offences') }}</h3>
                                            <br>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Offence Code') }}</th>
                                    <th>{{ __('Penalty Points') }}</th>
                                    <th>{{ __('Offence Legal Literal') }}</th>
                                    <th>{{ __('Offence Date') }}</th>
                                    <th>{{ __('Conviction Date') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(is_array($endorsements) && count($endorsements) > 0)
                                    @foreach($endorsements as $item)
                                        <tr>
                                            <td>{{ $item['offenceCode'] ?? ' ' }}</td>
                                            <td>{{ $item['penaltyPoints'] ?? ' ' }}</td>
                                            <td>{{ $item['offenceLegalLiteral'] ?? ' ' }}</td>
                                            <td>{{ isset($item['offenceDate']) ? \Carbon\Carbon::parse($item['offenceDate'])->format('d/m/Y') : ' ' }}</td>
<td>{{ isset($item['convictionDate']) ? \Carbon\Carbon::parse($item['convictionDate'])->format('d/m/Y') : ' ' }}</td>


                                        </tr>
                                    @endforeach
                                @else
                                    <!-- Display a message if no endorsements are available -->
                                    <tr>
                                        <td colspan="4" class="text-center">No endorsements available</td>
                                    </tr>
                                @endif
                                                </tbody>

                                            </table>
                                        </div>
                                        
                                        
                                        <!-- Licence Information and Endorsements -->
                                        <div class="driver-info-box">
                                            <div class="row">
                                                <!-- Tacho Card -->
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-body" style="min-height: 20px; background-color:transparent; border-radius: 10px; padding: 20px; border: 0.1px solid #dce0e0; width:35%; margin-top:5%;">
                                                            <h5 class="text-center" style="margin-bottom: 20px;">{{ __('Tacho Card') }}</h5>
                                                            <div class="d-flex justify-content-between" style="border-top: 1px solid #000; padding-bottom: 10px;">
                                                                <div class="d-flex flex-column"  style="margin-top: 10px;">
                                                                    <span><strong>{{ __('Valid From :') }}</strong></span>
                                                                    <span>{{ $driver->tacho_card_valid_from }}</span>
                                                                </div>
                                                                <div class="d-flex flex-column">
                                                                    <span><strong>{{ __('Valid Until :') }}</strong></span>
                                                                    <span>{{ $driver->tacho_card_valid_to }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Driver Qualification Card -->
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-body" style="min-height: 20px; background-color: transparent; border-radius: 10px; padding: 20px; border: 0.1px solid #dce0e0; width:35%; margin-top:-27%;   margin-left:58.5%;">
                                                            <h5 class="text-center" style="margin-bottom: 20px;">{{ __('Driver Qualification Card') }}</h5>
                                                            <div class="d-flex justify-content-between" style="border-top: 1px solid #000; padding-bottom: 10px;">
                                                                <div class="d-flex flex-column"  style="margin-top: 10px;">
                                                                    <span><strong>{{ __('Valid From :') }}</strong></span>
                                                                    <span>{{ $driver->dqc_issue_date }}</span>
                                                                </div>
                                                                <div class="d-flex flex-column">
                                                                    <span><strong>{{ __('Valid Until :') }}</strong></span>
                                                                    <span>{{ $driver->cpc_validto ?? 'N/A' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                            </div>
                                        </div>



                                        <!-- Page Break -->
                                        <div style="page-break-before: always;"></div>

                                        <div class="driver-info-box">
                                            <h3>{{ __('Vehicle You Can Drive') }}</h3>
                                            <br>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Category') }}</th>
                                                        <th>{{ __('Start Date') }}</th>
                                                        <th>{{ __('Until Date') }}</th>
                                                        <th>{{ __('Category Type') }}</th>
                                                        <th>{{ __('Restrictions Code') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($driver->entitlements as $entitlements)
                                                    <tr>
                                                        <td>
                                                            
                                                                {{ $entitlements->category_code ?? 'NULL' }}
                                                        </td>
                                                        <td>{{ $entitlements->from_date ?? 'NULL' }}</td>
                                                        <td>{{ $entitlements->expiry_date ?? 'NULL' }}</td>
                                                        <td>{{ $entitlements->category_type ?? 'NULL' }}</td>
                                                        <td>{{ $entitlements->restriction_code }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>

                                            </table>
                                            <div class="mt-4" style="margin-top: 3%;">
                                                <div class="d-flex justify-content-between">
                                                    <div class="d-flex flex-column w-50">
                                                        <span><strong>{{ __('Driver Content Valid Until') }}______________________________________</strong></span><br>
                                                        <span></span>
                                                    </div>
                                                    <div class="d-flex flex-column w-50" style="margin-top:2%">
                                                        <span><strong>{{ __('Current Licence Check Interval') }}_____________________________________</strong></span><br>
                                                        <span></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
<p>This disclaimer clarifies that PTC (Paramount Transport Consultants Ltd) is not accountable for the accuracy of the provided data since
            it originates from the DVLA (Driver and Vehicle Licensing Agency). By including this statement, PTC aims to inform users that any
            discrepancies or errors in the data are beyond their control and responsibility. If users encounter any issues or inaccuracies within the data,
            they are encouraged to reach out to PTC's technical team for assistance. The contact information for the technical team is provided,
            specifically an email address <span style="color: blue">(it@ptctransport.co.uk)</span>, to ensure users have a direct line of communication to report problems or seek further information. This approach helps manage user expectations and directs them to the appropriate support channel for resolution, maintaining transparency and accountability in data handling.
        </p>


                                        <div class="driver-info-box">
                                            <h3>{{ __('Entitlements') }}</h3>
                                            <br>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th style="width: 14%;">{{ __('Category Code') }}</th>
                                                        <th>{{ __('Legal Literal') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($driver->entitlements as $entitlement)
                                                    <tr>
                                                        <td>{{ $entitlement->category_code }}</td>
                                                        <td>{{ $entitlement->category_legal_literal }}</td>
                                                         <div >
        
    </div>
</tbody>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            <tbody>
   
                                        </div>



                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
