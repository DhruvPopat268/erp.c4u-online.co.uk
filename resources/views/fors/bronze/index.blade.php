@extends('layouts.admin')
@section('page-title')
    {{__('Manage Driver Handbook')}}
@endsection
@push('script-page')
<script>
    $(document).ready(function(){
        $(document).on('click', '.assign-policy-btn', function(e){
            e.preventDefault();
            var url = $(this).data('url');
            $('#assignPolicyModal .modal-content').load(url, function(){
                $('#assignPolicyModal').modal('show');
            });
        });
    });
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Driver Handbook')}}</li>
    <li class="breadcrumb-item">{{__('Policies')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">
        @can('create fors')
        <a href="#" data-size="md" data-url="{{ route('forsBronze.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Policy')}}" class="btn btn-sm btn-primary">
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
                                <th >{{__('Policy Name')}}</th>
                               
                                <th>{{__('Company Name')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($bronzePolicies as $bronzePolicy)
                                    <tr>
                                        <td style="text-align: center">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('forsBronze.edit', $bronzePolicy->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('forsBronze.show', $bronzePolicy->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}">
                                                    <span class="text-white"><i class="ti ti-eye"></i></span>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['forsBronze.destroy', $bronzePolicy->id]]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                {!! Form::close() !!}
                                            </div>

                                            <!--<div class="action-btn bg-success ms-2">-->
                                            <!--    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center assign-policy-btn" data-url="{{ route('forsBronze.assign', $bronzePolicy->id) }}" data-bs-toggle="tooltip" title="{{__('Assign Policy')}}">-->
                                            <!--        <i class="ti ti-user-plus text-white"></i>-->
                                            <!--    </a>-->
                                            <!--</div>-->

                                        </td>
                                        <td style="text-align: left">{{ $bronzePolicy->bronze_policy_name }}</td>
                                        
                                        <td style="text-align: left">
                                            {{ $bronzePolicy->company->name ?? 'Admin' }} {{-- Assuming relation: company --}}

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Policy Modal -->
    <div class="modal fade" id="assignPolicyModal" tabindex="-1" aria-labelledby="assignPolicyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Content will be loaded here from "data-url" -->
            </div>
        </div>
    </div>
@endsection
