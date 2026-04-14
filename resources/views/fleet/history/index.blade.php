@extends('layouts.admin')
@section('page-title')
    {{__('Planner Log')}}
@endsection
@push('script-page')
<script>
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
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Planner Log')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create fors')

        @endcan

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="row" style="margin-bottom: 10px;margin-top:10px;">
            <div class="col-12">
                <!-- Filter Form -->

                <form method="GET" action="{{ route('planner.history.index') }}">
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
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                            <a href="{{ route('planner.history.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th class="text-end ">{{__('Action')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Vehicle')}}</th>
                                <th>{{__('Planner Type')}}</th>
                                <th>{{__('Reminder Date')}}</th>
                                <th >{{__('Status')}}</th>

                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($plannerreminder as $plannerreminders)
                                <tr>
                                    <td>
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('planner.history.show', $plannerreminders->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}">
                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                            </a>
                                        </div>
                                    </td>
                                    <td style="text-align: center">
                                        {{ $plannerreminders->fleet->company->name ?? 'N/A' }}
                                    </td>
                                    <td style="text-align: center">
                                        {{ $plannerreminders->fleet->vehicle->registrationNumber ?? 'N/A' }}
                                    </td>
                                    <td style="text-align: left">
                                        {{ $plannerreminders->fleet ? $plannerreminders->fleet->planner_type : 'N/A' }}
                                    </td>
                                    <td style="text-align: center">
                                        {{ \Carbon\Carbon::parse($plannerreminders->next_reminder_date)->format('d/m/Y') }}
                                    </td>

                                    <td style="text-align: center">{{ $plannerreminders->status }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
