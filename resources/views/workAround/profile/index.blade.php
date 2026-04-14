@extends('layouts.admin')
@section('page-title')
    {{__('Manage Profile')}}
@endsection
@push('script-page')
    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip(); // Initialize Bootstrap tooltips
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('walkAround')}}</li>
    <li class="breadcrumb-item">{{__('Profile')}}</li>
@endsection
@php
use Carbon\Carbon;
use Illuminate\Support\Str;
@endphp

@section('action-btn')
    <div class="float-end">
        @can('create profile')
       <a href="#" data-size="md" data-url="{{ route('profile.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Profile')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>

        @endcan
    </div>
@endsection

@section('content')
<div class="row" style="margin-bottom: 10px;margin-top:10px;">
    <div class="col-12">
        <!-- Filter Form -->
         @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
        <form method="GET" action="{{ route('profile.index') }}">
            <div class="row">
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
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                    <a href="{{ route('profile.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                </div>
            </div>
        </form>
          @endif
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
                                <th>{{__('Profile')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Mobile App Enabled')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($profile as $profiles)
                                <tr>
                                    <td style="text-align: center">
                                        @can('edit profile')
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('profile.edit', $profiles->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
@endcan
                                        @can('show profile')
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('profile.show', $profiles->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}">
                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                            </a>
                                        </div>
@endcan
                                       @can('delete profile')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['profile.delete', $profiles->id]]) !!}
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                            {!! Form::close() !!}
                                        </div>
                                        @endcan
                                    </td>
                                    <td style="text-align: center">{{ $profiles->name }}</td>
                                    <td style="text-align: center">
                                        <span data-bs-toggle="tooltip" title="{{ $profiles->description }}">
                                            {{ Str::limit($profiles->description, 30) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center">{{ !empty($profiles->types) ? ucwords(strtoupper($profiles->types->name)) : '' }}</td>
                                    <td style="text-align: center">{{ $profiles->mobile_app_enabled }}</td>
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
