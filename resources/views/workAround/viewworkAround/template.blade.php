<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Walkaround Detail</title>
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
            justify-content: space-between;
            padding: 10px;
         }
         .header-logo {
            display: flex;
            align-items: center;
            margin-top: 20px;
         }
         .header-info {
            text-align: right;
            flex-grow: 5;
            margin-top: -200px;
            margin-bottom: -10px;
            font-family: Sans-serif;
         }
         .walkaround-info-box {
            border: 1.5px solid #cacaca;
            background-color: transparent;
            padding: 15px;
            width: 95%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
         }
         table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            margin-top: -5px;
         }
         th, td {
            border: transparent;
            padding: 3px;
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
            position: relative;
            min-height: 90vh; /* Ensures that content will push footer to the bottom */
         }
         .footer {
            position: fixed;
            bottom: 0;
            width: 97.5%;
            background-color: #f2f2f2;
            padding: 19px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ddd;
            color: #333;
            font-family: Sans-serif;
         }

         .signature-box {
            border: transparent;
            background-color: transparent;
            padding: 5px;
            width: 100%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1px;

         }

         .check-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.80rem;
            margin-top: -10px;
        }

        .check-cell {
            border: transparent;
            padding: 3px;
            word-wrap: break-word; /* Ensures that long text wraps within the cell */
            max-width: 150px; /* Adjust the max-width as needed */
        }


      </style>
   </head>
   <body>
    @php
    function chunkString($string, $chunkSize = 10) {
        // Break the string into an array of chunks
        $chunks = str_split($string, $chunkSize);
        // Join the chunks with <br> to display each chunk on a new line
        return implode('<br>', $chunks);
    }

@endphp

      <div class="header">
         <div class="header-logo">
            <img src="{{ $img }}" style="max-width: 130px;" alt="Logo"/>
         </div>
         <div class="header-info">
            <h3>Walkaround Details</h3>
            <p style="margin-top: -10px;">        {{ \Carbon\Carbon::now('Europe/London')->format('d/m/Y H:i') }}
