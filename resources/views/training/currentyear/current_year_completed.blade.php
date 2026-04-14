@extends('layouts.admin')

@section('page-title')
    {{__('Current Year Completed Training')}}
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

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
        <li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{__('Training')}}</a></li>
    <li class="breadcrumb-item">{{__('Current Year Completed Training')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
    <a href="{{ route('currentYearCompleted.export') }}?company_id={{ request('company_id') }}&from_date={{ request('from_date') }}&to_date={{ request('to_date') }}&depot_id={{ request('depot_id') }}&group_id={{ request('group_id') }}" class="btn btn-success">{{ __('Export to Excel') }}</a>
    </div>
@endsection

@section('content')
<div class="row" style="margin-bottom: 10px;margin-top:10px;">
    <div class="col-12">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('currentYearCompleted') }}">
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
                <!-- Training Type Filter: Visible for all roles -->
                <div class="col-md-4">
                    <label for="from_date">{{__('Filter by From Date')}}</label>
                    <input type="date" name="from_date" class="form-control" placeholder="{{__('From Date')}}" value="{{ request('from_date') }}">

                </div>

                <!-- Training Course Filter -->
                <div class="col-md-4">
                    <label for="training_course_id">{{__('Filter by To Date')}}</label>
                    <input type="date" name="to_date" class="form-control" placeholder="{{__('To Date')}}" value="{{ request('to_date') }}">

                </div>

                <div class="col-md-4 mt-3">
                    <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                    <a href="{{ route('currentYearCompleted') }}" class="btn btn-secondary">{{__('Reset Filter')}}</a>
                </div>
            </div>
        </form>
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
                                <th>{{__('Driver Name')}}</th>
                                <th>{{__('Driver Email')}}</th>
                                <th>{{__('Driver Mobile No')}}</th>
                                <th>{{__('Driver Group')}}</th>
                                <th>{{__('Training Type')}}</th>
                                <th>{{__('Training Course')}}</th>
                                <th>{{__('From Date')}}</th>
                                <th>{{__('To Date')}}</th>
                                                                <th>{{__('Signature')}}</th>
                                <th>{{__('Status')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">
                           @foreach ($completedTraining as $completed)
                                <tr>
                                    <td style="text-align: left">{{ $completed->driver->name ?? 'Driver Name Not Found' }}</td>
                                    <td style="text-align: left">{{ $completed->driver->contact_email ?? null }}</td>
                                    <td style="text-align: left">{{ $completed->driver->contact_no ?? null }}</td>
                                    <td style="text-align: left">{{ $completed->driver->group->name ?? null }}</td>
                                    <td style="text-align: left">{{ $completed->training->trainingType->name ?? null }}</td>
                                    <td style="text-align: left">{{ $completed->training->trainingCourse->name ?? null }}</td>
                                    <td style="text-align: left">{{ \Carbon\Carbon::parse($completed->training->from_date)->format('d/m/Y') }}</td>
                                    <td style="text-align: left">{{ \Carbon\Carbon::parse($completed->training->to_date)->format('d/m/Y') }}</td>
                                    <td style="text-align: left">
                                        @if($completed->signature)
                           <img src="{{ asset('storage/' . $completed->signature) }}" alt="Signature" style="width: 100px; height: auto;">
                           @endif
                                    </td>
                                    <td style="text-align: left">{{ $completed->status }}</td>
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
