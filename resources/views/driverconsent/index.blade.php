@extends('layouts.admin')
@section('page-title')
{{__('Manage Driver Consent Form')}}
@endsection
@push('script-page')
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip(); // Initialize Bootstrap tooltips
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
<style>
    td {
        text-align: center;
    }

</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Driver Consent Form')}}</li>
@endsection
@php
use Carbon\Carbon;
use Illuminate\Support\Str;
@endphp
@section('action-btn')
@can('create depot')
<div class="float-end">

</div>
@endcan
@endsection


@section('content')
<div class="row">
    <!--<div class="col-3">-->
    <!--    @include('layouts.depot_setup')-->
    <!--</div>-->
       <div class="row" style="margin-bottom:10px;margin-top:10px;">
        <div class="col-12">

            <form method="GET" action="{{ route('driver-consent-form.index') }}">

                <div class="row">

                @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                    <div class="col-md-4">
                        <label for="company_id">{{__('Filter by Company')}}</label>

                        <select name="company_id" id="company_id" class="form-control">

                            <option value="">{{__('All Companies')}}</option>

                            @foreach($companies as $company)

                        <option value="{{ $company->id }}"
                        {{ request('company_id') == $company->id ? 'selected' : '' }}>

                        {{ strtoupper($company->name) }}

                            </option>

                            @endforeach

                        </select>
                    </div>

                @endif


                <!-- Depot Filter -->
                    <div class="col-md-4">

                    <label for="depot_id">{{__('Filter by Depot')}}</label>

                    <select name="depot_id" id="depot_id" class="form-control">

                        @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                        <option value="">{{__('Select a Company First')}}</option>

                        @else

                        <option value="">{{__('All Depots')}}</option>

                        @foreach($depots as $depot)

                        <option value="{{ $depot->id }}"
                        {{ request('depot_id') == $depot->id ? 'selected' : '' }}>

                        {{ strtoupper($depot->name) }}

                        </option>

                        @endforeach

                        @endif

                    </select>

                </div>


                <!-- Group Filter -->
                <div class="col-md-4">

                    <label for="group_id">{{__('Filter by Group')}}</label>

                    <select name="group_id" id="group_id" class="form-control">

                        @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                        <option value="">{{__('Select a Company First')}}</option>

                        @else

                        <option value="">{{__('All Groups')}}</option>

                        @foreach($groups as $group)

                        <option value="{{ $group->id }}"
                        {{ request('group_id') == $group->id ? 'selected' : '' }}>

                        {{ strtoupper($group->name) }}

                        </option>

                        @endforeach

            @endif

                    </select>

                </div>


                <!-- Filter Buttons -->
                <div class="col-md-4 mt-4">

                    <button type="submit" class="btn btn-primary">
                        {{__('Filter')}}
                    </button>

                    <a href="{{ route('driver-consent-form.index') }}"
                       class="btn btn-secondary">

                        {{__('Reset Filter')}}

                    </a>

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
                                <th class="text-end ">{{__('Action')}}</th>
                                <th>{{__('Driver Name')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Licence Number')}}</th>
                                <th>{{__('Account Number')}}</th>
                                <th>{{__('Reference Number')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('Date of Birth')}}</th>
                                <th>{{__('Current Address')}}</th>
                                <th>{{__('Licence Address')}}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($driverconsent as $driverconsents)
                            <tr>
                                <td>
                                    <div class="action-btn ms-2" style="background-color: #48494B">
                                        <a href="{{ route('driverconsent.pdf.download', $driverconsents->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('Print')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('Print')}}">
                                            <span class="text-white"><i class="fa fa-print"></i></span>
                                        </a>
                                    </div>

                                </td>
                                <td>
                                    {{ ucwords(strtolower($driverconsents->first_name)) ?? '' }}
                                    {{ ucwords(strtolower($driverconsents->middle_name)) ?? '' }}
                                    {{ ucwords(strtolower($driverconsents->surname)) ?? '' }}
                                </td>

                                <td>{{ $driverconsents->companyName }}</td>
                                <td>{{ $driverconsents->driver_licence_no }}</td>
                                <td>{{ $driverconsents->account_number }}</td>
                                <td>{{ $driverconsents->reference_number }}</td>
                                <td>{{ $driverconsents->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($driverconsents->date_of_birth)->format('d/m/Y') }}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="{{ $driverconsents->current_address_line1 }} {{ $driverconsents->current_address_line2 ?? '' }} {{ $driverconsents->current_address_line3 ?? '' }}">
                                        {{ Illuminate\Support\Str::limit(ucwords(strtolower($driverconsents->current_address_line1)) . ' ' . (ucwords(strtolower($driverconsents->current_address_line2)) ?? '') . ' ' . (ucwords(strtolower($driverconsents->current_address_line3)) ?? ''), 30) }}
                                    </span>
                                </td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="{{ $driverconsents->licence_address_line1 }} {{ $driverconsents->licence_address_line2 ?? '' }} {{ $driverconsents->licence_address_line3 ?? '' }}">
                                        {{ Illuminate\Support\Str::limit(ucwords(strtolower($driverconsents->licence_address_line1)) . ' ' . (ucwords(strtolower($driverconsents->licence_address_line2)) ?? '') . ' ' . (ucwords(strtolower($driverconsents->licence_address_line3)) ?? ''), 30) }}
                                    </span>
                                </td>


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
