@extends('layouts.admin')
@section('page-title')
    {{__('Manage Company')}}
@endsection
@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('showdepotModal'))
        var depotModal = new bootstrap.Modal(document.getElementById('depotModal'), {});
        depotModal.show();
        @endif
    });

    function handleDepotChoice(choice) {
        if (choice === 'yes') {
            window.location.href = "{{ route('depot.index') }}"; // Redirect to the depot index page
        }
    }
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Company')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('contractType.create') }}" data-ajax-popup="true" data-ajax-popup="true" data-title="{{__('Create New Company')}}"class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        <a href="{{route('companyDataexport.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('contractType.file.import') }}" data-ajax-popup="true" data-title="{{__('Import Operator CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
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
                                    <th class="text-end ">{{__('Action')}}</th>
                                    <th style="padding-left: 21px;">{{__('Account ID')}}</th>
                                    <th>{{__('Company Name')}}</th>
                                    <th>{{__('Company Status')}}</th>
                                    <th>{{__('ADD API Call Count')}}</th>
                                     <th>{{__('Payment Type')}}</th>
                                    <th>{{__('Coins')}}</th>
                                    <th>{{__('Company Email')}}</th>
                                    <th>{{__('Promotional Email')}}</th>
                                     <th>{{__('PTC Library')}}</th>
                                    <th>{{__('Company Address')}}</th>
                                    <th>{{__('Contact Number')}}</th>
                                    <th>{{__('LC Check Status')}}</th>
                                   
                                    <th>{{__('Manager Name')}}</th>
                                    <!--//<th>{{__('Manager DOB')}}</th>-->
                                    <th>{{__('Created By')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($types as $type)
                                    <tr class="font-style">
                                        <td class="action text-end">
                                            @can('edit company')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('contractType.edit', $type->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Type')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('show company')
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{ route('contractType.show', $type->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}">
                                                        <span class="text-white"><i class="ti ti-eye"></i></span>
                                                    </a>
                                                </div>
                                            @endcan
                                            @if(\Auth::user()->type == 'company')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['contractType.destroy', $type->id]]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $type->account_no }}</td>
                                        <td>{{ strtoupper($type->name) }}</td>
                                        <td>{{ $type->company_status }}</td>
                                        <td style="text-align:center">{{ $type->api_call_count }}</td>
                                         <td>{{ $type->payment_type }}</td>
                                        <td>{{ $type->coins ?? 0 }}</td>
                                        <td>{{ $type->email }}</td>
                                        
                                         <td>{{ $type->promotional_email }}</td>
                                           <td>{{ $type->ptc_library }}</td>
                                        <td>{{ $type->address }}</td>
                                        <td>{{ $type->contact }}</td>
                                        <td>{{ $type->lc_check_status }}</td>
                                       
                                        <td>
                                            @php
                                                $directors = json_decode($type->operator_role, true);
                                            @endphp
                                            @foreach ($directors as $director)
                                                {{ $director }}<br>
                                            @endforeach
                                        </td>
<!--                                        <td>-->
<!--                                            @php-->
<!--                                                $directorsdob = json_decode($type->operator_dob, true);-->
<!--                                            @endphp-->
<!--                                            @foreach ($directorsdob ?? [] as $director)-->
<!--    {{ $director }}<br>-->
<!--@endforeach-->

<!--                                        </td>-->
                                        <td>{{ !empty($type->creator) ? $type->creator->username : '' }}</td>
                                    </tr>
                                    <!-- Director Modal -->
                                    <div class="modal fade" id="directorModal_{{ $type->id }}" tabindex="-1" aria-labelledby="directorModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="directorModalLabel">{{ __('Director  Details') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @php
                                                        $directors = json_decode($type->operator_role, true);
                                                        $directorsdob = json_decode($type->operator_dob, true);
                                                        $directorNumber = 1;
                                                    @endphp
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Depot Modal -->
    <div class="modal fade" id="depotModal" tabindex="-1" aria-labelledby="depotModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="depotModalLabel">{{ __('Add Operating Centre') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Would you like to add a Operating Centre now?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('No') }}</button>
                    <button type="button" class="btn btn-primary" onclick="handleDepotChoice('yes')">{{ __('Yes') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
