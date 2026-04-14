@extends('layouts.admin')
@section('page-title')
{{__('Manage PCN')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('PCN')}}</li>
@endsection
@section('action-btn')
@can('create pcn')
<div class="float-end">
    <a href="{{ route('pcn.create') }}" class="btn btn-primary">{{ __('Create PCN') }}</a>
    <a href="{{ route('planner.Data.export', [
    'company_id' => request('company_id'),
    'depot_id' => request('depot_id'),
    'group_id' => request('group_id'),
    'issuing_authority' => request('issuing_authority'),
    'from_date' => request('from_date'),
    'to_date' => request('to_date')
]) }}" class="btn btn-primary">
        Export To Excel
    </a>
</div>
@endcan
@endsection


@section('content')
<div class="d-flex justify-content-left align-items-center my-3">
    <p class="mb-0 me-4 fs-4">{{ __('Total PCNs:') }} <span style="color: #ffffff;padding: 10px;background-color: #48494B;border-radius: 7px;">{{ $totalCount }}</span></p>
    <p class="mb-0 fs-4">{{ __('Total Amount:') }} <span style="color: #ffffff;padding: 10px;background-color: #48494B;border-radius: 7px;">£{{ $totalFineAmount }}</span></p>
    @if(session('filters_applied') && $mostFrequentDriver)
    <p class="mb-0 fs-4" style="margin-left: 38px;">{{ __('Most Violation Driver:') }} <span style="color: #ffffff;padding: 10px;background-color: #48494B;border-radius: 7px;">{{ $mostFrequentDriver }} ({{ $mostFrequentDriverCount }})</span></p>
    @endif
</div>
<div class="row">
    <div class="row" style="margin-bottom: 10px; margin-top: 10px;">
    <div class="col-12">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('pcn.index') }}">
            <div class="row">
                @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                <div class="col-md-4">
                    <label for="company_id">{{__('Filter by Company')}}</label>
                    <select name="company_id" id="company_id" class="form-control" onchange="fetchDepots(this.value)">
                        <option value="">{{__('All Companies')}}</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ strtoupper($company->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- <div class="col-md-4">
                            <label for="depot_id">{{__('Filter by Depot')}}</label>
                <select name="depot_id" id="depot_id" class="form-control">
                    <option value="">{{__('All Depots')}}</option>
                    @foreach($depots as $depot)
                    <option value="{{ $depot->id }}" {{ request('depot_id') == $depot->id ? 'selected' : '' }}>
                        {{ strtoupper($depot->name) }}
                    </option>
                    @endforeach
                </select>
            </div> --}}

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
                <label for="group_id">{{__('Filter by Vehicle Group')}}</label>

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

            <!-- ROW 2 (3 fields) -->
            <div class="col-md-4 mt-3">
                <label for="issuing_authority">{{__('Filter by Issuing Authority')}}</label>
                <select name="issuing_authority" id="issuing_authority" class="form-control">
                    <option value="">{{__('All Authorities')}}</option>
                    <option value="Local Council" {{ request('issuing_authority') == 'Local Council' ? 'selected' : '' }}>
                        {{ __('Local Council') }}
                    </option>
                    <option value="Police" {{ request('issuing_authority') == 'Police' ? 'selected' : '' }}>
                        {{ __('Police') }}
                    </option>
                    <option value="DVSA" {{ request('issuing_authority') == 'DVSA' ? 'selected' : '' }}>
                        {{ __('DVSA') }}
                    </option>
                    <option value="Other" {{ request('issuing_authority') == 'Other' ? 'selected' : '' }}>
                        {{ __('Other') }}
                    </option>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <label for="from_date">{{ __('From Date') }}</label>
                <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-4 mt-3">
                <label for="to_date">{{ __('To Date') }}</label>
                <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <!-- ROW 3 (Buttons) -->
            <div class="col-md-4 mt-4">
                <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                <a href="{{ route('pcn.index') }}" class="btn btn-secondary">{{__('Reset Filter')}}</a>
            </div>

    </div>
    </form>
</div>
</div>





<div class="col-9">
    <div class="card">
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th class="text-end">{{__('Action')}}</th>
                            <th>{{__('Company Name')}}</th>
                            <th>{{__('Depot')}}</th>
                            <th>{{__('Registration Number')}}</th>
                            <th>{{__('Driver Name')}}</th>
                            <th>{{__('Notice Number')}}</th>
                            <th>{{__('Notice date')}}</th>
                            <th>{{__('Violation date')}}</th>
                            <th>{{__('Location of Contravention')}}</th>
                            <th>{{__('Issuing Authority')}}</th>
                            <th>{{__('Type')}}</th>
                            <th>{{__('Issuing Authority Action')}}</th>
                            <th>{{__('Fine Amount')}}</th>
                            <th>{{__('Deduction Amount')}}</th>
                            <th>{{__('Status')}}</th>
                            <th>{{__('Created By')}}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ( $pcn as $pcns )
                        <tr>
                            <td>
                                <div class="action-btn bg-primary ms-2">
                                    <a href="{{ route('pcn.show', $pcns->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}">
                                        <span class="text-white"><i class="fa fa-file"></i></span>
                                    </a>
                                </div>
                                <div class="action-btn bg-info ms-2">
                                    <a href="{{ route('pcn.edit', $pcns->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit PCN') }}">
                                        <i class="ti ti-pencil text-white"></i>
                                    </a>
                                </div>
                            </td>
                            <td>{{ strtoupper($pcns->types->name ?? '') }}</td>
                            <td>{{ strtoupper($pcns->depot->name ?? '') }}</td>
                            <td>{{ $pcns->vehicle_registration_number }}</td>
                            <td>{{ $pcns->driver_name }}</td>
                            <td>{{ $pcns->notice_number ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($pcns->notice_date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($pcns->violation_date)->format('d/m/Y') }}</td>
                            <td>{{ $pcns->location }}</td>
                            <td>{{ $pcns->issuing_authority }}</td>
                            <td>{{ $pcns->type }}</td>
                            <td>{{ $pcns->action }}</td>
                            <td>{{ $pcns->fine_amount }}</td>
                            <td>{{ $pcns->deduction_amount }}</td>
                            <td>{{ $pcns->status }}</td>
                            <td>{{ $pcns->creator->username ?? '' }}</td>
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

    //  function fetchDepots(companyId) {
    //     if (companyId) {
    //         $.ajax({
    //             url: '/get-depots/' + companyId, // Adjust the URL to your route for fetching depots
    //             method: 'GET',
    //             success: function(data) {
    //                 $('#depot_id').empty(); // Clear existing options
    //                 $('#depot_id').append('<option value="">{{__("All Depots")}}</option>'); // Add default option
    //                 $.each(data, function(index, depot) {
    //                     $('#depot_id').append('<option value="' + depot.id + '">' + depot.name.toUpperCase() + '</option>');
    //                 });
    //             }
    //         });
    //     } else {
    //         $('#depot_id').empty().append('<option value="">{{__("All Depots")}}</option>'); // Reset depots if no company selected
    //     }
    // }

</script>

@endpush
