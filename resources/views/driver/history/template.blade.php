<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{{ $driver->name }} Driver Information </title>
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
         display: flex;
         justify-content: space-between; /* Align content to the edges */
         padding: 10px; /* Add padding for spacing */

         }
         .header-logo {
         display: flex;
         align-items: center;
         }
         .header-info {
         text-align: right;
         flex-grow: 5;
         margin-top: -20px;
         margin-bottom: -5px;
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
         margin-top: -15px;
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
         margin-top: 30px; /* Space for the header */
         font-family: Sans-serif;
         }
         .footer {
            position: fixed;
            bottom: 0;
            width: 97.5%;
            background-color: #f2f2f2;
            padding: 9px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ddd;
            color: #333;
            /*margin-top: 50px;*/
             margin-bottom: -40px;
             font-family: Sans-serif;
         }
      </style>
   </head>
   <body>
    @php
    use Carbon\Carbon;

    // Fetch current time in the server's default timezone
    $systemTime = Carbon::now();

    // Convert current time to UK timezone
    $ukTime = $systemTime->setTimezone('Europe/London')->format('d-M-Y H:i:s');

    // Convert current time to India timezone
    $indiaTime = $systemTime->setTimezone('Asia/Kolkata')->format('d-M-Y H:i:s');
@endphp
      <!-- Header for PDF -->
      <div class="header">
         <div class="header-logo">
            <img src="{{ $img }}" style="max-width: 130px;" alt="Logo"/>
         </div>
         <div class="header-info">
            <div class="h6 text-sm">
               <span class="text-sm" style="  font-family: Arial, Helvetica, sans-serif;
