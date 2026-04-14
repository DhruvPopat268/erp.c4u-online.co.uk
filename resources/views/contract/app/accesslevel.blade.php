@extends('layouts.admin')
@section('page-title')
    {{__('Manage App Access Level')}}
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
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('App Access Level')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('accesslevel.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection


@section('content')
    <div class="row">
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th class="text-end">{{__('Action')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Manager Access Level')}}</th>
                                <th>{{__('Driver Access Level')}}</th>
                                <th>{{__('Created By')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($accesslevel as $accessLevel)
                                    <tr>
                                        <td class="text-end">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('accesslevel.edit', $accessLevel->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            @if(Auth::user()->type == 'company')
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['accesslevel.destroy', $accessLevel->id]]) !!}
                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $accessLevel->company->name ?? '-' }}</td>
                                        <td class="text-center">
                                            @if(!empty($accessLevel->manager_access))
                                                <div style="display: flex; flex-wrap: wrap; justify-content: center;">
                                                    @foreach($accessLevel->manager_access as $index => $access)
                                                        @if ($index % 3 == 0 && $index != 0)
                                                            </div><div style="display: flex; flex-wrap: wrap; justify-content: center;"> <!-- Create new row every 3 items -->
                                                        @endif
                                                        <span class="badge access">{{ $access }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{ '-' }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(!empty($accessLevel->driver_access))
                                                <div style="display: flex; flex-wrap: wrap; justify-content: center;">
                                                    @foreach($accessLevel->driver_access as $index => $access)
                                                        @if ($index % 3 == 0 && $index != 0)
                                                            </div><div style="display: flex; flex-wrap: wrap; justify-content: center;"> <!-- Create new row every 3 items -->
                                                        @endif
                                                        <span class="badge access">{{ $access }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{ '-' }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $accessLevel->creator->name ?? '-' }}</td>
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
