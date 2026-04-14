@extends('layouts.admin')
@section('page-title')
    {{__('Manage Group')}}
@endsection
@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Group')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">

        @can('manage driver')
        <a href="#" data-size="md" data-url="{{ route('group.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Group')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan


    </div>
@endsection



@section('content')
@if ($errors->any())
    <div id="error-message" class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="row">
        {{--  <div class="col-3">
            @include('layouts.depot_setup')
        </div>  --}}
        <div class="row" style="margin-bottom: 10px;">
             <div class="col-12">
            <!-- Filter Form -->
             @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
            <form method="GET" action="{{ route('group.index') }}">
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
                        <a href="{{ route('group.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
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
                                <th class="text-end ">{{__('Action')}}</th>
                                <th>{{__('Group Name')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{ __('Created') }} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($group as $groups)
                            <tr style="text-align: left;">
                                <td class="text-center">
                                  <div class="action-btn bg-info ms-2">
                                     <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('group.edit', $groups->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Type') }}">
                                       <i class="ti ti-pencil text-white"></i>
                                     </a>
                                   </div>

                                   {{--  <div class="action-btn bg-danger ms-2">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['group.destroy', $groups->id]]) !!}
                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="ti ti-trash text-white"></i>
                                    </a>
                                    {!! Form::close() !!}
                                </div>  --}}

                                </td>
                                <td class="text-center">{{ $groups->name }}</td>
                                <td class="text-center">{{ !empty($groups->types) ? strtoupper($groups->types->name) : '' }}</td>
                                <td class="text-center">{{ !empty($groups->creator)?$groups->creator->username:'' }}</td>
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

