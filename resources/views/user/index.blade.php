@extends('layouts.admin')

@php
    $profile=\App\Models\Utility::get_file('uploads/avatar');
@endphp

@section('page-title')
    {{__('Manage User')}}
@endsection
@push('script-page')
<style>
    .access{
        background: linear-gradient(141.55deg, #48494B 3.46%, #48494B 99.86%), #48494B !important;
        color: white;
        margin: 2px;
        padding: 5px 10px;
        border-radius: 4px;
    }
</style>
<script>
    $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip(); // Initialize Bootstrap tooltips
        });
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('User')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR')
            <a href="{{ route('user.userlog') }}" class="btn btn-primary btn-sm"
               data-bs-toggle="tooltip" title="{{ __('User Logs History') }}">
                <i class="ti ti-user-check"></i>
            </a>
        @endif
        @can('create user')
            <a href="#" data-size="lg" data-url="{{ route('users.create') }}" data-ajax-popup="true"
               data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row">
    <div class="row" style="margin-bottom: 10px;margin-top:10px;">
        <div class="col-12">
            <!-- Filter Form -->
             @if(Auth::user()->hasRole('company'))
            <form method="GET" action="{{ route('user.index') }}">
                <div class="row">
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
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                        <a href="{{ route('user.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                    </div>
                </div>
            </form>
              @endif
        </div>
    </div>
    <div class="col-9" style="width: 100%">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                        <tr>
                            <th>{{ __('Actions') }}</th>
                            <th>{{ __('Avatar') }}</th>
                                <th>{{ __('Username') }}</th>
                                <th>{{ __('Company Name') }}</th>
                                <th>{{ __('Depot') }}</th>
                                <th>{{ __('Vehicle Group') }}</th>
<th>{{ __('Driver Group') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Last Login') }}</th>
                                <th>{{ __('Created By') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                            <tr @if($user->delete_status == 0) style="background-color: #ff00005c; color: black;" @endif>
                                <td class="float-center">
                                        @can('edit user')
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('users.edit', $user->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit User') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan

                                        @can('delete user')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id],'id'=>'delete-form-'.$user->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete User') }}">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                            {!! Form::close() !!}
                                            </div>
                                        @endcan

                                        <div class="action-btn bg-secondary ms-2">
                                        <a href="#" data-url="{{route('users.reset',\Crypt::encrypt($user->id))}}"
                                           data-ajax-popup="true" data-size="md" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                           data-bs-toggle="tooltip" title="{{__('Reset Password')}}">
                                           <i class="ti ti-key text-white"></i>
                                        </a>
                                    </div>
                                </td>
                                    <td class="text-center">
                                        <img src="{{ !empty($user->avatar) ? asset(Storage::url('uploads/avatar/'.$user->avatar)) : asset(Storage::url('uploads/avatar/avatar.png')) }}"
                                             class="rounded-circle" width="40" height="40">
                                    </td>
                                    <td class="text-center">{{ $user->username }}</td>
                                    <td class="text-center">{{ $user->company->name ?? 'Null' }}</td>
<td class="text-center">
    @php
        // Ensure depot_id is treated as an array
        $depotIds = $user->depot_id;

        if (is_string($depotIds)) {
            // Try JSON decode first (handles JSON arrays like '["143","146"]')
            $depotIds = json_decode($depotIds, true);

            if (!is_array($depotIds)) {
                // If JSON decode fails, assume it's a comma-separated string (e.g., "143,146")
                $depotIds = explode(',', $user->depot_id);
            }
        } elseif (is_int($depotIds)) {
            // If it's a single integer, convert it to an array
            $depotIds = [$depotIds];
        } elseif (!is_array($depotIds)) {
            // Default to empty array if format is unexpected
            $depotIds = [];
        }

        // Fetch depot names
        $depots = \App\Models\Depot::whereIn('id', $depotIds)->pluck('name')->toArray();

        // Display names or 'N/A' if empty
       $depotText = !empty($depots) ? implode(', ', $depots) : 'N/A';
    @endphp
    <span data-bs-toggle="tooltip" title="{{ $depotText }}">
    {{ Str::limit($depotText, 40) }}
</span>
</td>
<td class="text-center">
@php
    $vehicleGroupIds = $user->vehicle_group_id;

    if (is_string($vehicleGroupIds)) {
        $vehicleGroupIds = json_decode($vehicleGroupIds, true);

        if (!is_array($vehicleGroupIds)) {
            $vehicleGroupIds = explode(',', $user->vehicle_group_id);
        }
    } elseif (is_int($vehicleGroupIds)) {
        $vehicleGroupIds = [$vehicleGroupIds];
    } elseif (!is_array($vehicleGroupIds)) {
        $vehicleGroupIds = [];
    }

    $vehicleGroups = \App\Models\VehicleGroup::whereIn('id', $vehicleGroupIds)
                    ->pluck('name')
                    ->toArray();

    $vehicleGroupText = !empty($vehicleGroups) ? implode(', ', $vehicleGroups) : 'N/A';
@endphp
<span data-bs-toggle="tooltip" title="{{ $vehicleGroupText }}">
    {{ Str::limit($vehicleGroupText, 40) }}
</span>
</td>
<td class="text-center">
@php
    $driverGroupIds = $user->driver_group_id;

    if (is_string($driverGroupIds)) {
        $driverGroupIds = json_decode($driverGroupIds, true);

        if (!is_array($driverGroupIds)) {
            $driverGroupIds = explode(',', $user->driver_group_id);
        }
    } elseif (is_int($driverGroupIds)) {
        $driverGroupIds = [$driverGroupIds];
    } elseif (!is_array($driverGroupIds)) {
        $driverGroupIds = [];
    }

    $driverGroups = \App\Models\Group::whereIn('id', $driverGroupIds)
                    ->pluck('name')
                    ->toArray();

   $driverGroupText = !empty($driverGroups) ? implode(', ', $driverGroups) : 'N/A';
@endphp
<span data-bs-toggle="tooltip" title="{{ $driverGroupText }}">
    {{ Str::limit($driverGroupText, 40) }}
</span>
</td>

                                    <td class="text-center">{{ $user->email }}</td>
                                    <td class="text-center">
                                        <span class="badge access">{{ ucfirst($user->type) }}</span>
                                </td>
                                    <td class="text-center">{{ $user->last_login_at ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ optional(\App\Models\User::find($user->created_by))->username ?? 'N/A' }}
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
