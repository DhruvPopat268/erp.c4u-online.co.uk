@extends('layouts.admin')
@section('page-title')
    @if ($reminder->fleet->planner_type === 'Brake Test Due')
        {{ __('Edit Brake Test Details') }}
    @else ($reminder->fleet->planner_type === 'PMI Due')
        {{ __('Enter Inspection Information') }}
    @endif
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
    <li class="breadcrumb-item"><a href="{{ route('fleet.index') }}">{{ __('Forward Planner') }}</a></li>
<li class="breadcrumb-item">{{ __('Form') }}</li>
@endsection
@section('action-btn')
@can('create depot')
<div class="float-end">
</div>
@endcan
@endsection
@section('content')
<div class="row">
<div class="col-lg-12">
    <div>

        <div class="card-body">
            <form id="formToSubmit" action="{{ route('fleet.update-status', $reminder->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="col-xxl-5" style="width: 100%;">
                    <div class="card report_card total_amount_card">
                        <div class="card-body pt-0" style="margin-top: 13px;">

                            <!-- Odometer Reading -->
                            <div class="form-group mt-3">
                                <label for="odometer_reading">Odometer Reading</label>
                                <input type="number" class="form-control" name="odometer_reading" id="odometer_reading" placeholder="Enter Odometer Reading" value="{{ old('odometer_reading') }}" step="0.1" min="0">
                            </div>

                <!-- Multiple File Upload -->
                <div class="form-group mt-3">
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
                                value="{{ old('ns_depth_1', $lastTyreDepth->ns_depth_1 ?? '') }}"
                                step="0.1" min="0"
                                max="{{ $lastTyreDepth->ns_depth_1 ?? 0 }}"
                                oninput="validateInput(this)">
                                <small class="text-danger d-none" id="error_ns_depth_1">
                                 Value exceeds the maximum allowed (<span id="max_ns_depth_1">{{ $lastTyreDepth->ns_depth_1 ?? 0 }}</span>).
                             </small>
                         </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="ns_depth_2" id="ns_depth_2" placeholder="N/S 2" value="{{ old('ns_depth_2', $lastTyreDepth->ns_depth_2 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->ns_depth_2 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_ns_depth_2">
                                        Value exceeds the maximum allowed (<span id="max_ns_depth_2">{{ $lastTyreDepth->ns_depth_2 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="ns_depth_3" id="ns_depth_3" placeholder="N/S 3" value="{{ old('ns_depth_3', $lastTyreDepth->ns_depth_3 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->ns_depth_3 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_ns_depth_3">
                                        Value exceeds the maximum allowed (<span id="max_ns_depth_3">{{ $lastTyreDepth->ns_depth_3 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="ns_depth_5" id="ns_depth_5" placeholder="N/S 5" value="{{ old('ns_depth_5', $lastTyreDepth->ns_depth_5 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->ns_depth_5 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_ns_depth_5">
                                        Value exceeds the maximum allowed (<span id="max_ns_depth_5">{{ $lastTyreDepth->ns_depth_5 ?? 0 }}</span>).
                                    </small>
                            </div>
                        </div>
                        <!-- Second Row: N/S 4, N/S 6 -->
                        <div class="row">
                            <div class="col-md-2 offset-md-4 mb-2">
                                <input type="number" class="form-control" name="ns_depth_4" id="ns_depth_4" placeholder="N/S 4" value="{{ old('ns_depth_4', $lastTyreDepth->ns_depth_4 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->ns_depth_4 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_ns_depth_4">
                                        Value exceeds the maximum allowed (<span id="max_ns_depth_4">{{ $lastTyreDepth->ns_depth_4 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="ns_depth_6" id="ns_depth_6" placeholder="N/S 6" value="{{ old('ns_depth_6', $lastTyreDepth->ns_depth_6 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->ns_depth_6 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_ns_depth_6">
                                        Value exceeds the maximum allowed (<span id="max_ns_depth_6">{{ $lastTyreDepth->ns_depth_6 ?? 0 }}</span>).
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
                                <input type="number" class="form-control" name="os_depth_1" id="os_depth_1" placeholder="O/S 1" value="{{ old('os_depth_1', $lastTyreDepth->os_depth_1 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->os_depth_1 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_os_depth_1">
                                        Value exceeds the maximum allowed (<span id="max_os_depth_1">{{ $lastTyreDepth->os_depth_1 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="os_depth_2" id="os_depth_2" placeholder="O/S 2" value="{{ old('os_depth_2', $lastTyreDepth->os_depth_2 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->os_depth_2 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_os_depth_2">
                                        Value exceeds the maximum allowed (<span id="max_os_depth_2">{{ $lastTyreDepth->os_depth_2 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="os_depth_3" id="os_depth_3" placeholder="O/S 3" value="{{ old('os_depth_3', $lastTyreDepth->os_depth_3 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->os_depth_3 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_os_depth_3">
                                        Value exceeds the maximum allowed (<span id="max_os_depth_3">{{ $lastTyreDepth->os_depth_3 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="os_depth_5" id="os_depth_5" placeholder="O/S 5" value="{{ old('os_depth_5', $lastTyreDepth->os_depth_5 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->os_depth_5 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_os_depth_5">
                                        Value exceeds the maximum allowed (<span id="max_os_depth_5">{{ $lastTyreDepth->os_depth_5 ?? 0 }}</span>).
                                    </small>
                            </div>
                        </div>
                        <!-- Second Row: O/S 4, O/S 6 -->
                        <div class="row">
                            <div class="col-md-2 offset-md-4 mb-2">
                                <input type="number" class="form-control" name="os_depth_4" id="os_depth_4" placeholder="O/S 4" value="{{ old('os_depth_4', $lastTyreDepth->os_depth_4 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->os_depth_4 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_os_depth_4">
                                        Value exceeds the maximum allowed (<span id="max_os_depth_4">{{ $lastTyreDepth->os_depth_4 ?? 0 }}</span>).
                                    </small>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" class="form-control" name="os_depth_6" id="os_depth_6" placeholder="O/S 6" value="{{ old('os_depth_6', $lastTyreDepth->os_depth_6 ?? '') }}" step="0.1" min="0"
                                max="{{ $lastTyreDepth->os_depth_6 ?? 0 }}"
                                       oninput="validateInput(this)">
                                       <small class="text-danger d-none" id="error_os_depth_6">
                                        Value exceeds the maximum allowed (<span id="max_os_depth_6">{{ $lastTyreDepth->os_depth_6 ?? 0 }}</span>).
                                    </small>
                            </div>
                        </div>
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
                                <input type="number" class="form-control" name="service_test_value" id="service_test_value" placeholder="Enter Test Value" step="0.1" min="0">
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
                                <input type="number" class="form-control" name="secondary_1_test_value" id="secondary_1_test_value" placeholder="Enter Test Value" step="0.1" min="0">
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
                                <input type="number" class="form-control" name="secondary_2_test_value" id="secondary_2_test_value" placeholder="Enter Test Value" step="0.1" min="0">
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
                                <input type="number" class="form-control" name="parking_test_value" id="parking_test_value" placeholder="Enter Test Value" step="0.1" min="0">
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
                                <input type="number" class="form-control" name="parts" id="parts" placeholder="Enter Parts Cost" value="{{ old('parts') }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="labour">Labour Cost</label>
                                <input type="number" class="form-control" name="labour" id="labour" placeholder="Enter Labour Cost" value="{{ old('labour') }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="tyre_cost">Tyre Cost</label>
                                <input type="number" class="form-control" name="tyre_cost" id="tyre_cost" placeholder="Enter Tyre Cost" value="{{ old('tyre_cost') }}" step="0.01" min="0">
                            </div>
                        </div>
                        <!-- Total Cost -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <label for="total_cost">Total Cost</label>
                                <input type="number" class="form-control" name="total_cost" id="total_cost" value="{{ old('total_cost') }}" readonly>
                            </div>
                        </div>
                        <!-- Type of Service -->
                        <div class="row mb-3">
                            <div class="col-md-12 mb-2">
                                <label for="type_of_service">Type of Service</label>
                                <input type="text" class="form-control" name="type_of_service" id="type_of_service" placeholder="Enter Type of Service">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 <div class="col-xxl-5" style="width: 100%;">
        <div class="card report_card total_amount_card">
            <div class="card-body pt-0" style="margin-top: 13px;">

                <!-- Comment Text Box -->
                <div class="form-group">
                    <label for="comment">Comment</label>
                    <textarea class="form-control" name="comment" id="comment" rows="4" placeholder="Enter your comments here...">{{ old('comment', $reminder->comment ?? '') }}</textarea>
                </div>

                            </div>
        </div>
    </div>


                    <button type="button" id="saveButton" class="btn btn-primary mt-4">Save</button>

<!-- Modal for Confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Save</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="confirmation_comment">Please provide a reason for saving:</label>
                <textarea class="form-control" id="confirmation_comment" name="confirmation_comment" rows="4" placeholder="Enter reason here..."></textarea>
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

@endsection
