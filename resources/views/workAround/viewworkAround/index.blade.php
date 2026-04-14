@extends('layouts.admin')

@section('page-title')
    {{__('Walkaround Log')}}
@endsection

@push('script-page')
<script>
    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();

       var selectedCompanyId = '{{ request("company_id") }}';
        var selectedDepotId = '{{ request("depot_id") }}';
    var selectedGroupId = '{{ request("group_id") }}';
        var selectedDriverId = '{{ request("driver_id") }}';
        var selectedVehicleId = '{{ request("vehicle_id") }}';

    // ================= DEPOT =================
        function loadDepots(companyId, selectedDepotId = null) {
            if (companyId) {

            $('#depot_id').html('<option>Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: '{{ route("get.depots.by.company") }}',
                    type: 'GET',
                    data: { company_id: companyId },
                    success: function (data) {

                    $('#depot_id').html('<option value="">Select Depot</option>');

                        $.each(data, function (key, depot) {
                            let selected = selectedDepotId == depot.id ? 'selected' : '';
                        $('#depot_id').append(
                            `<option value="${depot.id}" ${selected}>${depot.name.toUpperCase()}</option>`
                        );
                        });

                        $('#depot_id').prop('disabled', false);

                            fetchDrivers();
                            fetchVehicles();
                        }
                });
        }
    }

    // ================= GROUP =================
    function loadGroups(companyId, selectedGroupId = null) {
        if (companyId) {

            $('#group_id').html('<option>Loading...</option>').prop('disabled', true);

            $.ajax({
                url: '{{ route("get.driver.group.by.company") }}',
                type: 'GET',
                data: { company_id: companyId },
                success: function (data) {

                    $('#group_id').html('<option value="">Select Group</option>');

                    $.each(data, function (key, group) {
                        let selected = selectedGroupId == group.id ? 'selected' : '';
                        $('#group_id').append(
                            `<option value="${group.id}" ${selected}>${group.name.toUpperCase()}</option>`
                        );
                    });

                    $('#group_id').prop('disabled', false);

                    fetchDrivers();
            }
            });
        }
        }

function fetchDrivers() {

    let companyId = $('#company_id').val();
    let depotId = $('#depot_id').val();
    let groupId = $('#group_id').val();

    $.ajax({

        url: '{{ route("get.drivers.bycompany") }}',
        method: 'POST',

        data: {
            _token: '{{ csrf_token() }}',
            company_id: companyId,
            depot_ids: depotId ? [depotId] : [],
            group_id: groupId
        },
        success: function (data) {

            let existingVal = $('#driver_id').val();

            // ❌ DO NOT RESET if already selected
            if (!existingVal) {
            $('#driver_id').html('<option value="">Select Driver</option>');
            }

            $.each(data, function (index, driver) {

                let selected = (existingVal == driver.id) ? 'selected' : '';

                // ✅ avoid duplicate
                if ($('#driver_id option[value="'+driver.id+'"]').length === 0) {
                $('#driver_id').append(
                        `<option value="${driver.id}" ${selected}>
                            ${driver.name.toUpperCase()}
                        </option>`
                );
                }
            });
        }
    });
}

    // ================= VEHICLE =================
function fetchVehicles(){

        let companyId = $('#company_id').val();
        let depotId = $('#depot_id').val();

        if(!depotId){
            $('#vehicle_id').html('<option value="">Select Depot First</option>');
            return;
    }

    $.ajax({
        url: '{{ route("get.vehicles.bycompany") }}',
        method: 'POST',
        data:{
            _token:'{{ csrf_token() }}',
            company_id:companyId,
                depot_ids:[depotId]
        },
        success:function(data){

            $('#vehicle_id').html('<option value="">Select Vehicle</option>');

            $.each(data,function(index,vehicle){

                let selected = selectedVehicleId == vehicle.id ? 'selected' : '';

                $('#vehicle_id').append(
                        `<option value="${vehicle.id}" ${selected}>
                            ${vehicle.registrations.toUpperCase()}
                        </option>`
                );
            });
        }
    });
}

    // ================= INIT =================
            if (selectedCompanyId) {
                loadDepots(selectedCompanyId, selectedDepotId);
    loadGroups(selectedCompanyId, selectedGroupId);


            }

    // ================= EVENTS =================
            $('#company_id').on('change', function () {

        let companyId = $(this).val();

                loadDepots(companyId);
        loadGroups(companyId);

        $('#driver_id').html('<option value="">Select Driver</option>');
        $('#vehicle_id').html('<option value="">Select Vehicle</option>');
            });

            $('#depot_id').on('change', function () {
                    fetchDrivers();
                    fetchVehicles();
            });

    $('#group_id').on('change', function () {
            fetchDrivers();
    });

    });
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('walkAround')}}</li>
    <li class="breadcrumb-item">{{__('Walkaround Log')}}</li>
