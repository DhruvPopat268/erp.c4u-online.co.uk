<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Walkaround Report</title>
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
         margin-top: -30px; /* Space for the header */
         font-family: Sans-serif;
         }
      </style>
   </head>
   <body>
      <!-- Header for PDF -->
      <div class="header">
         <div class="header-logo">
            <img src="{{ $img }}" style="max-width: 130px;" alt="Logo"/>
         </div>
         <div class="header-info">
            <div class="h6 text-sm">
               <span class="text-sm" style="font-family: Arial, Helvetica, sans-serif;"><b>{{ $walkaroundData->first()->types->name ?? 'Company Name' }}</b></span>
            </div>
         </div>
      </div>
      <!-- Page Content -->
      <div class="content">
         <div class="container">
            <div class="row">
               <div class="col-xl-9">
                  <div id="useradd-1">
                     <div class="row">
                        <div class="col-xxl-5" style="width: 100%;">
                           <div class="card report_card total_amount_card">
                              <div class="card-body pt-0">
                                 <table>
                                    <thead>
                                       <tr>
                                          <th>Driver</th>
                                          <th>Depot</th>
                                          <th>Vehicle</th>
                                          <th>WalkAround Date</th>
                                          <th>Duration</th>
                                          <th>Defects</th>
                                          <th>Rectified</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       @foreach($walkaroundData as $data)
                                       <tr>
                                          <td>{{ $data->driver->name ?? 'N/A' }}</td>
                                          <td>{{ $data->depot->name ?? 'N/A' }}</td>
                                          <td>
                                              @if ($data->vehicle)
            @if ($data->vehicle->vehicle_type == 'Trailer')
                {{ $data->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID' }} - {{ $data->vehicle->vehicleDetail->make ?? 'No Make' }}
            @else
                {{ $data->vehicle->registrations ?? 'No Registration' }} - {{ $data->vehicle->vehicleDetail->make ?? 'No Make' }}
            @endif
        @else
            N/A
        @endif
                                          </td>
                                          <td>{{ $data->uploaded_date ?? 'N/A' }}</td>
                                          <td>{{ $data->duration ?? '0' }}</td>
                                          <td>{{ $data->defects_count ?? 0 }}</td>
                                          <td> {{ $data->rectified ?? 0 }}</td>
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
   </body>
</html>
