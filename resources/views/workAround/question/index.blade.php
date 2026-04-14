@extends('layouts.admin')
@section('page-title')
    {{__('Manage Description')}}
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
    <li class="breadcrumb-item">{{__('workAround')}}</li>
    <li class="breadcrumb-item">{{__('Description')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">
        @can('create question')
        <a href="#" data-size="md" data-url="{{ route('question.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Description')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th class="text-end ">{{__('Action')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Explanation')}}</th>
                                <th>{{__('Description Type')}}</th>
                                <th>{{__('Reason/image Upload')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($question as $questions)
                                <tr>
                                    <td style="text-align: center">
                                        @can('edit question')
                                    <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('question.edit', $questions->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan
                                        @can('delete question')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['question.delete', $questions->id]]) !!}
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                            {!! Form::close() !!}
                                        </div>
                                        @endcan

                                    </td>
                                    <td style="text-align: left">{{ $questions->name }}</td>
                                     <td style="text-align: left">
                                        <span data-bs-toggle="tooltip" title="{{ $questions->description }}">
                                            {{ Str::limit($questions->description, 30) }}
                                        </span>
                                    </td>                                    <td style="text-align: center">{{ $questions->question_type }}</td>
                                    <td style="text-align: center">{{ $questions->select_reasonimage }}</td>
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