@endsection
@php
use Carbon\Carbon;
use Illuminate\Support\Str;
@endphp

@section('action-btn')
    <div class="float-end">
        {{--  @can('create profile')
        <a href="#" data-size="md" data-url="{{ route('profile.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Profile')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan  --}}

        @if((Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager')) && request('company_id'))
    <a href="{{ route('export.workaround', ['company_id' => request('company_id'),'depot_id' => request('depot_id'),'start_date' => request('start_date'),'end_date' => request('end_date'),'driver_id' => request('driver_id'),'vehicle_id' => request('vehicle_id'),'group_id' => request('group_id'),'issue_filter' => request('issue_filter')]) }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
                Export
            </a>
    <a href="{{ route('pdf.workaround', ['company_id' => request('company_id'),'depot_id' => request('depot_id'),'start_date' => request('start_date'),'end_date' => request('end_date'),'driver_id' => request('driver_id'),'vehicle_id' => request('vehicle_id'),'group_id' => request('group_id'),'issue_filter' => request('issue_filter')]) }}" data-bs-toggle="tooltip" title="{{ __('PDF') }}" class="btn btn-sm btn-primary">
                PDF
            </a>
        @elseif(!(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager')))
    <a href="{{ route('export.workaround', ['company_id' => request('company_id'),'depot_id' => request('depot_id'),'start_date' => request('start_date'),'end_date' => request('end_date'),'driver_id' => request('driver_id'),'vehicle_id' => request('vehicle_id'),'group_id' => request('group_id'),'issue_filter' => request('issue_filter')]) }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
                Export
            </a>
    <a href="{{ route('pdf.workaround', ['company_id' => request('company_id'),'depot_id' => request('depot_id'),'start_date' => request('start_date'),'end_date' => request('end_date'),'driver_id' => request('driver_id'),'vehicle_id' => request('vehicle_id'),'group_id' => request('group_id'),'issue_filter' => request('issue_filter')]) }}" data-bs-toggle="tooltip" title="{{ __('PDF') }}" class="btn btn-sm btn-primary">
                PDF
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="row" style="margin-bottom: 10px;margin-top:10px;">
    <div class="col-12">
       <form method="GET" action="{{ route('viewworkaround.index') }}">
          
            <div class="row">
                @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                    <div class="col-md-4">
                        <label for="company_id">{{ __('Filter by Company') }}</label>
                        <select name="company_id" id="company_id" class="form-control">
                            <option value="">{{ __('Select a Company') }}</option>
                            @foreach($companies->sortBy('name') as $company)
                                <option value="{{ $company->id }}" {{ $selectedCompanyId == $company->id ? 'selected' : '' }}>
                                    {{ strtoupper($company->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                @else
                <!-- Hidden fields for normal users -->
                <input type="hidden" name="company_id" id="company_id" value="{{ \Auth::user()->company_id }}">

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
                    <label for="group_id">{{ __('Filter by Driver Group') }}</label>
                    <select name="group_id" id="group_id" class="form-control">
                        <option value="">{{ __('Select Group') }}</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ strtoupper($group->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                

                <!-- Driver Filter Dropdown -->
<div class="col-md-4 mt-3">
    <label for="driver_id">{{ __('Filter by Driver') }}</label>
    <select name="driver_id" id="driver_id" class="form-control">
 <option value="">{{ __('Select Driver') }}</option>
    @if(request('driver_id'))
        @php
            $selectedDriver = \App\Models\Driver::find(request('driver_id'));
        @endphp

        <option value="{{ request('driver_id') }}" selected>
            {{ strtoupper($selectedDriver->name ?? 'SELECTED DRIVER') }}
        </option>
    @else
       <option value="">{{ __('Select Driver') }}</option>
    @endif

    </select>
</div>
                <!-- Vehicle Filter Dropdown -->
<div class="col-md-4 mt-3">
    <label for="vehicle_id">{{ __('Filter by Vehicle') }}</label>
    <select name="vehicle_id" id="vehicle_id" class="form-control">
        <option value="">{{ __('Select Vehicle') }}</option>
    </select>
</div>

                <div class="col-md-4 mt-3">
                    <label for="start_date">{{ __('From Date') }}</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-4 mt-3">
                    <label for="end_date">{{ __('To Date') }}</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                
                <div class="col-md-4 mt-3">
    <label for="issue_filter">{{ __('Filter By Defect / Rectified') }}</label>
    <select name="issue_filter" id="issue_filter" class="form-control">
        <option value="">{{ __('All') }}</option>

        <option value="defect" {{ request('issue_filter') == 'defect' ? 'selected' : '' }}>
            Defect (>0)
        </option>

        <option value="rectified" {{ request('issue_filter') == 'rectified' ? 'selected' : '' }}>
            Rectified (>0)
        </option>
    </select>
</div>

                <div class="col-md-4 mt-3">
                    <button type="submit" class="btn btn-primary mt-4">{{ __('Filter') }}</button>
                    <a href="{{ route('viewworkaround.index') }}" class="btn btn-secondary mt-4">{{ __('Reset Filter') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
    <div class="row">
        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th class="text-end ">{{__('Action')}}</th>
                                <th>{{__('Driver')}}</th>
                                <th>{{__('Depot')}}</th>
                                <th>{{__('Vehicles')}}</th>
                                <th>{{__('Walkaround Date')}}</th>
                                <th>{{__('Duration')}}</th>
                                <!--<th>{{__('Location')}}</th>-->
                                <th>{{__('Defects')}}</th>
                                <th>{{__('Rectified ')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if ($walkaround->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">
                                        {{ $selectedCompanyId ? __('No Walkarounds') : __('No data available. Please select a company to filter the data.') }}
                                    </td>
                                </tr>
                                @else
                                    @foreach ($walkaround as $walkarounds)
                                                                     <tr style="{{ $walkarounds->duration && \Carbon\Carbon::parse($walkarounds->duration)->diffInSeconds() < 599 ? 'background-color: #ff00005c;' : '' }}">

                                        <td style="text-align: center">
                                            {{--  @can('show profile')  --}}
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('viewworkaround.show', $walkarounds->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}">
                                                    <span class="text-white"><i class="ti ti-eye"></i></span>
                                                </a>
                                            </div>
                                              {{--  @endcan  --}}
                                        </td>
                                        <td style="text-align: left">
        {{ $walkarounds->driver ? strtoupper($walkarounds->driver->name) : 'N/A' }}

    </td>
    <td style="text-align: left">
        {{ $walkarounds->depot ? $walkarounds->depot->name : 'N/A' }}
    </td>
    <td style="text-align: left">
        @if ($walkarounds->vehicle)
            @if ($walkarounds->vehicle->vehicle_type == 'Trailer')
                {{ $walkarounds->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID' }} - {{ $walkarounds->vehicle->vehicleDetail->make ?? 'No Make' }}
            @else
                {{ $walkarounds->vehicle->registrations ?? 'No Registration' }} - {{ $walkarounds->vehicle->vehicleDetail->make ?? 'No Make' }}
            @endif
        @else
            N/A
        @endif
    </td>
    <td style="text-align: left">
        {{ $walkarounds->uploaded_date ?? 'N/A' }}
    </td>
    <td style="text-align: left">
        {{ $walkarounds->duration ?? '0' }}
    </td>
    <!--<td style="text-align: center">-->
    <!--     <span data-bs-toggle="tooltip" title="{{ $walkarounds->location }}">-->
    <!--                                        {{ Str::limit($walkarounds->location, 30) }}-->
    <!--                                    </span>-->
    <!--</td>-->
    <td style="text-align: center">
        {{ $walkarounds->defects_count ?? 0 }}
    </td>
        <td style="text-align: center">
        {{ $walkarounds->rectified ?? 0 }}
    </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
