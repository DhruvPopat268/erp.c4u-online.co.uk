@extends('layouts.admin')

@section('page-title')
    {{__('Training')}}
@endsection

@push('css-page')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Training')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @can('create trainings')
            <a href="#" data-size="md" data-url="{{ route('training.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Assign Training')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
@if (session('errorArray'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="d-flex justify-content-between align-items-center">
            <span>Skipped Records:</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </h5>
        <ul>
            @foreach (session('errorArray') as $error)
                <li>
                    <strong>Error:</strong> {{ $error['error'] }}<br>
                    {{--  <strong>Data:</strong> {{ implode(', ', $error['data']) }}  --}}
                </li>
            @endforeach
        </ul>
    </div>
    @php
        // Remove the session data so it doesn't show up after reload
        session()->forget('errorArray');
    @endphp
@endif

   <div class="row">
       <div class="row" style="margin-bottom: 10px;margin-top:10px;">
            <div class="col-12">
                 Filter Form

                <form method="GET" action="{{ route('training.index') }}">
                    <div class="row">
                    @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                        <div class="col-md-4">
                            <label for="company_id">{{__('Filter by Company')}}</label>
                            <select name="company_id" id="company_id" class="form-control">
                                <option value="">{{__('All Companies')}}</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ strtoupper($company->name) }}
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
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                            <a href="{{ route('training.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
<div class="col-xl-12">
    <div class="row">
        <!-- Total Complete Training -->
        <div class="col-lg-4 col-6">
                <a href="{{ route('currentYearCompleted',['company_id' => request('company_id'),'depot_id' => request('depot_id'),
        'group_id' => request('group_id'),]) }}" class="card" style="text-decoration: none;">
                <div class="card-body" style="min-height: 180px;background-color: #abddab;border-radius: 12px;">
                    <div class="theme-avtar bg-primary">
                        <i class='fas fa-chalkboard-teacher' style='font-size:15px'></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Total Complete Training') }}</h6>
                    <h3 class="mb-0">{{ $completestatusCount }}</h3>
                </div>
            </a>
        </div>
        <!-- Total Pending Training -->
        <div class="col-lg-4 col-6">
                <a href="{{ route('currentYearPending',['company_id' => request('company_id'),'depot_id' => request('depot_id'),
        'group_id' => request('group_id'),]) }}" class="card" style="text-decoration: none;">
                <div class="card-body" style="min-height: 180px;background-color: #fbcf7d;border-radius: 12px;">
                    <div class="theme-avtar bg-primary">
                        <i class='fas fa-chalkboard-teacher' style='font-size:15px'></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Total Pending Training') }}</h6>
                    <h3 class="mb-0">{{ $PendingstatusCount }}</h3>
                </div>
            </a>
        </div>
        <!-- Total Decline Training -->
        <div class="col-lg-4 col-6">
                <a href="{{ route('currentYearDecline',['company_id' => request('company_id'),'depot_id' => request('depot_id'),
        'group_id' => request('group_id'),]) }}" class="card" style="text-decoration: none;">
                <div class="card-body" style="min-height: 180px;background-color: #ff00005c;border-radius: 12px;">
                    <div class="theme-avtar bg-primary">
                        <i class='fas fa-chalkboard-teacher' style='font-size:15px'></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Total Decline Training') }}</h6>
                    <h3 class="mb-0">{{ $DeclinestatusCount }}</h3>
                </div>
            </a>
        </div>
    </div>