</p>
         </div>
      </div>
      <!-- Page Content -->
      <div class="content">
         <!-- Existing Content -->
         <div class="container" style="margin-top: 5%;">
            <div class="row">
               <div class="col-xl-9">
                  <div id="useradd-1">
                     <div class="row">
                        <div class="col-xxl-5" style="width: 100%;">
                           <div class="card report_card total_amount_card">
                              <div class="card-body pt-0">
                                 <!-- First Walkaround Info Box -->
                                 <div class="walkaround-info-box" style="margin-top: -13.5%;">
                                    <table>
                                        <tr>
                                            <td><strong>Company:</strong><span style="margin-left: 30px;">{{ $workAround->types->name }}</span></td>
                                            <td><strong>Operating Centre:</strong><span style="margin-left: 14px;">{{ $workAround->depot->name }}</span></td>
                                         </tr>
                                         <tr>
                                          <td><strong>Walkaround:</strong> <span style="margin-left: 11px;">#{{ $workAround->id }}</span></td>
                                            <td><strong>Vehicle:</strong> <span style="margin-left: 70px;">
                                                @if ($workAround->vehicle)
                                                        @if ($workAround->vehicle->vehicle_type == 'Trailer')
                                                            {{ $workAround->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID' }} - {{ $workAround->vehicle->vehicleDetail->make ?? 'No Make' }}
                                                        @else
                                                            {{ $workAround->vehicle->registrations ?? 'No Registration' }} - {{ $workAround->vehicle->vehicleDetail->make ?? 'No Make' }}
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                            </span></td>
                                         </tr>
                                    </table>
                                 </div>
                                 <!-- Second Walkaround Info Box -->
                                 <div class="walkaround-info-box">
                                    <table>
                                        <tr>
                                            <td><strong>Driver:</strong> <span style="margin-left: 33px;">{{ ucwords(strtolower($workAround->driver->name)) ?? 'N/A' }}</span></td>
                                            <td><strong>Profile:</strong><span style="margin-left: 29px;">{{ $workAround->profile->name ?? 'N/A' }}</span></td>
                                         </tr>
                                         <tr>
                                          <td><strong>Date:</strong><span style="margin-left: 46px;">{{ $workAround->uploaded_date }}</span> </td>
                                            <td><strong>Duration:</strong><span style="margin-left: 14.5px;">{{ $workAround->duration ?? 'N/A' }}</span> </td>
                                         </tr>
                                         <tr>
                                            <td><strong>Outcome:</strong><span style="margin-left: 13px;">
                                                @if ($defectCount > 0)
                                                {{ $defectCount }} defects reported
                                            @else
                                                No defects reported
                                            @endif</span> </td>
                                              <!--<td><strong>Location:</strong><span style="margin-left: 14px;">{{ $workAround->location ?? 'N/A' }}</span> </td>-->
                                           </tr>
                                    </table>
                                 </div>
                                 <!-- Walkaround Checks Passed Section -->
                                 <div class="walkaround-info-box">
                                    <h4 style="margin-top: -1%">Walkaround Checks Passed</h4>
                                    <table class="check-table">
                                        <tr>
                                            <td class="check-cell">Odometer Reading: {{ $workAround->speedo_odometer ?? 'N/A' }}</td>
                                            <td class="check-cell">Fuel Level: {{ $workAround->fuel_level ?? 'N/A' }}</td>
                                                                                        <td class="check-cell">Adblue Level: {{ $workAround->adblue_level ?? 'N/A' }}</td>

                                        </tr>
                                        @foreach($passedChecks->chunk(3) as $chunkedChecks)
                                            <tr>
                                                @foreach($chunkedChecks as $passedCheck)
                                                    <td class="check-cell">                        <img src="{{ $rightImage }}" style="width: 11px; height: auto;" alt="Default Image"/>
{{ $passedCheck->question->name }}
 @if(!empty($passedCheck->other_reason))
                                          - ({{ $passedCheck->other_reason }})
                                      @endif
</td>
                                                @endforeach
                                                @if(count($chunkedChecks) < 3)
                                                    @for($i = count($chunkedChecks); $i < 3; $i++)
                                                        <td class="check-cell"></td>
                                                    @endfor
                                                @endif
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                                <div class="walkaround-info-box">
                                    <h4 style="margin-top: -1%">Walkaround Defects</h4>
                                    <table>
                                       <thead style="font-size: 12px;">
                                        <th>Reg</th>  #vehicle id
                                        <th>Status</th> # date null or not null par thi
                                        <th>Check</th> # question name
                                        <th>Defect Type</th> #
                                        <th>Defect Description</th> # reason

                                       </thead>
                                       <tbody style="font-size: 10px; text-align:center;">
                                        @foreach($notRectifiedDefects as $defect)
                                        <tr>
                                            <td>{{ $defect->vehicle_registration  ?? 'No' }}</td>
                                            <td>
                                                @if ($defect->rectified_date)
                                                    Rectified
                                                @else
                                                    Not Rectified
                                                @endif
                                            </td>
                                            <td>{{ $defect->question->name ?? 'N/A' }}</td>
                                            <td>{{ $defect->problem_type ?? '-' }}</td>
                                            <td>{!! chunkString($defect->reason ?? 'N/A', 10) !!}</td>


                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="signature-box">
                                <div>
                                    <h5 style="margin-top: -1%;">Signed By and Verified with PIN:</h5>
                                    <p style="font-size: 12px; text-align: left;margin-top: -3%;">
                                        This is to certify that I have read, understood and answered all questions to the best of my knowledge.
                                    </p>
                                </div>

                                <div class="walkaround-info-box">

                                    <div style="text-align: center;">
                                        @if (!empty($signatureImg))
                                        <img src="{{ $signatureImg }}" style="max-width: 100px; height: auto;" alt="Signature"/>
                                    @else
                                        <p>No signature available.</p>
                                    @endif
                                    </div>
                                </div>


                            </div>
                            <div class="walkaround-info-box">
                                <h4 style="margin-top: -1%">Walkaround Rectified </h4>
                                <table>
                                   <thead style="font-size: 12px;">
                                    <th>Reg</th>  #vehicle id
                                    <th>Check</th> # question name
                                    <th>Defect Type</th> #
                                    <th>Defect Description</th> # reason
                                    <th>Rectified Date</th>
                                    <th>Rectified By</th>
                                    <th>Action Taken</th> # defect options
                                    <th>Action Notes</th>
                                    <th>Signature</th>
                                   </thead>
                                   <tbody style="font-size: 10px; text-align:center;">
                                    @foreach($rectifiedDefects as $defect)
                                    <tr>
                                        <td>{{ $defect->vehicle_registration  ?? 'No' }}</td>

                                        <td>{{ $defect->question->name ?? 'N/A' }}</td>
                                        <td>{{ $defect->problem_type ?? '-' }}</td>
                                        <td>{!! chunkString($defect->reason ?? 'N/A', 10) !!}</td>

                                        <td>{{ $defect->rectified_date ?? '' }}</td>
                                        <td>{{ $defect->rectified_username ?? '' }}</td>
                                        <td>{{ $defect->defect_options ?? '' }}</td>
                                        <td>{{ $defect->problem_solution ?? '' }}</td>
                                        <td>
                                            @if (!empty($defect->rectified_signature))
                                            <img src="{{ $defect->rectified_signature }}" style="max-width: 100px; height: auto;" alt="Signature"/>
                                            @else
                                            <p>No signature available.</p>
                                            @endif
                                        </td>

                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
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

     <!-- <div class="footer">-->
     <!--   {{ \Carbon\Carbon::now('Europe/London')->format('d/m/Y H:i') }}-->
     <!--</div>-->
   </body>
</html>
