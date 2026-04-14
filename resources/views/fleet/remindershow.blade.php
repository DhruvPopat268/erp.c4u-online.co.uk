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
                                                   data-url="{{ route('other.reminder.edit', $reminder->id) }}"
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
 
     @php
    $specialTypes = ['Fridge Service', 'Fridge Calibration', 'Tail lift', 'Loler'];
    $isSpecialType = in_array($fleet->planner_type, $specialTypes);
@endphp

@if($isSpecialType)
    @php
        $editableReminder = $reminders->firstWhere('status', 'Pending');
    @endphp

    @if($editableReminder)
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
                                    <tr>
                                        <td class="text-center">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                   data-url="{{ route('other.reminder.edit', $editableReminder->id) }}"
                                                   data-ajax-popup="true"
                                                   data-size="md"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Edit') }}"
                                                   data-title="{{ __('Edit Type') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($editableReminder->next_reminder_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ $editableReminder->status }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@elseif($reminders->isNotEmpty())
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
                                @php $editIconShown = false; @endphp
                                @foreach($reminders as $reminder)
                                    <tr>
                                        <td class="text-center">
                                            @if($reminder->status === 'Pending' && !$editIconShown)
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                       data-url="{{ route('other.reminder.edit', $reminder->id) }}"
                                                       data-ajax-popup="true"
                                                       data-size="md"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}"
                                                       data-title="{{ __('Edit Type') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                @php $editIconShown = true; @endphp
                                            @endif
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
@endif

@endsection