"><b>Driving licence check summary </b></span>
            </div>
            <!--<div class="h6 text-sm">-->
            <!--   <span class="text-sm">{{ $driver->driver_dob }}</span>-->
            <!--</div>-->
            @php
            $address = $driver->driver_address . ', ' . $driver->post_code;
            $wrappedAddress = wordwrap($address, 40, "\n", true);
            @endphp
            <!--<div class="h6 text-sm">-->
            <!--   @foreach(explode("\n", $wrappedAddress) as $line)-->
            <!--   <span class="text-sm">{{ $line }}</span><br>-->
            <!--   @endforeach-->
            <!--</div>-->
         </div>
      </div>
      <!-- Page Content -->
      <div class="content">
         <!-- Existing Content -->
         <div class="container">
            <div class="row">
               <div class="col-xl-9">
                  <div id="useradd-1">
                     <div class="row">
                        <div class="col-xxl-5" style="width: 100%;">
                           <div class="card report_card total_amount_card">
                              <div class="card-body pt-0">
                                 <!-- Driver Information Container -->
                                 <div class="driver-info-box" style="margin-top: -13.5%;">
                                    <!--<h3 style="margin-top: -10px;">{{ __('Driver Information') }}</h3>-->
                                    <h3 style="margin-top: -0px;">{{ $driver->name }}</h3>
                                    <p class="text-sm" style="font-size: 12px; text-align:right; margin-top:-30px; margin-bottom:20px;">
                                        <!--{{ __('Summary generated :') }} {{ $ukTime }}-->
                                         {{ __('Summary generated :') }} {{ $driver->latest_lc_check }}
                                    </p>
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
                                          {{--  <div class="row">
                                             <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                   <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Name') }}:</span>
                                                   <span class="text-sm">{{ $driver->name }}</span>
                                                </dt>
                                             </div>
                                          </div>  --}}
                                          <div class="row">
                                             <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                   <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Gender') }}:</span>
                                                   <span class="text-sm" style="text-transform:uppercase; font-size: 0.85rem">{{ $driver->gender }}</span>
                                                </dt>
                                             </div>
                                             <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                   <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Date Of Birth') }}:</span>
                                                   <span class="text-sm" style="font-size: 0.85rem">{{ $driver->driver_dob }}</span>
                                                </dt>
                                             </div>

                                          </div>
                                           <div <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                             <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Consent Valid Until') }} :</span> <span class="text-sm" style="font-size: 0.85rem">{{ $driver->consent_valid ?? '___________'  }} </strong></span><br>
                                             <span></span>
                                          </div>
                                          <div <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                             <span><span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Current Licence Check Interval') }} :</strong></span> <span class="text-sm" style="font-size: 0.85rem">  {{ $driver->current_licence_check_interval ?? '___________' }}</span><br>
                                             <span></span>
                                          </div>
                                       </div>
                                    </div>
                                          <div class="row">
 @php
            $address = ucwords(strtolower($driver->driver_address)) . ', ' . $driver->post_code;
            $wrappedAddress = wordwrap($address, 40, "\n", true);
            @endphp
                                         <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                   <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Address') }}:</span>
                                                @foreach(explode("\n", $wrappedAddress) as $line)
        <span class="text-sm" style="font-size: 0.85rem">{{ $line }}</span><br>
            @endforeach
                                                   <!--<span class="text-sm">{{ $driver->driver_address }},{{ $driver->post_code }}</span>-->
                                                </dt>
                                             </div>
                                          </div>

                                       </div>

                                       <div class="col-sm-6">
                                          <div class="col-xl-13">
                                             <div class="row">
                                                {{--  <p class="text-sm" style="font-weight: bold;">
                                                    {{ __('Summary Generated (UK Time):') }} {{ $ukTime }}
                                                </p>  --}}

                                                <div class="col-lg-2 col-6" style="margin-left: 50%; margin-top:-32%; margin-right:5%;">
                                                    <!--margin-left: 53%-->

                                                   <div class="card">
                                                      <div class="card-body" style="min-height: 150px; background-color: #229183; color:white; border-radius: 5px; width:55%">
                                                          <!--width:60%"-->
                                                         <h4 style="text-align:center; padding-top:8px; font-family:Sans-serif;">{{ __('Licence Status') }}</h4>
                                                         <!--<div style="border-top: 2px solid #000; margin: 10px 0;"></div>-->
                                                         <div class="text-center">
                                                            <div style="font-size: 1.5rem;">{{ $driver->licence_type }}</div>
                                                            <!--<div style="border-bottom: 1px solid #000; margin: 10px 0;"></div>-->
                                                            <div style="font-size: 1.5rem;">{{ $driver->driver_licence_status }}</div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-lg-3 col-6" style="margin-left: 76%; margin-top:-35%;margin-right:2%;">
                                                   <div class="card">
                                                       <!--width:110%-->
                                                      <div class="card-body" style="min-height: 150px; background-color: #229183; color:white; border-radius: 5px; margin-right:5%; width:110%;
                                                         @if($firstPenaltyPoints >= 0 && $firstPenaltyPoints <= 5)
                                                         background-color: #28a745; /* Green */
                                                         @elseif($firstPenaltyPoints >= 6 && $firstPenaltyPoints <= 11)
                                                         background-color: #fd7e14; /* Orange */
                                                         @else
                                                         background-color: #dc3545; /* Red */
                                                         @endif">
                                                         <h4 style="text-align:center; padding-top:8px; font-family:Sans-serif;">{{ __('Endorsements') }}</h4>
                                                         <!--<div style="border-top: 2px solid #000; margin: 10px 0;"></div>-->
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
                                    <h3 style="margin-top: -10px">{{ __('Offences') }}</h3>
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
                                             <td><center>{{ $item['offenceCode'] ?? ' ' }}</center></td>
                                             <td><center> {{ $item['penaltyPoints'] ?? ' ' }}</center></td>
                                             <td>{{ $item['offenceLegalLiteral'] ?? ' ' }}</td>
                                             <td><center>{{ isset($item['offenceDate']) ? \Carbon\Carbon::parse($item['offenceDate'])->format('d/m/Y') : ' ' }}</center></td>
                                             <td><center>{{ isset($item['convictionDate']) ? \Carbon\Carbon::parse($item['convictionDate'])->format('d/m/Y') : ' ' }}</center></td>
                                          </tr>
                                          @endforeach
                                          @else
                                          <!-- Display a message if no endorsements are available -->
                                          <tr>
                                             <td colspan="5" class="text-center">No endorsements available</td>
                                          </tr>
                                          @endif
                                       </tbody>
                                    </table>
                                 </div>
                                 <!-- Licence Information and Endorsements -->
<div class="driver-info-box" style="height:19% !important;">
   <table style="width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dce0e0;">
      <thead>
         <tr>
            <th style="background-color: #dce0e0; text-align: center; padding: 10px; border: 1px solid #dce0e0;">
               {{ __('Driver Tachograph') }}
            </th>
            <th style="background-color: #dce0e0; text-align: center; padding: 10px; border: 1px solid #dce0e0;">
               {{ __('Driver Qualification Card') }}
            </th>
         </tr>
      </thead>
      <tbody>
         <tr>
            <!-- Driver Tachograph Column -->
            <td style="text-align: center; vertical-align: top; border: 1px solid #dce0e0; padding: 20px;">
               <div class="d-flex flex-column">
                  <div style="margin-bottom: 10px;">
                     <strong>{{ __('Number :') }}</strong> {{ $driver->tacho_card_no }}
                  </div>
                  <div style="margin-bottom: 10px;">
                     <strong>{{ __('Valid From :') }}</strong> {{ $driver->tacho_card_valid_from }}
                  </div>
                  <div>
                     <strong>{{ __('Valid Until :') }}</strong> {{ $driver->tacho_card_valid_to }}
                  </div>
               </div>
            </td>
            <!-- Driver Qualification Card Column -->
            <td style="text-align: center; vertical-align: top; border: 1px solid #dce0e0; padding: 20px;">
               <div class="d-flex flex-column">
                  <div style="margin-bottom: 10px;">
                     <strong>{{ __('Type :') }}</strong> International
                  </div>
                  <div style="margin-bottom: 10px;">
                     <strong>{{ __('Valid From :') }}</strong> {{ $driver->dqc_issue_date }}
                  </div>
                  <div>
                     <strong>{{ __('Valid Until :') }}</strong> {{ $driver->cpc_validto ?? 'N/A' }}
                  </div>
               </div>
            </td>
         </tr>
      </tbody>
   </table>
