@extends('layouts.admin')
@section('page-title')
{{__('Manage Vehicle')}}
@endsection

@push('script-page')
<script>
    function updateAllData() {
        // Show full-screen loader
        $('#loader').show();
    }
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('showDriverModal'))
        var DriverModal = new bootstrap.Modal(document.getElementById('DriverModal'), {});
        DriverModal.show();
        @endif
    });

    function handleDriverChoice(choice) {
        if (choice === 'yes') {
            window.location.href = "{{ route('driver.index') }}"; // Redirect to the Driver index page
        }
    }

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

<script>
    document.getElementById('updateDataForm').addEventListener('submit', function() {
        const btn = document.getElementById('updateBtn');
        btn.disabled = true;
        btn.innerText = 'Updating...';
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const bulkActionBtn = document.getElementById('bulk-action-btn');
        const bulkEditBtn = document.getElementById('bulk-edit-btn');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

        function updateBulkActionState() {
            const anyChecked = document.querySelectorAll('.select-item:checked').length > 0;
            bulkActionBtn.disabled = !anyChecked;
        }

        // Handle "Select All"
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.select-item').forEach(cb => cb.checked = this.checked);
                updateBulkActionState();
            });
        }

        // Event delegation for dynamic checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('select-item')) {
                if (!e.target.checked && selectAll) {
                    selectAll.checked = false;
                }
                updateBulkActionState();
            }
        });

        // Bulk edit popup
        bulkEditBtn.addEventListener('click', function() {
            let selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
                .map(cb => cb.value);

            if (selectedIds.length > 0) {
                let url = "{{ route('selected.vehicle.edit', ':ids') }}".replace(':ids', selectedIds.join(','));

                // Simulate the exact same type of link click your single edit button uses
                let tempLink = document.createElement('a');
                tempLink.setAttribute('href', '#');
                tempLink.setAttribute('data-url', url);
                tempLink.setAttribute('data-ajax-popup', 'true');
                tempLink.setAttribute('data-size', 'md');
                tempLink.setAttribute('data-title', 'Edit Selected vehicle');

                // Append to DOM so event listeners catch it
                document.body.appendChild(tempLink);
                tempLink.click();
                tempLink.remove();
            } else {
                alert('Please select at least one vehicle.');
            }
        });

        bulkDeleteBtn.addEventListener('click', function() {
            let selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
                .map(cb => cb.value);

            if (selectedIds.length > 0) {
                if (confirm('The vehicle’s data will be permanently deleted and cannot be recovered. Are you sure you want to continue?')) {
                    fetch("{{ route('selected.vehicle.delete') }}", {
                            method: 'POST'
                            , headers: {
                                'Content-Type': 'application/json'
                                , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                            , body: JSON.stringify({
                                ids: selectedIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert(data.message || 'Error deleting selected vehicle.');
                            }
                        })
                        .catch(err => alert('Error deleting selected vehicle.'));
                }
            } else {
                alert('Please select at least one driver.');
            }
        });




    });

</script>
<script>
    function exportSelected() {
        let selectedIds = [];
        document.querySelectorAll('.select-item:checked').forEach(cb => {
            selectedIds.push(cb.value);
        });

        // Get the button safely
        const btn = document.getElementById('exportBtn');
        if (btn) {
            btn.disabled = true;
        }

        let url = "{{ route('vehicleDataexport.export') }}" +
            "?company_id={{ request()->input('company_id') }}" +
            "&filter_column={{ request()->input('filter_column') }}" +
            "&filter_value={{ request()->input('filter_value') }}" +
            "&vehicle_status={{ request()->input('vehicle_status') }}" +
            "&depot_id={{ request()->input('depot_id') }}" +
             "&group_id={{ request()->input('group_id') }}";

        // If some are selected → pass IDs
        if (selectedIds.length > 0) {
            url += "&ids=" + selectedIds.join(',');
        }
        // If none selected → export all (don’t add ids)

        window.location.href = url;

        // Re-enable button after 2s (if exists)
        setTimeout(() => {
            if (btn) {
                btn.disabled = false;
            }
        }, 2000);
    }

