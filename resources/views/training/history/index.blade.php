@extends('layouts.admin')


@section('page-title')
    {{ __('Driver Training Log') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Driver Training Log') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <!-- Optional: Add any additional action buttons here -->
    </div>
@endsection

@section('content')
<div class="row" style="margin-bottom: 10px;margin-top:10px;">
    <div class="col-12">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('training.history.index') }}">
            <div class="row">
                <!-- Company Filter: Only visible for 'company' and 'PTC manager' roles -->
                @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                    <div class="col-md-4">
                        <label for="company_id">{{__('Filter by Company')}}</label>
                        <select name="company_id" id="company_id" class="form-control">
                            <option value="">{{__('All Companies')}}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                 <div class="col-md-4">
                        <label for="depot_id">{{__('Filter by Depot')}}</label>

                        <select name="depot_id" id="depot_id" class="form-control">

                            @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                            <option value="">{{__('Select a Company First')}}</option>

                            @else

                            <option value="">{{__('All Depots')}}</option>

                            @foreach($depots as $depot)

                            <option value="{{ $depot->id }}" {{ request('depot_id') == $depot->id ? 'selected' : '' }}>
                                {{ strtoupper($depot->name) }}
                            </option>

                            @endforeach

                            @endif

                        </select>

                    </div>


                    <div class="col-md-4">
                        <label for="group_id">{{__('Filter by Group')}}</label>

                        <select name="group_id" id="group_id" class="form-control">

                            @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                            <option value="">{{__('Select a Company First')}}</option>

                            @else

                            <option value="">{{__('All Groups')}}</option>

                            @foreach($groups as $group)

                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                {{ strtoupper($group->name) }}
                            </option>

                            @endforeach

                            @endif

                        </select>

                    </div>

                <!-- Training Type Filter: Visible for all roles -->
                <div class="col-md-4">
                    <label for="training_type_id">{{__('Filter by Training Type')}}</label>
                    <select name="training_type_id" id="training_type_id" class="form-control">
                        <option value="">{{__('All Training Types')}}</option>
                        @foreach($trainingTypes as $trainingType)
                            <option value="{{ $trainingType->id }}" {{ request('training_type_id') == $trainingType->id ? 'selected' : '' }}>
                                {{ $trainingType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Training Course Filter -->
                <div class="col-md-4">
                    <label for="training_course_id">{{__('Filter by Training Course')}}</label>
                    <select name="training_course_id" id="training_course_id" class="form-control">
                        <option value="">{{__('Select Training Course')}}</option>
                        <!-- Options will be populated based on training type selection -->
                        @foreach($trainingCourses as $course)
                            <option value="{{ $course->id }}" {{ request('training_course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mt-3">
                    <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                    <a href="{{ route('training.history.index') }}" class="btn btn-secondary">{{__('Reset Filter')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
    <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px; ">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Action') }}</th>
                                <th>{{ __('Driver Name') }}</th>
                                <th>{{ __('Training Type') }}</th>
                                <th>{{ __('Training Course') }}</th>
                                <th>{{ __('From Date') }}</th>
                                <th>{{ __('To Date') }}</th>
                                <th>{{ __('Company Name') }}</th>
                                <th>{{ __('Training Valid Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Signature') }}</th>
                                <th>{{ __('File') }}</th>
                                <th>{{ __('Created By') }}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($trainings as $training)
                                @foreach ($training->trainingDriverAssigns as $driverAssign)
                                <tr style="{{ $driverAssign->training->status === 'Reassign' ? 'background-color: #ff00002e;' : '' }}">
                                    <td>
                                            @php
                                                $currentDate = \Carbon\Carbon::now(); // Get the current date
                                                $toDate = \Carbon\Carbon::createFromFormat('Y-m-d', $driverAssign->training->to_date); // Assuming to_date is stored in Y-m-d format
                                                $status = $driverAssign->training->status; // Accessing the status value
                                            @endphp

                                            @if($currentDate > $toDate && $status !== 'Reassign')
                                                <button type="button" class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addTrainingModal{{ $driverAssign->id }}">
                                                    {{ __('Reassign') }}
                                                </button>
                                            @endif
                                        </td>



                                        <td style="text-align: left">{{ $driverAssign->driver->name ?? null }}</td>
                                        <td style="text-align: left">{{ $training->trainingType->name }}</td>
                                        <td style="text-align: left">{{ $training->trainingCourse->name ?? null }}</td>
                                        <td style="text-align: left">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $training->from_date)->format('d/m/Y') }}</td>
                                        <td style="text-align: left">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $training->to_date)->format('d/m/Y') }}</td>
                                        <td style="text-align: left">{{ $training->company->name }}</td>
                                        <td style="text-align: left">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $training->next_training_date)->format('d/m/Y') }}</td>
                                        <td style="text-align: left">{{ $driverAssign->status }}</td>
                                                                                <td style="text-align: center">
    <!-- Display the first 20 characters of reason -->
    <a href="#" data-toggle="modal" data-target="#reasonModal{{ $driverAssign->id }}">
        {{ Str::limit($driverAssign->reason, 20) }}
    </a>

    <!-- Modal to show the full reason -->
    <div class="modal fade" id="reasonModal{{ $driverAssign->id }}" tabindex="-1" role="dialog" aria-labelledby="reasonModalLabel{{ $driverAssign->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reasonModalLabel{{ $driverAssign->id }}">Reason Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Show the full reason here -->
    <pre style="white-space: pre-wrap;">{{ wordwrap($driverAssign->reason, 50, "\n", true) }}</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</td>



                                        <td style="text-align: center">
                                            @if ($driverAssign->signature)
                                                <a href="{{ asset('storage/' . $driverAssign->signature) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $driverAssign->signature) }}" alt="Signature" style="width: 100px; height: auto;"/>
                                                </a>
                                                @else
                                                <span>No Signature</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center">
                                            @if ($driverAssign->file)  <!-- Assuming 'file' is the attribute holding the file path -->
                                                <a href="{{ asset('storage/' . $driverAssign->file) }}" target="_blank">
                                                    <i class="fas fa-file-download" style="font-size: 24px; color: #007bff;" title="{{ __('Download File') }}"></i> <!-- Font Awesome icon -->
                                                </a>
                                            @else
                                                <span><i class="fas fa-file-download" style="font-size: 24px; color: #e93c07;"></i></span>
                                            @endif
                                        </td>
                                        <td style="text-align: left">{{ !empty($training->creator) ? $training->creator->username : '' }}</td>
                                    </tr>

                                    <!-- Add Training Modal -->
                                    <div class="modal fade" id="addTrainingModal{{ $driverAssign->id }}" tabindex="-1" aria-labelledby="addTrainingModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                {{ Form::open(['route' => 'traininghistory.store', 'method' => 'POST']) }}
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="addTrainingModalLabel">{{ __('Reassign Training for') }} {{ $driverAssign->driver->name ?? null }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">


                                                    <div class="form-group mt-3">
                                                        {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                                        {{ Form::date('from_date', $training->from_date, ['class' => 'form-control', 'required']) }}
                                                    </div>

                                                    <div class="form-group mt-3">
                                                        {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                                        {{ Form::date('to_date', $training->to_date, ['class' => 'form-control', 'required']) }}
                                                    </div>

                                                    <div class="form-group mt-3">
                                                        {{ Form::label('from_time', __('From Time'), ['class' => 'form-label']) }}
                                                        {{ Form::time('from_time', $training->from_time, ['class' => 'form-control']) }}
                                                    </div>
                                                    <div class="form-group mt-3">
                                                        {{ Form::label('to_time', __('To Time'), ['class' => 'form-label']) }}
                                                        {{ Form::time('to_time', $training->to_time, ['class' => 'form-control']) }}
                                                    </div>


                                                    <div class="form-group mt-3">
                                                        {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                                        {{ Form::textarea('description', $training->description, ['class' => 'form-control', 'required']) }}
                                                    </div>
                                                    {{ Form::hidden('training_type_id', $training->training_type_id) }}
                                                    {{ Form::hidden('training_course_id', $training->training_course_id) }}

                                                    {{ Form::hidden('driver_id', $driverAssign->driver->id ?? null) }}
                                                    {{ Form::hidden('companyName', $training->company->id) }}
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <input type="submit" value="{{ __('Reassign Training') }}" class="btn btn-primary">
                                                </div>
                                                {{ Form::close() }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Bootstrap CSS -->

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        function loadTrainingCourses(trainingTypeId) {
            $('#training_course_id').empty(); // Clear existing options
            $('#training_course_id').append('<option value="">{{__('Select Training Course')}}</option>'); // Default option

            if (trainingTypeId) {
                $.ajax({
                    url: '{{ route("bytraining.courses") }}', // Define the route for fetching courses
                    type: 'GET',
                    data: { training_type_id: trainingTypeId },
                    success: function(data) {
                        $.each(data, function(key, value) {
                            $('#training_course_id').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                        });

                        // Re-select the previously selected course
                        var selectedCourseId = '{{ request("training_course_id") }}';
                        if (selectedCourseId) {
                            $('#training_course_id').val(selectedCourseId);
                        }
                    }
                });
            } else {
                // If no training type is selected, reselect the previously selected course
                var selectedCourseId = '{{ request("training_course_id") }}';
                if (selectedCourseId) {
                    $('#training_course_id').val(selectedCourseId);
                }
            }
        }

        // Load training courses when the training type is selected
        $('#training_type_id').change(function() {
            loadTrainingCourses($(this).val());
        });

        // On page load, check if there's a selected training type and load courses
        var initialTrainingTypeId = '{{ request("training_type_id") }}';
        if (initialTrainingTypeId) {
            loadTrainingCourses(initialTrainingTypeId);
        }
         // Show loader when the Reassign Training button is clicked
        $(document).on('click', 'input[type="submit"][value="Reassign Training"]', function() {
            $('#loader').show(); // Show loader
        });
    });

        $(document).ready(function() {
        var selectedCompanyId = $('#company_id').length ? $('#company_id').val() : null;
        var selectedDepotId = '{{ request("depot_id") }}'; // Get selected depot from request
        var selectedGroupId = '{{ request("group_id") }}';

        function loadDepots(companyId, selectedDepotId = null) {
            if (companyId) {
                $('#depot_id').html('<option value="">{{__("Loading...")}}</option>');
                $('#depot_id').prop('disabled', true);

                $.ajax({
                    url: '{{ route("get.depots.by.company") }}'
                    , type: 'GET'
                    , data: {
                        company_id: companyId
                    }
                    , success: function(data) {
                        $('#depot_id').html('<option value="">{{__("Select Depot")}}</option>');

                        $.each(data, function(key, depot) {
                            let selected = selectedDepotId == depot.id ? 'selected' : '';
                            $('#depot_id').append('<option value="' + depot.id + '" ' + selected + '>' + depot.name.toUpperCase() + '</option>');
                        });

                        $('#depot_id').prop('disabled', false);
                    }
                });
            } else {
                $('#depot_id').html('<option value="">{{__("Select a Company First")}}</option>');
                $('#depot_id').prop('disabled', true);
            }
        }

        function loadGroups(companyId, selectedGroupId = null) {
            if (companyId) {
                $('#group_id').html('<option value="">{{__("Loading...")}}</option>');
                $('#group_id').prop('disabled', true);

                $.ajax({
                    url: '{{ route("get.driver.group.by.company") }}'
                    , type: 'GET'
                    , data: {
                        company_id: companyId
                    }
                    , success: function(data) {

                        $('#group_id').html('<option value="">{{__("Select Group")}}</option>');

                        $.each(data, function(key, group) {

                            let selected = selectedGroupId == group.id ? 'selected' : '';

                            $('#group_id').append(
                                '<option value="' + group.id + '" ' + selected + '>' + group.name.toUpperCase() + '</option>'
                            );

                        });

                        $('#group_id').prop('disabled', false);
                    }
                });
            } else {
                $('#group_id').html('<option value="">{{__("Select a Company First")}}</option>');
                $('#group_id').prop('disabled', true);
            }
        }

        // Load depots if a company is already selected (after form submission)
        if (selectedCompanyId) {
            loadDepots(selectedCompanyId, selectedDepotId);
        }

        if (selectedCompanyId) {
            loadGroups(selectedCompanyId, selectedGroupId);
        }

        // Handle company selection change
        $('#company_id').on('change', function() {
            var companyId = $(this).val();
            loadDepots(companyId);
        });

        $('#company_id').on('change', function() {
            var companyId = $(this).val();
            loadGroups(companyId);
        });

        // Ensure depot remains enabled after selection
        $('#depot_id').on('change', function() {
            if ($(this).val()) {
                $(this).prop('disabled', false);
            }
        });

        $('#group_id').on('change', function() {
            if ($(this).val()) {
                $(this).prop('disabled', false);
            }
        });
    });
</script>
