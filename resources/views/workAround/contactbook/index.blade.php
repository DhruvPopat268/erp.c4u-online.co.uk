@extends('layouts.admin')
@section('page-title')
    {{__('Manage Contact Book')}}
@endsection
@push('script-page')

@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('workAround')}}</li>
    <li class="breadcrumb-item">{{__('Contact Book')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">
        @can('create contactbook')
        <a href="#" data-size="md" data-url="{{ route('contactbook.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Contact')}}" class="btn btn-sm btn-primary">
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
        <form method="GET" action="{{ route('contactbook.index') }}">
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
                    <a href="{{ route('contactbook.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
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
                                <th>{{__('Designation')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Mobile No')}}</th>
                                <th>{{__('Address')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Created')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($contactbook as $contactbook)
                                <tr>
                                    <td style="text-align: center">
                                        @can('edit contactbook')
                                    <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('contactbook.edit', $contactbook->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan
                                        @can('delete contactbook')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['contactbook.delete', $contactbook->id]]) !!}
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                            {!! Form::close() !!}
                                        </div>
                                        @endcan

                                    </td>
                                    <td style="text-align: left">{{ $contactbook->designation }}</td>
                                    <td style="text-align: left">{{ $contactbook->name }}</td>
                                    <td style="text-align: left">+44 {{ $contactbook->mobile_no }}</td>
                                    <td style="text-align: left">{{ $contactbook->address }}</td>
                                    <td style="text-align: left">{{ !empty($contactbook->types) ? ucwords(strtoupper($contactbook->types->name)) : '' }}</td>
                                    <td style="text-align: left">{{ !empty($contactbook->creator)?$contactbook->creator->username:'' }}</td>
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