</script>

<style>
    /* Styles for full-screen loader */
    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Semi-transparent black */
        z-index: 9999;
        text-align: center;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loader-content {
        background: rgba(255, 255, 255, 0.8);
        /* Semi-transparent white */
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
        color: #ffffff;
        /* White spinner color */
    }

    .expired-date {
        color: white;
        /* Color for expired dates */
        background-color: red !important;
    }

    .warning-date {
        color: black;
        /* Color for dates within 15 days of expiration */
        background-color: orange !important;

    }

    /*    td {
    padding: 8px;
    text-align: center;
    border: 1px solid #ddd;
}*/

</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Vehicle')}}</li>
@endsection
@php
use Carbon\Carbon;
$today = Carbon::today();

@endphp
@section('action-btn')
<div class="float-end">
    <!--<a href="{{ route('contract.grid') }}"  data-bs-toggle="tooltip" title="{{__('Grid View')}}" class="btn btn-sm btn-primary">-->
    <!--    <i class="ti ti-layout-grid"></i>-->
    <!--</a>-->
    <div class="btn-group">
        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-plus"></i>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a href="#" data-size="md" data-url="{{ route('contract.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create New Vehicle') }}" class="dropdown-item">
                    {{ __('Vehicle') }}
                </a>
            </li>
            <li>
                <a href="#" data-size="md" data-url="{{ route('trailer.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create New Trailer') }}" class="dropdown-item">
                    {{ __('Trailer') }}
                </a>
            </li>
        </ul>
    </div>


    <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('vehicle.file.import') }}" data-ajax-popup="true" data-bs-toggle="tooltip" class="btn btn-sm btn-primary">
        <i class="ti ti-file-import"></i>
    </a>

    <a href="javascript:void(0)" id="exportBtn" onclick="exportSelected()" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">
        <i class="ti ti-file-export"></i>
    </a>

    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateRoadTaxModal">
        <i class="ti ti-plus"></i> {{ __('Update Road Tax') }}
    </button>

    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateDataModal">
        <i class="ti ti-plus"></i> {{ __('Update MOT') }}
    </button>

    @php
    $isCompanyOrPTC = Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager');
    @endphp

    @if(($isCompanyOrPTC && request('company_id')) || !$isCompanyOrPTC)
    <div class="btn-group me-1">
        <button id="bulk-action-btn" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" disabled>
            <i class="ti ti-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a href="javascript:void(0)" id="bulk-edit-btn" class="dropdown-item">Edit Selected</a>
            </li>
            @if(\Auth::user()->type == 'company')
            <li>
                <a href="javascript:void(0)" id="bulk-delete-btn" class="dropdown-item text-danger">Delete Selected</a>
            </li>
            @endif
            {{-- Add more bulk actions here --}}
        </ul>
    </div>
    @endif


</div>
@endsection

@section('content')

<div class="modal fade" id="updateRoadTaxModal" tabindex="-1" aria-labelledby="updateRoadTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vehicle.updateAllData') }}" onsubmit="document.getElementById('updateRoadTaxBtn').disabled = true;">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateRoadTaxModalLabel">{{ __('Update Road Tax (API 1)') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="from_number" class="form-label">{{ __('From Data Number') }}</label>
                        <input type="number" class="form-control" name="from_number" id="from_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="to_number" class="form-label">{{ __('To Data Number') }}</label>
                        <input type="number" class="form-control" name="to_number" id="to_number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="updateRoadTaxBtn">{{ __('Update') }}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Update Data Modal -->
