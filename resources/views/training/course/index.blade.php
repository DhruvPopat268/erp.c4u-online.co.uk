@extends('layouts.admin')

@section('page-title')
    {{__('Manage Training Course')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{__('Training Type')}}</a></li>
    <li class="breadcrumb-item"><a href="">{{ !empty($trainingTypes->types) ? ucwords(strtolower($trainingTypes->types->name)) : ' '}}</a></li>

@endsection

@section('action-btn')
    <div class="float-end">
        @can('create training course')
            <a href="#" data-size="md" data-url="{{ route('training.course.create', $trainingTypes->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Training Course')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>

        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>

                                <th width="200px">{{__('Action')}}</th>
                                <th>{{__('Course Name')}}</th>
                                <th>{{__('Duration (in Days)')}}</th>
                                <th>{{__('Created')}}</th>

                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($trainingTypes->TrainingCourse as $trainingType)
                                <tr>
                                    <td style="text-align: center">
                                        @can('edit training course')
                                                <div class="action-btn bg-info ms-2">


                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('training.course.edit',$trainingType->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Training Course')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                </div>
                                            @endcan

                                            @can('delete training course')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['training.course.delete', $trainingType->id]]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$trainingTypes->id}}').submit();" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}">
                                                        <i class="ti ti-trash text-white"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                    </td>
                                    <td style="text-align: center">{{ $trainingType->name }}</td>
                                    <td style="text-align: center">{{ $trainingType->duration }}</td>
                                    <td style="text-align: center">{{ !empty($trainingType->creator)?$trainingType->creator->username:'' }}</td>
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
