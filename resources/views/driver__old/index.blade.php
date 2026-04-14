@extends('layouts.admin')
@section('page-title')
    {{__('Manage Driver')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Driver')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">
        @can('import driver')
        <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('driver.file.import') }}" data-ajax-popup="true" data-title="{{__('Import Driver CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        @endcan
        @can('create driver')
          <a href="{{route('driverDataexport.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        @endcan
        @can('create driver')
        <a href="#" data-size="md" data-url="{{ route('driver.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Driver')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan
        {{--  <a href="#" data-size="md" data-url="{{ route('driver.bulkimport') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Bulk Import')}}" class="btn btn-sm btn-success">
            <i class="ti ti-upload"></i> {{__('Bulk Import')}}
        </a>  --}}

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
                                <th style="text-align: center;">{{__(' Driver Name')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Driver Status')}}</th>
                                <th>{{__('NI Number')}}</th>
                                <th>{{__('Post Code')}}</th>
                                <th>{{__('Contact No')}}</th>
                                <th>{{__('Contact Email')}}</th>
                                <th>{{__('Driver DOB')}}</th>
                                <th>{{__('Driver Age')}}</th>
                                <th>{{__('Driver Address')}}</th>
                                <th>{{__('Driver Licence No')}}</th>
                                <th>{{__('Driver Licence Status')}}</th>
                                <th>{{__('Driver Licence expiry')}}</th>
                                <th>{{__('CPC Status')}}</th>
                                <th>{{__('CPC valid to')}}</th>
                                <th>{{__('Tacho Card No')}}</th>
                                <th>{{__('Tacho card valid from')}}</th>
                                <th>{{__('Tacho Card Status')}}</th>
                                <th>{{__('Tacho card valid to')}}</th>
                                <th>{{__('Latest LC Check')}}</th>
                                <th>{{__('Comment')}}</th>
                                <th>{{ __('Created') }} </th>


                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($contracts as $contract)

                                <tr class="font-style">
                                    <td class="action text-end">
                                        @can('edit driver')
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('driver.edit',$contract->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('driver.show',$contract->id) }}"
                                               class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                               data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                                        </div>
                                       @can('delete driver')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['driver.destroy', $contract->id]]) !!}
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                            {!! Form::close() !!}
                                        </div>
                                      @endcan
                                    </td>
                                    <td>{{ ucwords(strtolower($contract->name)) }}</td>
                                    <td>{{ !empty($contract->types) ? ucwords(strtolower($contract->types->name)) : '' }}</td>
                                    <td>{{ $contract->driver_status }}</td>
                                    <td>{{ $contract->ni_number }}</td>
                                    <td>{{ $contract->post_code }}</td>
                                    <td>{{ $contract->contact_no }}</td>
                                    <td>{{ $contract->contact_email }}</td>
                                    <td>{{ $contract->driver_dob }}</td>
                                    <td>
                                        @if (!empty($contract->driver_age))
                                            {{ $contract->driver_age }} Years
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $contract->driver_address }}</td>
                                    <td>{{ $contract->driver_licence_no }}</td>
                                   <td >
    @if($contract->driver_licence_status === 'VALID' || $contract->driver_licence_status === 'Valid')
        <span style="color: green; font-weight:bold;">{{ $contract->driver_licence_status }}</span>
    @elseif($contract->driver_licence_status === 'EXPIRED' || $contract->driver_licence_status === 'Expired')
        <span style="color: red; font-weight:bold;">{{ $contract->driver_licence_status }}</span>
    @elseif($contract->driver_licence_status === 'EXPIRING SOON' || $contract->driver_licence_status === 'Expiring Soon')
        <span style="color: orange; font-weight:bold;">{{ $contract->driver_licence_status }}</span>
    @else
        {{ $contract->driver_licence_status }}
    @endif
</td>


                                    {{--  <td>
                                        @if(is_null($contract->driver_licence_expiry))
                                        @else
                                            {{ Carbon::parse($contract->driver_licence_expiry)->format('d/m/Y') }}
                                        @endif
                                    </td>  --}}
                                    <td>{{ $contract->driver_licence_expiry }}</td>

<td>
    @if($contract->cpc_status === 'VALID' || $contract->cpc_status === 'Valid')
        <span style="color: green; font-weight:bold;">{{ $contract->cpc_status }}</span>
    @elseif($contract->cpc_status === 'EXPIRED' || $contract->cpc_status === 'Expired')
        <span style="color: red; font-weight:bold;">{{ $contract->cpc_status }}</span>
    @elseif($contract->cpc_status === 'EXPIRING SOON' || $contract->cpc_status === 'Expiring Soon')
        <span style="color: orange; font-weight:bold;">{{ $contract->cpc_status }}</span>
    @else
        {{ $contract->cpc_status }}
    @endif
</td>
                                    {{--  <td>
                                        @if(is_null($contract->cpc_validto))
                                        @else
                                            {{ Carbon::parse($contract->cpc_validto)->format('d/m/Y') }}
                                        @endif
                                    </td>  --}}
                                    <td>{{ $contract->cpc_validto }}</td>

                                    <td>{{ $contract->tacho_card_no }}</td>

                                    <td>
                                        @if($contract->tacho_card_valid_from === '-')
                                            -
                                        @else
                                            {{ $contract->tacho_card_valid_from }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($contract->tacho_card_status === 'VALID' || $contract->tacho_card_status === 'Valid')
                                            <span style="color: green; font-weight:bold;">{{ $contract->tacho_card_status }}</span>
                                        @elseif($contract->tacho_card_status === 'EXPIRED' || $contract->tacho_card_status === 'Expired')
                                            <span style="color: red; font-weight:bold;">{{ $contract->tacho_card_status }}</span>
                                        @elseif($contract->tacho_card_status === 'EXPIRING SOON' || $contract->tacho_card_status === 'Expiring Soon')
                                            <span style="color: orange; font-weight:bold;">{{ $contract->tacho_card_status }}</span>
                                        @else
                                            {{ $contract->tacho_card_status }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($contract->tacho_card_valid_to === '-')
                                            -
                                        @else
                                            {{ $contract->tacho_card_valid_to }}
                                        @endif
                                    </td>
                                    {{--  <td>
    @if($contract->lc_check_status === 'VALID' || $contract->lc_check_status === 'Valid')
        <span style="color: green; font-weight:bold;">{{ $contract->lc_check_status }}</span>
    @elseif($contract->lc_check_status === 'EXPIRED' || $contract->lc_check_status === 'Expired')
        <span style="color: red; font-weight:bold;">{{ $contract->lc_check_status }}</span>
    @elseif($contract->lc_check_status === 'EXPIRING SOON' || $contract->lc_check_status === 'Expiring Soon')
        <span style="color: orange; font-weight:bold;">{{ $contract->lc_check_status }}</span>
    @else
        {{ $contract->lc_check_status }}
    @endif
</td>  --}}
                                    <td>
                                        @if(is_null($contract->latest_lc_check))

                                        @else
                                            {{ $contract->latest_lc_check }}
                                        @endif
                                    </td>

                                    <td>{{ $contract->comment }}</td>
                                    <td>{{ !empty($contract->creator)?$contract->creator->username:'' }}</td>



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