<div class="modal fade" id="updateDataModal" tabindex="-1" aria-labelledby="updateDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vehicle.updateAllData2') }}" onsubmit="document.getElementById('updateBtn').disabled = true;">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateDataModalLabel">Update MOT (API 2)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="from_number" class="form-label">From Data Number</label>
                        <input type="number" class="form-control" name="from_number" id="from_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="to_number" class="form-label">To Data Number</label>
                        <input type="number" class="form-control" name="to_number" id="to_number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="updateBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

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
            <strong>Data:</strong> {{ implode(', ', $error['data']) }}
        </li>
        @endforeach
    </ul>
</div>
@php
// Remove the session data so it doesn't show up after reload
session()->forget('errorArray');
@endphp
@endif
<!-- Loader -->
<div id="loader" style="display: none;">
    <div class="loader-content">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
<div class="row">

    <div class="row" style="margin-bottom: 10px;margin-top:10px;">
        <div class="col-12">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('contract.index') }}">
                <div class="row">
                    <!-- Filter by Company -->
                    @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                    <div class="col-md-4">
                        <label for="company_id">{{__('Filter by Company')}}</label>
                        <select name="company_id" id="company_id" class="form-control">
                            <option value="">{{__('All Companies')}}</option>
                            @foreach($companies->sortBy('name') as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ strtoupper($company->name) }}
                            </option>
                            @endforeach
                        </select>
                    </div>


                    @endif

                                        <!-- Depot Selection -->
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


                    <!-- Filter by Column -->
                    <div class="col-md-4 mt-3">
                        <label for="filter_column">{{__('Filter by Column')}}</label>
                        <select name="filter_column" id="filter_column" class="form-control">
                            <option value="">{{__('Select Filter')}}</option>
                            <option value="taxDueDate" {{ request('filter_column') == 'taxDueDate' ? 'selected' : '' }}>
                                {{__('Road Tax')}}</option>
                            <option value="annual_test_expiry_date" {{ request('filter_column') == 'annual_test_expiry_date' ? 'selected' : '' }}>
                                {{__('MOT')}}</option>
                            <option value="tacho_calibration" {{ request('filter_column') == 'tacho_calibration' ? 'selected' : '' }}>
                                {{__('Tacho Calibration')}}</option>
                            <option value="dvs_pss_permit_expiry" {{ request('filter_column') == 'dvs_pss_permit_expiry' ? 'selected' : '' }}>
                                {{__('DVS PSS Permit Expiry')}}</option>
                            <option value="insurance" {{ request('filter_column') == 'insurance' ? 'selected' : '' }}>
                                {{__('Insurance')}}</option>
                            <option value="PMI_due" {{ request('filter_column') == 'PMI_due' ? 'selected' : '' }}>
                                {{__('PMI Due')}}</option>
                            <option value="brake_test_due" {{ request('filter_column') == 'brake_test_due' ? 'selected' : '' }}>
                                {{__('Brake Test Due')}}</option>

                        </select>
                    </div>

                    <!-- Filter by Expiry or Expiry Soon -->
                    <div class="col-md-4 mt-3">
                        <label for="filter_value">{{__('Filter by Expiry Status')}}</label>
                        <select name="filter_value" id="filter_value" class="form-control">
                            <option value="">{{__('Select Expiry Status')}}</option>
                            <option value="expiry" {{ request('filter_value') == 'expiry' ? 'selected' : '' }}>
                                {{__('Expired')}}</option>
                            <option value="expiry_soon" {{ request('filter_value') == 'expiry_soon' ? 'selected' : '' }}>
                                {{__('Expiry Soon (Within 15 Days)')}}</option>
                        </select>
                    </div>

                    <div class="col-md-4 mt-3">
                        <label for="vehicle_status">{{ __('Filter by Vehicle Status') }}</label>
                        <select name="vehicle_status" id="vehicle_status" class="form-control">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="Owned" {{ request('vehicle_status') == 'Owned' ? 'selected' : '' }}>Owned</option>
                            <option value="Rented" {{ request('vehicle_status') == 'Rented' ? 'selected' : '' }}>Rented</option>
                            <option value="Leased" {{ request('vehicle_status') == 'Leased' ? 'selected' : '' }}>Leased</option>
                            <option value="Contract Hire" {{ request('vehicle_status') == 'Contract Hire' ? 'selected' : '' }}>Contract Hire</option>
                            <option value="Depot Transfer" {{ request('vehicle_status') == 'Depot Transfer' ? 'selected' : '' }}>Depot Transfer</option>
                            <option value="Archive" {{ request('vehicle_status') == 'Archive' ? 'selected' : '' }}>Archive</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                        <a href="{{ route('contract.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>
                                    <a href="#">
                                        <input type="checkbox" id="select-all">
                                    </a>
                                </th>
                                <th scope="col">#</th>
                                <th scope="col">{{__('Action')}}</th>
                                <th scope="col">{{__('Vehicle ID')}}</th>
                                <th scope="col">{{__('Vehicle Reg')}}</th>
                                <th scope="col">{{__('Vehicle Group')}}</th>
                                <th>{{ __('Depot') }}</th>
                                <th scope="col">{{__('Vehicle Status')}}</th>
                                <th scope="col">{{__('Company Name')}}</th>
                                <th scope="col">{{__('Vehicle Type')}}</th>
                                <th scope="col">{{__('Make')}}</th>
                                <th scope="col">{{__('Model')}}</th>
                                <th scope="col">{{__('Road Tax')}}</th>
                                <th scope="col">{{__('MOT')}}</th>
                                <th scope="col">{{__('Tacho Calibration')}}</th>
                                <th scope="col">{{__('DVS/PSS Permit Expiry')}}</th>
                                <th scope="col">{{__('Insurance')}}</th>
                                <th scope="col">{{__('PMI Due')}}</th>
                                <th scope="col">{{__('Brake Test Due')}}</th>

                                <th scope="col">{{__('Created By')}}</th>


                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contracts as $contract)

                            <tr class="font-style">
                                <td>
                                    <input type="checkbox" class="select-item" value="{{ $contract->id }}">
                                </td>
                                <td>{{ $contract->id ?? '-' }}</td>
                                <td class="action ">

                                    @if($contract->status=='accept')
                                    <div class="action-btn bg-primary ms-2">
                                        <a href="#" data-size="lg" data-url="{{ route('contract.copy', $contract->id) }}" data-ajax-popup="true" data-title="{{ __('Copy Contract') }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Duplicate') }}"><i class="ti ti-copy text-white"></i>
                                        </a>
                                    </div>

                                    @endif
                                    @can('show vehicle')
                                    <div class="action-btn bg-warning ms-2">
                                        @if($contract->vehicle && $contract->vehicle->vehicle_type == 'Trailer')
                                        <a href="{{ route('trailer.show',$contract->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                                        @else
                                        <a href="{{ route('contract.show',$contract->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                                        @endif
                                    </div>

                                    @endcan
                                    @can('edit vehicle')
                                    <div class="action-btn bg-info ms-2">
                                        @if($contract->vehicle && $contract->vehicle->vehicle_type == 'Trailer')
                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('trailer.edit', $contract->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Trailer') }}">
                                            <i class="ti ti-pencil text-white"></i>
                                        </a>
                                        @else
                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('contract.edit', $contract->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Vehicle') }}">
                                            <i class="ti ti-pencil text-white"></i>
                                        </a>
                                        @endif
                                    </div>
                                    @endcan
                                    @can('delete vehicle')
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['contract.destroy', $contract->id]]) !!}
                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                        {!! Form::close() !!}
                                    </div>
                                    @endcan


                                </td>
                                <td>{{ $contract->vehicle_nick_name ?? '-'}}</td>
                                <td>{{ $contract->registrationNumber}}</td>
                                <td>{{ $contract->group ? $contract->group->name : '-' }}</td>
                                <td>{{ $contract->depot ? ucfirst(strtolower($contract->depot->name)) : '-' }}</td>


                                <td>{{ $contract->vehicle_status ?? null}}</td>
                                <td>{{ !empty($contract->types) ? strtoupper($contract->types->name) : '' }}</td>
                                <td>{{ !empty($contract->vehicle) ? $contract->vehicle->vehicle_type : '-' }}</td>
                                <td>{{ $contract->make }}</td>
                                <td>{{ !empty($contract->vehicle) ? $contract->vehicle->model : '-' }}</td>
                                <td class="
     {{
        $contract->taxDueDate_status == 'EXPIRED' ? 'expired-date' :
        ($contract->taxDueDate_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->taxDueDate) && $contract->taxDueDate != '-' ?
        Carbon::parse($contract->taxDueDate)->format('d/m/Y') :
        '-'
    }}
                                </td>
                                <td class="
    {{
        $contract->vehicle?->annual_test_status == 'EXPIRED' ? 'expired-date' :
        ($contract->vehicle?->annual_test_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->vehicle?->annual_test_expiry_date) && $contract->vehicle?->annual_test_expiry_date != '-' ?
        \Carbon\Carbon::parse($contract->vehicle->annual_test_expiry_date)->format('d/m/Y') :
        '-'
    }}
                                </td>

                                <td class="
    {{
        $contract->tacho_status == 'EXPIRED' ? 'expired-date' :
        ($contract->tacho_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->tacho_calibration) && \Carbon\Carbon::hasFormat($contract->tacho_calibration, 'Y-m-d') ?
        \Carbon\Carbon::parse($contract->tacho_calibration)->format('d/m/Y') :
        '-'
    }}
                                </td>
                                <td class="
    {{
        $contract->dvs_pss_status == 'EXPIRED' ? 'expired-date' :
        ($contract->dvs_pss_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->dvs_pss_permit_expiry) && $contract->dvs_pss_permit_expiry != '-' ?
        Carbon::parse($contract->dvs_pss_permit_expiry)->format('d/m/Y') :
        '-'
    }}
                                </td>
                                <td class="
     {{
        $contract->insurance_status == 'EXPIRED' ? 'expired-date' :
        ($contract->insurance_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->insurance) && $contract->insurance != '-' ?
        Carbon::parse($contract->insurance)->format('d/m/Y') :
        '-'
    }}
                                </td>
                                <td class="
    {{
        $contract->PMI_status == 'EXPIRED' ? 'expired-date' :
        ($contract->PMI_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->PMI_due) && $contract->PMI_due != '-' ?
        Carbon::parse($contract->PMI_due)->format('d/m/Y') :
        '-'
    }}
                                </td>
                                <td class="
    {{
        $contract->brake_test_status == 'EXPIRED' ? 'expired-date' :
        ($contract->brake_test_status == 'EXPIRING SOON' ? 'warning-date' : '')
    }}
">
                                    {{
        !empty($contract->brake_test_due) && $contract->brake_test_due != '-' ?
        Carbon::parse($contract->brake_test_due)->format('d/m/Y') :
        '-'
    }}
                                </td>


                                <td>{{ !empty($contract->creator)?$contract->creator->username:'' }}</td>

                                {{-- <td>--}}
                                {{-- <a href="#" class="action-item" data-url="{{ route('contract.description',$contract->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Desciption')}}" data-title="{{__('Desciption')}}"><i class="fa fa-comment"></i></a>--}}
                                {{-- </td>--}}


                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="DriverModal" tabindex="-1" aria-labelledby="DriverModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="DriverModalLabel">{{ __('Add Driver') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Would you like to add a Driver now?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('No') }}</button>
                <button type="button" class="btn btn-primary" onclick="handleDriverChoice('yes')">{{ __('Yes') }}</button>
            </div>
        </div>
    </div>
</div>


@endsection
