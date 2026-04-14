@extends('layouts.admin')
@section('page-title')
    {{__('Forward Planner')}}
@endsection

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

@push('script-page')

<script>
         <!--<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>-->
       <!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
       <!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>-->
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var events = @json($events); // Ensure you're passing the events correctly



        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
           height: 'auto', // Adjusts to fit container height
            contentHeight: 600, // Minimum height to display full month
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: events, // Pass the events here
            eventColor: '#3788d8', // Default color for events

            eventContent: function(arg) {
        // Use the Planner Type as the main title and the Registration Number below it
        var plannerType = arg.event.title; // Planner Type
        var registrationNumber = arg.event.extendedProps.registration_number; // Registration Number
                var statusIcon = arg.event.extendedProps.status_icon; // Registration Number

        return {
            html: `
                <div style="text-align: center;">
                                    <span>(${registrationNumber})</span><br/>
                    <strong>${plannerType}</strong>
                    <strong style="position: absolute; top: 0px; right: 5px;">${statusIcon ? statusIcon : ''}</strong>
                </div>
            `
        };
    },
            eventClick: function(info) {
                var eventTitle = info.event.title;

                // Get event details and other properties
                var eventStart = info.event.start;
                var eventStatus = info.event.extendedProps.status || 'Pending';
                var redirectUrl = info.event.extendedProps.redirectUrl;
                var eventId = info.event.id;
                var registrationNumber = info.event.extendedProps.registration_number;
                var companyName = info.event.extendedProps.company_name || 'N/A';
                var eventComment = info.event.extendedProps.comment || '';
                var eventOdometerReading = info.event.extendedProps.odometer_reading || '';
                var eventTotalCost = info.event.extendedProps.total_cost || '';

                var formattedStartDate = eventStart.toLocaleDateString('en-GB');
                var reminderRedirectUrl = info.event.extendedProps.reminderredirectUrl; // Extract reminder redirect URL
                var historyUrl = info.event.extendedProps.historyUrl;


                if (!['PMI Due', 'Brake Test Due'].includes(eventTitle)) {
                // Set event details in modal
                document.getElementById('eventTitle').innerText = eventTitle;
                document.getElementById('eventStart').innerText = formattedStartDate;
                document.getElementById('eventStatus').innerText = eventStatus;
                document.getElementById('registrationNumber').innerText = registrationNumber;
                document.getElementById('companyName').innerText = companyName;
                    {{--  // Handle the condition for hiding/showing fields based on event title
                if (eventTitle === 'PMI Due' || eventTitle === 'Brake Test Due') {
                    // Hide the Comment, Odometer, File Upload, and Cost Section fields
                    document.getElementById('eventComment').closest('div').style.display = 'none';
                    document.getElementById('eventOdometerReading').closest('div').style.display = 'none';
                    document.getElementById('eventFiles').closest('div').style.display = 'none';
                    document.querySelector('label[for="cost"]').closest('div').style.display = 'none';

                    // Hide the Save Changes button for PMI Due and Brake Test Due
                    document.getElementById('saveChangesButton').style.display = 'none';
                } else {
                    // Show the Comment, Odometer, File Upload, and Cost Section fields
                    document.getElementById('eventComment').closest('div').style.display = 'block';
                    document.getElementById('eventOdometerReading').closest('div').style.display = 'block';
                    document.getElementById('eventFiles').closest('div').style.display = 'block';
                    document.querySelector('label[for="cost"]').closest('div').style.display = 'block';

                    // Show the Save Changes button if it's not PMI Due or Brake Test Due
                    document.getElementById('saveChangesButton').style.display = 'inline-block';
                    }  --}}

                // Set other field values
                document.getElementById('eventComment').value = eventComment;
                document.getElementById('eventOdometerReading').value = eventOdometerReading;
                document.getElementById('total_cost').value = eventTotalCost;

                // Make fields read-only if status is "Completed"
                            if (eventStatus === 'Completed') {
                                document.getElementById('eventComment').readOnly = true;
                                document.getElementById('eventOdometerReading').readOnly = true;
                                document.getElementById('total_cost').readOnly = true;
                                document.getElementById('eventFiles').disabled = true;
                            } else {
                                document.getElementById('eventComment').readOnly = false;
                                document.getElementById('eventOdometerReading').readOnly = false;
                                document.getElementById('total_cost').readOnly = false;
                                document.getElementById('eventFiles').disabled = false;
                            }


                // Enable/Disable Save Changes button based on start date and status
                var currentDate = new Date();
                var saveChangesButton = document.getElementById('saveChangesButton');

                if (eventStatus === 'Completed' && eventStart <= currentDate) {
                    saveChangesButton.style.display = 'none';
                } else {
                    saveChangesButton.style.display = 'inline-block';
                }

                if (eventStart <= currentDate && eventStatus !== 'Completed') {
                    saveChangesButton.disabled = false;
                } else {
                    saveChangesButton.disabled = true;
                }

                 var reminderRedirectButton = document.getElementById('reminderRedirectButton');
if (eventStatus === 'Pending' && !['MOT', 'Road Tax'].includes(eventTitle)) {
            reminderRedirectButton.style.display = 'inline-block';
            reminderRedirectButton.href = reminderRedirectUrl;  // Set the reminder redirect URL
        } else {
            reminderRedirectButton.style.display = 'none';  // Hide the reminder button for other event titles
        }


                var reminderhistoryButton = document.getElementById('reminderhistoryButton');
       if (!['PMI Due', 'Brake Test Due'].includes(eventTitle) && eventStatus === 'Completed') {
            reminderhistoryButton.style.display = 'inline-block';
            reminderhistoryButton.href = historyUrl;
        } else {
            reminderhistoryButton.style.display = 'none';
        }


                document.getElementById('saveChangesButton').onclick = function() {
                    var updatedComment = document.getElementById('eventComment').value;
                    var updatedOdometerReading = document.getElementById('eventOdometerReading').value;
                    var updatedTotalCost = document.getElementById('total_cost').value;
                    var files = document.getElementById('eventFiles').files;

                    var formData = new FormData();
                    formData.append('comment', updatedComment);
                    formData.append('odometer_reading', updatedOdometerReading);
                    formData.append('total_cost', updatedTotalCost);

                    Array.from(files).forEach(function(file) {
                        formData.append('files[]', file);
                    });

                    fetch(`/update-event/${eventId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    }).then(response => response.json())
                      .then(data => {
                          if (data.success) {
                                      location.reload(); // Reload the page after success
                          } else {
                              alert('Failed to save changes.');
                          }
                      }).catch(error => {
                          console.error('Error:', error);
                          alert('An error occurred while saving changes.');
                      });
                };

                var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
                myModal.show();

                } else if (eventTitle === 'PMI Due' || eventTitle === 'Brake Test Due') {
                    // Open the redirect URL for PMI Due or Brake Test Due
                    window.location.href = redirectUrl;
                }
            }

        });

        calendar.render();
    });



      document.addEventListener('DOMContentLoaded', function () {
        // Get the company select and buttons
        const companySelect = document.getElementById('company_id');
        const exportButton = document.querySelector('.btn-export');
        const pdfButton = document.querySelector('.btn-pdf'); // Add this for the PDF button

        // Function to toggle button visibility based on company selection
        function toggleButtons() {
            if (companySelect.value) {
                exportButton.style.display = 'inline-block'; // Show Export button
                pdfButton.style.display = 'inline-block';    // Show PDF button
            } else {
                exportButton.style.display = 'none';         // Hide Export button
                pdfButton.style.display = 'none';           // Hide PDF button
            }
        }

        // Initial check when the page loads
        toggleButtons();

        // Add event listener to toggle button visibility when the company selection changes
        companySelect.addEventListener('change', toggleButtons);
    });

            function toggleExportInputs() {
        var filterType = document.getElementById('export_filter_type').value;
        var yearSelection = document.getElementById('export_year_selection');
        var dateSelection = document.getElementById('export_date_range_selection');
        var submitButton = document.getElementById('exportButton');

        yearSelection.style.display = 'none';
        dateSelection.style.display = 'none';
        submitButton.disabled = true;

        if (filterType === 'year') {
            yearSelection.style.display = 'block';
        } else if (filterType === 'date') {
            dateSelection.style.display = 'block';
        }
    }

    function toggleExportSubmitButton() {
        var filterType = document.getElementById('export_filter_type').value;
        var yearSelect = document.getElementById('exportYear');
        var fromDate = document.getElementById('export_from_date');
        var toDate = document.getElementById('export_to_date');
        var submitButton = document.getElementById('exportButton');

        if (filterType === 'year' && yearSelect.value !== "") {
            submitButton.disabled = false;
        } else if (filterType === 'date' && fromDate.value !== "" && toDate.value !== "") {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

        function toggleFilterInputs() {
            var filterType = document.getElementById('filter_type').value;
            var yearSelection = document.getElementById('year_selection');
            var dateSelection = document.getElementById('date_range_selection');
            var submitButton = document.getElementById('pdfButton');

            // Reset visibility of sections
            yearSelection.style.display = 'none';
            dateSelection.style.display = 'none';
            submitButton.disabled = true;

            if (filterType === 'year') {
                yearSelection.style.display = 'block';
            } else if (filterType === 'date') {
                dateSelection.style.display = 'block';
            }
        }

        function toggleSubmitButton() {
            var filterType = document.getElementById('filter_type').value;
            var yearSelect = document.getElementById('pdfYear');
            var fromDate = document.getElementById('from_date');
            var toDate = document.getElementById('to_date');
            var submitButton = document.getElementById('pdfButton');

            if (filterType === 'year' && yearSelect.value !== "") {
                submitButton.disabled = false;
            } else if (filterType === 'date' && fromDate.value !== "" && toDate.value !== "") {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

            $(document).ready(function() {
       var selectedCompanyId = $('#company_id').length ? $('#company_id').val() : null;
        var selectedDepotId = "{{ request()->get('depot_id') }}";
var selectedGroupId = "{{ request()->get('group_id') }}";
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
                    url: '{{ route("get.vehicle.group.by.company") }}'
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

        function loadVehicles(companyId, depotId, groupId, selectedVehicleId = null)
{
    $('#vehicle_id').html('<option value="">Loading...</option>');
    $('#vehicle_id').prop('disabled', true);

    $.ajax({
        url: '{{ route("get.vehicles.by.depot.group") }}',
        type: 'GET',
        data: {
            company_id: companyId,
            depot_id: depotId,
            group_id: groupId
        },

        success: function(data){

            $('#vehicle_id').html('<option value="">All Vehicles</option>');

            $.each(data,function(key,vehicle){

                let selected = selectedVehicleId == vehicle.id ? 'selected' : '';

                let name = vehicle.registrationNumber
                    ? vehicle.registrationNumber
                    : vehicle.vehicle_nick_name;

                $('#vehicle_id').append(
                    '<option value="'+vehicle.id+'" '+selected+'>'+name.toUpperCase()+'</option>'
                );
            });

            $('#vehicle_id').prop('disabled',false);
        }
    });
}

$('#depot_id, #group_id').on('change', function(){

    var companyId = $('#company_id').val();
    var depotId = $('#depot_id').val();
    var groupId = $('#group_id').val();

    loadVehicles(companyId, depotId, groupId);

});

var selectedVehicleId = '{{ request("vehicle_id") }}';

if(selectedCompanyId){
    loadVehicles(selectedCompanyId, selectedDepotId, selectedGroupId, selectedVehicleId);
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

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Forward Planner')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
         <a href="#" data-size="lg" data-url="{{ route('fleet.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Fleet')}}" data-title="{{__('Create Fleet')}}" class="btn btn-sm btn-primary">
    <i class="ti ti-plus" style="font-size: 28px;"></i>
</a>
        @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
        <button type="button" class="btn btn-success btn-export" data-bs-toggle="modal" data-bs-target="#exportModal" style="display:none;">
            Export
        </button>
        <button type="button" class="btn btn-success btn-pdf" data-bs-toggle="modal" data-bs-target="#pdfModal" style="display:none;">
            PDF
        </button>
        @else
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
            Export
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#pdfModal">
            PDF
        </button>
        @endif
    </div>

@endsection

@section('content')


    <div class="row">
         <div class="row" style="margin-bottom: 10px; margin-top: 10px;">
            <div class="col-12">
                <form method="GET" action="{{ route('fleet.index') }}">
                    <div class="row">
                        @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                        <div class="col-md-4">
                            <label for="company_id">{{__('Filter by Company')}}</label>
                            <select name="company_id" id="company_id" class="form-control" onchange="this.form.submit()">
                                <option value="">{{__('All Companies')}}</option>
                                @foreach($companies->sortBy('name') as $company)
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
                            <label for="vehicle_id">{{__('Filter by Vehicle')}}</label>
                            <select name="vehicle_id" id="vehicle_id" class="form-control">
                                <option value="">{{__('All Vehicles')}}</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
{{ isset($vehicle->vehicle) && $vehicle->vehicle->vehicle_type == 'Trailer' ? $vehicle->vehicle_nick_name : $vehicle->registrationNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="planner_type">{{__('Filter by Planner Type')}}</label>
                            <select name="planner_type" id="planner_type" class="form-control">
                                @foreach($plannerTypes as $key => $type)
                                    <option value="{{ $key }}" {{ request('planner_type') == $key ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mt-3">
                            <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                            <a href="{{ route('fleet.index') }}" class="btn btn-secondary">{{__('Reset Filter')}}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
 <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Forward Planner') }}</h5>
                        </div>

                        <div class="col-lg-6">
                            @if (isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on')
                                <select class="form-control" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="google_calendar">{{__('Google Calendar')}}</option>
                                    <option value="local_calendar" selected="true">{{__('Local Calendar')}}</option>
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
                 <div class="card-body">
                    <!-- Add a container for the calendar -->
                    <div id="calendar-container" style="max-height: 700px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                        <div id='calendar' style="min-height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    <!-- Modal for event details -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="eventModalLabel">Edit <span id="eventTitle"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!--<p><strong>Title: </strong><span id="eventTitle"></span></p>-->
            <p><strong>Company Name: </strong><span id="companyName"></span></p>

            <p><strong>Start: </strong><span id="eventStart"></span></p>
            <p><strong>Status: </strong><span id="eventStatus"></span></p>
                        <p><strong>Vehicle Registration Number: </strong><span id="registrationNumber"></span></p>

                    <div>
                        <strong>Comment: </strong>
                        <textarea id="eventComment" class="form-control"></textarea>
                    </div>
                    <div class="mt-2">
                        <strong>Odometer Reading: </strong>
                        <input type="number" id="eventOdometerReading" class="form-control" />
                    </div>
                    <div class="mt-2">
                        <strong>Upload Files: </strong>
                        <input type="file" id="eventFiles" class="form-control" multiple />
                    </div>
                    <!-- Cost Section -->
                    <div class="mt-2">
                        <label for="cost" class="form-label"><strong>Cost</strong></label>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <!--<label for="total_cost">Total Cost</label>-->
                                <input type="number" class="form-control" name="total_cost" id="total_cost" placeholder="Cost" value="{{ old('total_cost') }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
          </div>
          <div class="modal-footer">
                          <a href="" id="reminderRedirectButton" class="btn btn-info">Go to Reminder</a>
                                                    <a href="" id="reminderhistoryButton" class="btn btn-primary">Go to Attachments</a>
                                <button type="button" id="saveChangesButton" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>


      <!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Select Filter Type for Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" action="{{ route('fleet.calendar.export') }}" method="GET">
                        <input type="hidden" name="company_id" value="{{ request('company_id') }}">
                        <input type="hidden" name="vehicle_id" value="{{ request('vehicle_id') }}">
                        <input type="hidden" name="planner_type" value="{{ request('planner_type') }}">
                        <input type="hidden" name="depot_id" value="{{ request('depot_id') }}">
<input type="hidden" name="group_id" value="{{ request('group_id') }}">

                        <!-- Filter Type Dropdown -->
                        <div class="mb-3">
                            <label for="export_filter_type" class="form-label">Filter Type</label>
                            <select name="filter_type" id="export_filter_type" class="form-control" onchange="toggleExportInputs()">
                                <option value="">Select Filter Type</option>
                                <option value="year">Year Wise</option>
                                <option value="date">Date Wise</option>
                            </select>
                        </div>

                        <!-- Year Selection -->
                        <div class="mb-3" id="export_year_selection" style="display: none;">
                            <label for="exportYear" class="form-label">Select Year</label>
                            <select name="year" id="exportYear" class="form-control" onchange="toggleExportSubmitButton()">
                                <option value="">Please select the Year</option>
                                @for ($i = 2020; $i <= 2030; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- From & To Date Selection -->
                        <div id="export_date_range_selection" style="display: none;">
                            <div class="mb-3">
                                <label for="export_from_date" class="form-label">From Date</label>
                                <input type="date" name="from_date" id="export_from_date" class="form-control" onchange="toggleExportSubmitButton()">
                            </div>
                            <div class="mb-3">
                                <label for="export_to_date" class="form-label">To Date</label>
                                <input type="date" name="to_date" id="export_to_date" class="form-control" onchange="toggleExportSubmitButton()">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="exportForm" class="btn btn-success" id="exportButton" disabled>Export</button>
                </div>
            </div>
        </div>
    </div>
          <!-- PDF Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Select Filter Type for PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="pdfForm" action="{{ route('fleet.calendar.pdf') }}" method="GET">
                        <input type="hidden" name="company_id" value="{{ request('company_id') }}">
                        <input type="hidden" name="vehicle_id" value="{{ request('vehicle_id') }}">
                        <input type="hidden" name="planner_type" value="{{ request('planner_type') }}">
                        <input type="hidden" name="depot_id" value="{{ request('depot_id') }}">
<input type="hidden" name="group_id" value="{{ request('group_id') }}">

                        <!-- Filter Type Dropdown -->
                        <div class="mb-3">
                            <label for="filter_type" class="form-label">Filter Type</label>
                            <select name="filter_type" id="filter_type" class="form-control" onchange="toggleFilterInputs()">
                                <option value="">Select Filter Type</option>
                                <option value="year">Year Wise</option>
                                <option value="date">Date Wise</option>
                            </select>
                        </div>

                        <!-- Year Selection (Hidden by Default) -->
                        <div class="mb-3" id="year_selection" style="display: none;">
                            <label for="year" class="form-label">Select Year</label>
                            <select name="year" id="pdfYear" class="form-control" onchange="toggleSubmitButton()">
                                <option value="">Please select the Year</option>
                                @for ($i = 2020; $i <= 2030; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- From and To Date Selection (Hidden by Default) -->
                        <div id="date_range_selection" style="display: none;">
                            <div class="mb-3">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" name="from_date" id="from_date" class="form-control" onchange="toggleSubmitButton()">
                            </div>
                            <div class="mb-3">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" name="to_date" id="to_date" class="form-control" onchange="toggleSubmitButton()">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="pdfForm" class="btn btn-success" id="pdfButton" disabled>Generate PDF</button>
                </div>
            </div>
        </div>
    </div>
@endsection
