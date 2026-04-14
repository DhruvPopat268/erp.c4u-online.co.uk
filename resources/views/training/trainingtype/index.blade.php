@extends('layouts.admin')

@section('page-title')
    {{__('Manage Training Type')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Training Type')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create training types')
            <a href="#" data-size="md" data-url="{{ route('trainingTypes.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Training Type')}}" class="btn btn-sm btn-primary">
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
                 @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                <form method="GET" action="{{ route('trainingTypes.index') }}">
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
                            <a href="{{ route('trainingTypes.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                        </div>
                    </div>
                </form>
                  @endif
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>

                                <th width="200px">{{__('Action')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Created')}}</th>

                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($trainingType as $trainingTypes)
                                <tr>
                                    <td style="text-align: center">

                                  @can('manage training course')
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('trainingTypes.course.index', $trainingTypes->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div>
                                        @endcan
                                        @can('edit training types')
                                                <div class="action-btn bg-info ms-2">


                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('trainingTypes.edit',$trainingTypes->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Training Type')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                </div>
                                            @endcan

                                            @can('delete training types')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['trainingTypes.delete', $trainingTypes->id]]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$trainingTypes->id}}').submit();" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}">
                                                        <i class="ti ti-trash text-white"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                    </td>
                                    <td style="text-align: center">{{ $trainingTypes->name }}</td>
                                    <td style="text-align: center">{{ !empty($trainingTypes->types) ? strtoupper($trainingTypes->types->name) : '' }}</td>
                                    <td style="text-align: center">{{ !empty($trainingTypes->creator)?$trainingTypes->creator->username:'' }}</td>
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