</div>

                                 <!-- Page Break -->
                                 <div style="page-break-before: always;"></div>
                                 <div class="driver-info-box" style="height: 3%;">
                                 <div class="col-xl-3">
                                    <div class="card card-body pt-0">
                                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/exclamation.png'))) }}" alt="" style="display: inline-block; vertical-align: middle; width:4%">
                                        <p style="margin-left:6%; margin-top:-3.5%">
                                        More information is available at.
                                        <a href="{{ route('driver.entitlements', ['id' => $driver->id]) }}" target="_blank">Click here to view more details on Entitlements</a>
                                    </p>
                                    </div>
                                </div>
                                 </div>

                                 <div class="driver-info-box">
                                    <h3 style="margin-top: -10px;">{{ __('Vehicle You Can Drive') }}</h3>

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
                                                @if($entitlements->category_code == 'AM')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/AM.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'A')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/A.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'B1')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/B1.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'B')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/B.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'BE')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/BE.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/B.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'F')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/F.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'C')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/C.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'C1')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/C1.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'C1E')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/C1E.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/C1.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'CE')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/CE.png'))) }}" alt="" style="display: inline-block; vertical-align: middle; width:28%; ">
                                                @elseif($entitlements->category_code == 'D')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/D.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @elseif($entitlements->category_code == 'D1')
                                                {{ $entitlements->category_code }}
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/category_icons/D1.png'))) }}" alt="" style="display: inline-block; vertical-align: middle;">
                                                @else
                                                {{ $entitlements->category_code ?? 'NULL' }}
                                                @endif
                                             </td>
                                             <td>{{ $entitlements->from_date ?? 'NULL' }}</td>
                                             <td>{{ $entitlements->expiry_date ?? 'NULL' }}</td>
                                             <td>{{ $entitlements->category_type ?? 'NULL' }}</td>
                                             <td>
                                                @if($entitlements->restrictions)
                                                @php
                                                $restrictions = json_decode($entitlements->restrictions, true);
                                                @endphp
                                                @foreach($restrictions as $restriction)
                                                {{ $restriction['restrictionCode'] }}
                                                @if (!$loop->last), @endif
                                                @endforeach
                                                @else
                                                NULL
                                                @endif
                                             </td>
                                          </tr>
                                          @endforeach
                                       </tbody>
                                    </table>
                                    <!--<div class="mt-4" style="margin-top: 3%;">-->
                                    <!--   <div class="d-flex justify-content-between">-->
                                    <!--      <div class="d-flex flex-column w-50">-->
                                    <!--         <span><strong>{{ __('Driver Consent Valid Until') }} = {{ $driver->content_valid_until ?? '___________'  }} </strong></span><br>-->
                                    <!--         <span></span>-->
                                    <!--      </div>-->
                                    <!--      <div class="d-flex flex-column w-50" style="margin-top:2%">-->
                                    <!--         <span><strong>{{ __('Current Licence Check Interval') }} = {{ $driver->current_licence_check_interval ?? '___________' }}</strong></span><br>-->
                                    <!--         <span></span>-->
                                    <!--      </div>-->
                                    <!--   </div>-->
                                    <!--</div>-->
                                 </div>
                                 {{--  <div class="driver-info-box">
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
                                          </tr>
                                          @endforeach
                                       </tbody>
                                    </table>
                                 </div>  --}}
                              </div>
                                <!-- Footer with Disclaimer -->
    <footer class="footer">
        <p class="disclaimer">
            This disclaimer clarifies that PTC (Paramount Transport Consultants Ltd) is not accountable for the accuracy of the provided data since
            it originates from the DVLA (Driver and Vehicle Licensing Agency). By including this statement, PTC aims to inform users that any
            discrepancies or errors in the data are beyond their control and responsibility. If users encounter any issues or inaccuracies within the data,
            they are encouraged to reach out to PTC's technical team for assistance. The contact information for the technical team is provided,
            specifically an email address <span style="color: blue;">(it@ptctransport.co.uk)</span>, to ensure users have a direct line of communication to report problems or seek further information. This approach helps manage user expectations and directs them to the appropriate support channel for resolution, maintaining transparency and accountability in data handling.
        </p>
    </footer>
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
