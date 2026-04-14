@extends('layouts.admin')

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush

@section('page-title')
    {{ __('Assign Policy Detail') }}
@endsection
@push('script-page')

@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="#">{{ __('Assign Policy Detail') }}</a></li>

@endsection

 @section('action-btn')
   <div class="float-end d-flex align-items-center">

</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                        <tr>
                            <th class="text-end ">{{__('Action')}}</th>
                            <th>{{__('Policy Name')}}</th>
                            <th>{{__('Total Accept')}}</th>
                            <th>{{__('Total Decline')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($policyAssignments as $assignment)
                            <tr>
                                <td></td>
                                <td>{{ $assignment->policy_id }}</td>
                                <td></td>
                                <td></td>
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