</div>


        <div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-6">
                    <h5>{{ __('Training Planner') }}</h5>
                </div>
                <div class="col-lg-6">
                    @if (isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on')
                        <select class="form-control" name="calendar_type" id="calendar_type" style="float: right; width: 150px;" onchange="get_data()">
                            <option value="google_calendar">{{ __('Google Calendar') }}</option>
                            <option value="local_calendar" selected="true">{{ __('Local Calendar') }}</option>
                        </select>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body" style="overflow-x: auto; overflow-y: auto;">
            <div id="calendar" style="min-width: 600px; min-height: 400px;"></div>
        </div>
    </div>
</div>


<!--        <div class="col-lg-4">-->
<!--            <div class="card">-->
<!--                <div class="card-body task-calendar-scroll">-->
<!--                    <h4 class="mb-4">{{ __('Training Planner Of Current Year') }}</h4>-->
<!--                    <ul class="event-cards list-group list-group-flush mt-3 w-100">-->
<!--                        {{--  @forelse($trainings as $training)  --}}-->
<!--<a href="{{ route('currentYearCompleted') }}" style="text-decoration: none; color: inherit;">-->
<!--    <li class="list-group-item card mb-3" style="padding: 20px; background-color:#abddab;">-->
<!--        <div class="row align-items-center justify-content-between">-->
<!--            <div class="col-auto mb-3 mb-sm-0">-->
<!--                <div class="d-flex align-items-center">-->
<!--                    <div class="theme-avtar bg-primary">-->
<!--                        <i class='fas fa-eye'></i>-->
<!--                    </div>-->
<!--                    <div class="ms-3 fc-event-title-container">-->
<!--                        <h3 class="m-0 fc-event-title text-primary">-->
<!--                            {{ __('Total Complete Training') }}-->
<!--                        </h3>-->
<!--                        <h2 class="text-muted">-->
<!--                            {{ $completestatusCount }}-->
<!--                        </h2>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </li>-->
<!--</a>-->


<!--                            <a href="{{ route('currentYearPending') }}" style="text-decoration: none; color: inherit;">-->
<!--    <li class="list-group-item card mb-3" style="padding: 20px; background-color:#fbcf7d;">-->
<!--        <div class="row align-items-center justify-content-between">-->
<!--            <div class="col-auto mb-3 mb-sm-0">-->
<!--                <div class="d-flex align-items-center">-->
<!--                    <div class="theme-avtar bg-primary">-->
<!--                        <i class='fas fa-eye'></i>-->
<!--                    </div>-->
<!--                    <div class="ms-3 fc-event-title-container">-->
<!--                        <h3 class="m-0 fc-event-title text-primary">-->
<!--                            {{ __('Total Pending Training') }}-->
<!--                        </h3>-->
<!--                        <h2 class="text-muted">-->
<!--                            {{ $PendingstatusCount }}-->
<!--                        </h2>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </li>-->
<!--</a>-->

<!--<a href="{{ route('currentYearDecline') }}" style="text-decoration: none; color: inherit;">-->
<!--    <li class="list-group-item card mb-3" style="padding: 20px; background-color:#ff00005c;">-->
<!--        <div class="row align-items-center justify-content-between">-->
<!--            <div class="col-auto mb-3 mb-sm-0">-->
<!--                <div class="d-flex align-items-center">-->
<!--                    <div class="theme-avtar bg-primary">-->
<!--                        <i class='fas fa-eye'></i>-->
<!--                    </div>-->
<!--                    <div class="ms-3 fc-event-title-container">-->
<!--                        <h3 class="m-0 fc-event-title text-primary">-->
<!--                            {{ __('Total Decline Training') }}-->
<!--                        </h3>-->
<!--                        <h2 class="text-muted">-->
<!--                            {{ $DeclinestatusCount }}-->
<!--                        </h2>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </li>-->
<!--</a>-->

<!--                        {{--  @empty-->
<!--                            <p class="text-dark text-center">{{ __('No Data Found') }}</p>-->
<!--                        @endforelse  --}}-->
<!--                    </ul>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

    </div>

    <!-- Modal for training details -->
    <div class="modal fade" id="trainingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="trainingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trainingDetailsModalLabel">{{__('Training Details')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>{{__('Training Type:')}}</strong> <span id="trainingType"></span></p>
                    <p><strong>{{__('Training Course:')}}</strong> <span id="trainingCourse"></span></p>
                    <p><strong>{{__('From Date:')}}</strong> <span id="fromDate"></span></p>
                    <p><strong>{{__('To Date:')}}</strong> <span id="toDate"></span></p>
                    <p><strong>{{__('Status:')}}</strong> <span id="status"></span></p> <!-- New status line -->
                    <p><strong>{{__('Drivers:')}}</strong> <span id="drivers"></span></p>
                    <p><strong>{{__('Company Name:')}}</strong> <span id="companyName"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var events = @json($events);

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
               eventClick: function(info) {
                    function formatDate(date) {
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        return `${day}/${month}/${year}`;
                    }

                    // Set the training details in the modal
                    document.getElementById('trainingType').textContent = info.event.extendedProps.description;
                    document.getElementById('trainingCourse').textContent = info.event.title;

                    // Format the start date (From Date)
                    const startDate = formatDate(info.event.start);
                    document.getElementById('fromDate').textContent = startDate; // Set From Date

                    // Subtract one day from the end date (To Date)
                    let endDate = info.event.end || info.event.start; // Use start date if end date is null
                    endDate = new Date(endDate); // Create a Date object
                    endDate.setDate(endDate.getDate() - 1); // Subtract 1 day
                    const formattedEndDate = formatDate(endDate);

                    // Set To Date
                    document.getElementById('toDate').textContent = formattedEndDate;

                    // Set drivers and company details
                    document.getElementById('drivers').textContent = info.event.extendedProps.drivers.join(', ');
                    document.getElementById('companyName').textContent = info.event.extendedProps.company;

                    // Set and color the status
                    const statusElement = document.getElementById('status');
                    statusElement.textContent = info.event.extendedProps.status;

                    // Apply color based on status
                    if (info.event.extendedProps.status === 'Reassign') {
                        statusElement.style.color = 'red';
                    } else {
                        statusElement.style.color = '';
                    }

                    // Show the modal
                    $('#trainingDetailsModal').modal('show');
                }
            });
            calendar.render();
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
@endpush

