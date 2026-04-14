

@extends('layouts.admin')
@section('page-title')
    {{__('News Letter')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('News Letter')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">

    </div>
@endsection


@section('content')
    <div class="row">
        {{--  <div class="col-3">
            @include('layouts.depot_setup')
        </div>  --}}
        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>

                                <th class="text-end ">{{__('Action')}}</th>

                                <th>{{__('Name')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{ __('Created') }} </th>


                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($emails as $contract)

                            <tr>
                                <td style="text-align: center">
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['newsletter.destroy', $contract->id]]) !!}
                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                        {!! Form::close() !!}
                                    </div>
                                </td>
                                <td style="text-align: center">{{ ucwords(strtolower($contract->name)) }}</td>
                                <td style="text-align: center">{{ ($contract->email) }}</td>

                                <td style="text-align: center">{{ !empty($contract->creator)?$contract->creator->username:'' }}</td>
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

