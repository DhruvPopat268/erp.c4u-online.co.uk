@extends('layouts.admin')

@section('page-title')
    {{ __('Planner Reminder Details') }}
@endsection

@push('script-page')
<!-- Add Bootstrap 5 Modal JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Show the modal on clicking the Save button
        $('#saveButton').on('click', function() {
            $('#confirmModal').modal('show');
        });

        // Submit the form when the Confirm button is clicked
        $('#confirmSubmit').on('click', function() {
            $('#formToSubmit').submit();  // Submit the form
        });

        // Calculate total cost based on parts, labour, and tyre cost
        function calculateTotalCost() {
            var parts = parseFloat($('#parts').val()) || 0;
            var labour = parseFloat($('#labour').val()) || 0;
            var tyreCost = parseFloat($('#tyre_cost').val()) || 0; // Get the tyre cost value
            var totalCost = parts + labour + tyreCost; // Add tyre cost to total
            $('#total_cost').val(totalCost.toFixed(2)); // Update total cost field
        }

        // Trigger calculation when parts, labour, or tyre cost values change
        $('#parts, #labour, #tyre_cost').on('input', function() {
            calculateTotalCost();
        });

        // Initialize total cost on page load if parts, labour, or tyre cost have values
        calculateTotalCost();
    });

    function validateInput(input) {
        const max = parseFloat(input.max); // Get the max value
        const currentValue = parseFloat(input.value); // Get the current value

        const errorElement = document.getElementById(`error_${input.id}`); // Find the error element by ID

        // Check if the value exceeds max
        if (!isNaN(currentValue) && currentValue > max) {
            errorElement.classList.remove('d-none'); // Show error message
        } else {
            errorElement.classList.add('d-none'); // Hide error message
        }
    }
</script>

@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{ __('Forward Planner') }}</a>
    <li class="breadcrumb-item active">{{ __('Planner Reminder Details') }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Fleet Details Section -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Planner Reminder Information ') }} {{ $fleet->name }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>{{ __('Planner Type') }}</th>
                            <td>{{ $fleet->planner_type }}</td>
                          <th>{{ __('Vehicle Registration Number') }}</th>
                            <td>{{ $fleet->vehicle->registrationNumber }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Start Date') }}</th>
                            <td>{{ \Carbon\Carbon::parse($fleet->start_date)->format('d/m/Y') }}</td>
                            <th>{{ __('End Date') }}</th>
                            <td>{{ \Carbon\Carbon::parse($fleet->end_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
            <th>{{ __('Reminder Date') }}</th>
            <td>{{ \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d/m/Y') }}</td>
            <th>{{ __('Main Vehicle Status') }}</th>
            <td>{{ $fleet->vehicle->vehicle_status }}</td>
        </tr>

                                     @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                        <tr>
                              <th>{{ __('Company Name') }}</th>
                            <td>{{ $fleet->company->name ?? 'N/A' }}</td>
                        </tr>
                           @endif
                    </table>
                </div>

            </div>
        </div>
    </div>

        <!-- New Table: Reminder Details, Vehicle Status, and Action (Just the first reminder) -->
        <div class="row">
    <div class="col-xxl-5" style="width: 100%;">
               <div class="card report_card total_amount_card">
                  <div class="card-body pt-0" style="margin-top: 13px;">
        <h5>{{ __('Reminder Details') }}</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ __('Reminder Date') }}</th>
                    <th>{{ __('Vehicle Status') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d/m/Y') }}</td>
                    <td>
                       {{ $reminder->vehicle_status }}
                    </td>
                    <td>
@php
        $firstPending = $reminders->where('status', 'Pending')->first();
    @endphp
                    @if($firstPendingReminder && $reminder->id === $firstPendingReminder->id)
                    <div class="action-btn bg-info ms-2">
                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                           data-url="{{ route('reminder.edit', $reminder->id) }}"
                                           data-ajax-popup="true"
                                           data-size="md"
                                           data-bs-toggle="tooltip"
                                           title="{{ __('Edit') }}"
                                           data-title="{{ __('Edit Type') }}">
                                            <i class="ti ti-pencil text-white"></i>
                                        </a>
                                    </div>
                                @endif
                                 @if($firstPending && $reminder->id != $firstPending->id && $reminder->status == 'Pending')
        <div class="text-danger">
            ⚠ Pending Reminder ({{ \Carbon\Carbon::parse($firstPending->next_reminder_date)->format('d-m-Y') }}) <br>
            You have not completed your Previous reminders. <br>
            Please complete all past inspections before you can set or change the next inspection date.
          <br><br>
        <a href="{{ route('fleet.show', $firstPending->id) }}" class="btn btn-sm btn-danger">
            View Pending Reminder
        </a>
        </div>
    @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
   </div>
   </div>
   </div>

   @if ($reminder->reminder_status === 'Done' && !in_array($reminder->vehicle_status, ['Sold', 'Scrapped']))
   <div class="row">
    <div class="col-xxl-5" style="width: 100%;">
               <div class="card report_card total_amount_card">
                  <div class="card-body pt-0" style="margin-top: 13px;">
                    <h5>{{ __('Form') }}</h5>
         <form id="formToSubmit" action="{{ route('fleet.update-status', $reminder->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="col-xxl-5" style="width: 100%;">
               <div class="card report_card total_amount_card">
                  <div class="card-body pt-0" style="margin-top: 13px;">
                     <!-- Odometer Reading -->
                     <div class="form-group mt-3">
                        <label for="odometer_reading">Odometer Reading</label>
                        <input type="number" class="form-control" name="odometer_reading" id="odometer_reading" placeholder="Enter Odometer Reading" value="{{ old('odometer_reading', $reminder->odometer_reading) }}" step="0.1" min="0"
                           @if($reminder->status === 'Completed') readonly @endif>
                     </div>
                     <!-- Multiple File Upload -->
                     <div class="form-group mt-3" @if($reminder->status === 'Completed') style="display: none;" @endif>
                        <label for="files">Upload Files</label>
                        <input type="file" class="form-control" name="files[]" id="files" multiple>
                        <small class="form-text text-muted">You can upload multiple files</small>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xxl-5" style="width: 100%;">
               <div class="card report_card total_amount_card">
                  <div class="card-body pt-0" style="margin-top: 13px;">
                     <!-- Type Depth (MM) -->
                     <div class="form-group mt-4">
                        <label>Tyre Depth (mm)</label>
                        <!-- N/S Section -->
                        <div class="mt-4">
                           <label for="ns_depth" class="form-label"><strong>N/S</strong></label>
                           <div class="row">
                              <!-- First Row: N/S 1, N/S 2, N/S 3, N/S 5 -->
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="ns_depth_1" id="ns_depth_1" placeholder="N/S 1"
       value="{{ old('ns_depth_1', $reminder->tyreDepth->ns_depth_1 ?? $lastTyreDepth->ns_depth_1 ?? '') }}"
                                    step="0.1" min="0"
       @if(!is_null($lastTyreDepth?->ns_depth_1)) max="{{ $lastTyreDepth->ns_depth_1 }}" @endif
                                   @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_ns_depth_1">
    Value exceeds the maximum allowed (<span id="max_ns_depth_1">{{ $lastTyreDepth?->ns_depth_1 ?? 0 }}</span>).
                                 </small>

                              </div>

                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="ns_depth_2" id="ns_depth_2" placeholder="N/S 2" value="{{ old('ns_depth_2', $reminder->tyreDepth->ns_depth_2 ?? $lastTyreDepth->ns_depth_2 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->ns_depth_2 ?? null))
                                 max="{{ $lastTyreDepth->ns_depth_2 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_ns_depth_2">
                                 Value exceeds the maximum allowed (<span id="max_ns_depth_2">{{ $lastTyreDepth?->ns_depth_2 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="ns_depth_3" id="ns_depth_3" placeholder="N/S 3" value="{{ old('ns_depth_3', $reminder->tyreDepth->ns_depth_3 ?? $lastTyreDepth->ns_depth_3 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->ns_depth_3 ?? null))
                                 max="{{ $lastTyreDepth->ns_depth_3 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_ns_depth_3">
                                 Value exceeds the maximum allowed (<span id="max_ns_depth_3">{{ $lastTyreDepth?->ns_depth_3 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="ns_depth_5" id="ns_depth_5" placeholder="N/S 5" value="{{ old('ns_depth_5', $reminder->tyreDepth->ns_depth_5 ?? $lastTyreDepth->ns_depth_5 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->ns_depth_5 ?? null))
                                 max="{{ $lastTyreDepth->ns_depth_5 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_ns_depth_5">
                                 Value exceeds the maximum allowed (<span id="max_ns_depth_5">{{ $lastTyreDepth?->ns_depth_5 ?? 0 }}</span>).
                                 </small>
                              </div>
                           </div>
                           <!-- Second Row: N/S 4, N/S 6 -->
                           <div class="row">
                              <div class="col-md-2 offset-md-4 mb-2">
                                 <input type="number" class="form-control" name="ns_depth_4" id="ns_depth_4" placeholder="N/S 4" value="{{ old('ns_depth_4', $reminder->tyreDepth->ns_depth_4 ?? $lastTyreDepth->ns_depth_4 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->ns_depth_4 ?? null))
                                 max="{{ $lastTyreDepth->ns_depth_4 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_ns_depth_4">
                                 Value exceeds the maximum allowed (<span id="max_ns_depth_4">{{ $lastTyreDepth?->ns_depth_4 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="ns_depth_6" id="ns_depth_6" placeholder="N/S 6" value="{{ old('ns_depth_6', $reminder->tyreDepth->ns_depth_6 ?? $lastTyreDepth->ns_depth_6 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->ns_depth_6 ?? null))
                                 max="{{ $lastTyreDepth->ns_depth_6 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_ns_depth_6">
                                 Value exceeds the maximum allowed (<span id="max_ns_depth_6">{{ $lastTyreDepth?->ns_depth_6 ?? 0 }}</span>).
                                 </small>
                              </div>
                           </div>
                        </div>
                        <!-- O/S Section -->
                        <div class="mt-4">
                           <label for="os_depth" class="form-label"><strong>O/S</strong></label>
                           <div class="row">
                              <!-- First Row: O/S 1, O/S 2, O/S 3, O/S 5 -->
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="os_depth_1" id="os_depth_1" placeholder="O/S 1" value="{{ old('os_depth_1', $reminder->tyreDepth->os_depth_1 ?? $lastTyreDepth->os_depth_1 ?? '') }}" step="0.1" min="0"
                                @if(!is_null($lastTyreDepth?->os_depth_1 ?? null))
                                   max="{{ $lastTyreDepth->os_depth_1 }}"
                                @endif
                                @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_os_depth_1">
                                 Value exceeds the maximum allowed (<span id="max_os_depth_1">{{ $lastTyreDepth?->os_depth_1 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="os_depth_2" id="os_depth_2" placeholder="O/S 2" value="{{ old('os_depth_2', $reminder->tyreDepth->os_depth_2 ?? $lastTyreDepth->os_depth_2 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->os_depth_2 ?? null))
                                 max="{{ $lastTyreDepth->os_depth_2 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_os_depth_2">
                                 Value exceeds the maximum allowed (<span id="max_os_depth_2">{{ $lastTyreDepth?->os_depth_2 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="os_depth_3" id="os_depth_3" placeholder="O/S 3" value="{{ old('os_depth_3', $reminder->tyreDepth->os_depth_3 ?? $lastTyreDepth->os_depth_3 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->os_depth_3 ?? null))
                                 max="{{ $lastTyreDepth->os_depth_3 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_os_depth_3">
                                 Value exceeds the maximum allowed (<span id="max_os_depth_3">{{ $lastTyreDepth?->os_depth_3 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="os_depth_5" id="os_depth_5" placeholder="O/S 5" value="{{ old('os_depth_5', $reminder->tyreDepth->os_depth_5 ?? $lastTyreDepth->os_depth_5 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->os_depth_5 ?? null))
                                 max="{{ $lastTyreDepth->os_depth_5 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_os_depth_5">
                                 Value exceeds the maximum allowed (<span id="max_os_depth_5">{{ $lastTyreDepth?->os_depth_5 ?? 0 }}</span>).
                                 </small>
                              </div>
                           </div>
                           <!-- Second Row: O/S 4, O/S 6 -->
                           <div class="row">
                              <div class="col-md-2 offset-md-4 mb-2">
                                 <input type="number" class="form-control" name="os_depth_4" id="os_depth_4" placeholder="O/S 4" value="{{ old('os_depth_4', $reminder->tyreDepth->os_depth_4 ?? $lastTyreDepth->os_depth_4 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->os_depth_4 ?? null))
                                 max="{{ $lastTyreDepth->os_depth_4 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_os_depth_4">
                                 Value exceeds the maximum allowed (<span id="max_os_depth_4">{{ $lastTyreDepth?->os_depth_4 ?? 0 }}</span>).
                                 </small>
                              </div>
                              <div class="col-md-2 mb-2">
                                 <input type="number" class="form-control" name="os_depth_6" id="os_depth_6" placeholder="O/S 6" value="{{ old('os_depth_6', $reminder->tyreDepth->os_depth_6 ?? $lastTyreDepth->os_depth_6 ?? '') }}" step="0.1" min="0"
                                 @if(!is_null($lastTyreDepth?->os_depth_6 ?? null))
                                 max="{{ $lastTyreDepth->os_depth_6 }}"
                              @endif
                              @if($reminder->status === 'Completed') readonly @endif
                                    oninput="validateInput(this)">
                                 <small class="text-danger d-none" id="error_os_depth_6">
                                 Value exceeds the maximum allowed (<span id="max_os_depth_6">{{ $lastTyreDepth?->os_depth_6 ?? 0 }}</span>).
                                 </small>
                              </div>
                           </div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="tyre_depth_comment">{{ __('Tyre Depth Comment') }}</label>
                            <textarea class="form-control" name="tyre_depth_comment" id="tyre_depth_comment"
                                      rows="3" placeholder="Enter Tyre Depth Comment" @if($reminder->status === 'Completed') readonly @endif>{{ old('tyre_depth_comment', $reminder->tyre_depth_comment) }} </textarea>

                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-xxl-5" style="width: 100%;">
                  <div class="card report_card total_amount_card">
                     <div class="card-body pt-0" style="margin-top: 13px;">
                        <!-- Brake System Section -->
                        <div class="mt-4">
                           <!-- First Part: Service -->
                           <div class="row mb-3">
                              <div class="col-md-4 mb-2">
                                 <label for="service">Brake Test Result</label>
                                 <input type="text" class="form-control" name="service" id="service" value="Service" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <label for="service_pass_value">Pass Value</label>
                                 <input type="text" class="form-control" name="service_pass_value" id="service_pass_value" value="50% GVW" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <label for="service_test_value">Test Value</label>
                                 <input type="number" class="form-control" name="service_test_value" id="service_test_value" placeholder="Enter Test Value" value="{{ old('service_test_value', $reminder->service_test_value) }}" step="0.1" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                           </div>
                           <!-- Second Part: Secondary (First) -->
                           <div class="row mb-3">
                              <div class="col-md-4 mb-2">
                                 <input type="text" class="form-control" name="secondary_1" id="secondary_1" value="Secondary" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <input type="text" class="form-control" name="secondary_1_pass_value" id="secondary_1_pass_value" value="25% GVW" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <input type="number" class="form-control" name="secondary_1_test_value" id="secondary_1_test_value" placeholder="Enter Test Value" value="{{ old('secondary_1_test_value', $reminder->secondary_1_test_value) }}" step="0.1" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                           </div>
                           <!-- Third Part: Secondary (Second) -->
                           <div class="row mb-3">
                              <div class="col-md-4 mb-2">
                                 <input type="text" class="form-control" name="secondary_2" id="secondary_2" value="Secondary" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <input type="text" class="form-control" name="secondary_2_pass_value" id="secondary_2_pass_value" value="25% GVW" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <input type="number" class="form-control" name="secondary_2_test_value" id="secondary_2_test_value" placeholder="Enter Test Value" value="{{ old('secondary_2_test_value', $reminder->secondary_2_test_value) }}" step="0.1" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                           </div>
                           <!-- Fourth Part: Parking -->
                           <div class="row mb-3">
                              <div class="col-md-4 mb-2">
                                 <input type="text" class="form-control" name="parking" id="parking" value="Parking" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <input type="text" class="form-control" name="parking_pass_value" id="parking_pass_value" value="16% GVW" readonly style="background-color: transparent; border: none; color: black;">
                              </div>
                              <div class="col-md-4 mb-2">
                                 <input type="number" class="form-control" name="parking_test_value" id="parking_test_value" placeholder="Enter Test Value" value="{{ old('parking_test_value', $reminder->parking_test_value) }}" step="0.1" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-xxl-5" style="width: 100%;">
                  <div class="card report_card total_amount_card">
                     <div class="card-body pt-0" style="margin-top: 13px;">
                        <!-- Cost Section -->
                        <div class="mt-4">
                           <label for="cost" class="form-label"><strong>Cost</strong></label>
                           <!-- Parts, Labour, and Tyre Cost -->
                           <div class="row mb-3">
                              <div class="col-md-4 mb-2">
                                 <label for="parts">Parts Cost</label>
                                 <input type="number" class="form-control" name="parts" id="parts" placeholder="Enter Parts Cost" value="{{ old('parts_cost', $reminder->parts_cost) }}" step="0.01" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                              <div class="col-md-4 mb-2">
                                 <label for="labour">Labour Cost</label>
                                 <input type="number" class="form-control" name="labour" id="labour" placeholder="Enter Labour Cost" value="{{ old('labour_cost', $reminder->labour_cost) }}" step="0.01" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                              <div class="col-md-4 mb-2">
                                 <label for="tyre_cost">Tyre Cost</label>
                                 <input type="number" class="form-control" name="tyre_cost" id="tyre_cost" placeholder="Enter Tyre Cost" value="{{ old('tyre_cost', $reminder->tyre_cost) }}" step="0.01" min="0" @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                           </div>
                           <!-- Total Cost -->
                           <div class="row mb-3">
                              <div class="col-md-6 mb-2">
                                 <label for="total_cost">Total Cost</label>
                                 <input type="number" class="form-control" name="total_cost" id="total_cost" value="{{ old('parts_cost', $reminder->total_cost) }}" readonly>
                              </div>
                           </div>
                           <!-- Type of Service -->
                           <div class="row mb-3">
                              <div class="col-md-12 mb-2">
                                 <label for="type_of_service">Type of Service</label>
                                 <input type="text" class="form-control" name="type_of_service" id="type_of_service" placeholder="Enter Type of Service" value="{{ old('type_of_service', $reminder->type_of_service) }}"  @if($reminder->status === 'Completed') readonly @endif>
                              </div>
                           </div>

                           <!-- Confirmation Comment Input -->
<div class="form-group mt-3">
    <label for="confirmation_comment">{{ __('Confirmation Comment') }}</label>
    <textarea class="form-control" name="confirmation_comment" id="confirmation_comment"
                                      rows="3" placeholder="Enter confirmation comment"
                                      @if($reminder->status === 'Completed') readonly @endif>{{ old('confirmation_comment', $reminder->confirmation_comment) }}</textarea>
                           </div>



                        </div>
                     </div>
                  </div>
               </div>
               <div class="d-flex justify-content-end mt-4">
                    @if ($reminder->status === 'Completed')
                        <button type="button" class="btn btn-primary"  disabled>You have already completed the form</button>
                    <!--    <div style="margin-left: 1%;">-->
                    <!--    <a href="{{ route('planner.history.show', $reminder->id) }}"  class="btn btn-primary">Go to Attachment</a>-->
                    <!--</div>-->
                    @else
                                            @if(\Carbon\Carbon::parse($reminder->next_reminder_date)->lte(\Carbon\Carbon::today()) && $firstPendingReminder && $reminder->id === $firstPendingReminder->id)

                        <button type="button" class="btn btn-primary" id="saveButton">Save</button>
                          @endif
                    @endif
            </div>

               <!-- Modal for Confirmation -->
               <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h5 class="modal-title" id="confirmModalLabel">Confirm Save</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to save?</p>
                        </div>
                        <div class="modal-footer">
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                           <button type="button" id="confirmSubmit" class="btn btn-primary">Confirm Save</button>
                        </div>
                     </div>
                  </div>
               </div>

         </form>
         </div>
      </div>
   </div>
</div>
</div>
@endif

@if ($reminder->status === 'Completed')
<div class="row">
    <div class="col-xl-9" style="width: 100%">
        <div id="useradd-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Planner Attachments') }}</h5>
                </div>
                <div class="card-body">
                    <div class="scrollbar-inner">
                        <div class="card-wrapper p-3 lead-common-box">
                            @if($reminder->fileUploads->isNotEmpty())
                                @foreach($reminder->fileUploads as $file)
                                    <div class="card mb-3 border shadow-none">
                                        <div class="px-3 py-3">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h6 class="text-sm mb-0">
                                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">
                                                            {{ basename($file->file_path) }}
                                                        </a>
                                                    </h6>
                                                    <p class="card-text small text-muted">
                                                        @if(!empty($file->file_path) && file_exists(storage_path('app/public/' . $file->file_path)))
                                                            {{ number_format(\File::size(storage_path('app/public/' . $file->file_path)) / 1048576, 2) . ' MB' }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="col-auto actions">
                                                    <!-- Download Button -->
                                                    <div class="action-btn bg-warning">
                                                        <a href="{{ asset('storage/' . $file->file_path) }}"
                                                           class="btn btn-sm d-inline-flex align-items-center"
                                                           download="{{ basename($file->file_path) }}"
                                                           data-bs-toggle="tooltip" title="Download">
                                                            <span class="text-white"><i class="ti ti-download"></i></span>
                                                        </a>
                                                    </div>
                                                    <!-- View Button -->
                                                    <div class="action-btn bg-warning">
                                                        <a href="{{ asset('storage/' . $file->file_path) }}"
                                                           class="btn btn-sm d-inline-flex align-items-center mx-2"
                                                           target="_blank" data-bs-toggle="tooltip" title="View">
                                                            <span class="text-white"><i class="ti ti-eye"></i></span>
                                                        </a>
                                                    </div>
                                                      <div class="action-btn bg-info ms-2">
        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('planner.document.name.edit', $file->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Rename') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No attachments available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- Fleet Reminders Section -->
    {{--  @if($reminders->isNotEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Action') }}</th>
                                        <th>{{ __('Reminder Date') }}</th>
                                        <th>{{ __('Status') }}</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $editIconShown = false;
                                    @endphp
                                    @foreach($reminders as $reminder)
                                        <tr>
                                            <td class="text-center">
                                                @if($reminder->status === 'Pending' && !$editIconShown)
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                           data-url="{{ route('reminder.edit', $reminder->id) }}"
                                                           data-ajax-popup="true"
                                                           data-size="md"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}"
                                                           data-title="{{ __('Edit Type') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    @php
                                                        $editIconShown = true;
                                                    @endphp
                                                @endif

                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['reminder.destroy', $reminder->id]]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($reminder->next_reminder_date)->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ $reminder->status }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <p>{{ __('No reminders available.') }}</p>
    @endif  --}}
@endsection
