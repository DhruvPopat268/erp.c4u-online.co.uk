@extends('layouts.admin')
@section('page-title')
    {{__('Manage Depot')}}
@endsection
@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('showVehicleModal'))
        var VehicleModal = new bootstrap.Modal(document.getElementById('VehicleModal'), {});
        VehicleModal.show();
        @endif
    });

    function handleVehicleChoice(choice) {
        if (choice === 'yes') {
            window.location.href = "{{ route('contract.index') }}"; // Redirect to the Vehicle index page
        }
    }

    function openViewModal(contract) {
        document.getElementById('viewName').textContent = contract.name;
        document.getElementById('viewLicenceNumber').textContent = contract.licence_number;
        document.getElementById('viewTrafficArea').textContent = contract.traffic_area;
        document.getElementById('viewContinuationDate').textContent = contract.continuation_date;
        document.getElementById('viewManagerName').textContent = contract.transport_manager_name;
        document.getElementById('viewOperatingCentre').textContent = contract.operating_centre;
        document.getElementById('viewVehicles').textContent = contract.vehicles;
        document.getElementById('viewTrailers').textContent = contract.trailers;
        document.getElementById('viewCompanyName').textContent = contract.types.name;
        document.getElementById('viewStatus').textContent = contract.status;
        document.getElementById('viewCreatedBy').textContent = contract.creator.username;

        var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
        viewModal.show();
    }
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Depot')}}</li>
@endsection
@section('action-btn')
@can('create depot')
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('depot.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Depot')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>

         <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('depot.file.import') }}"  data-ajax-popup="true" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
    </div>
    @endcan
@endsection


@section('content')
    <div class="row">
        <div class="row" style="margin-bottom: 10px;margin-top:10px;">
            <div class="col-12">
                <!-- Filter Form -->
                 @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                <form method="GET" action="{{ route('depot.index') }}">
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
                            <a href="{{ route('depot.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                        </div>
                    </div>
                </form>
                  @endif
            </div>
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th class="text-end">{{__('Action')}}</th>
                                <th>{{__('Depot Name')}}</th>
                                     <th>{{__('Company Name')}}</th>
                                <th>{{__('Licence Number')}}</th>
                                <th>{{__('Traffic Area')}}</th>
                                <th>{{__('depot O-license date')}}</th>
                                <th>{{__('Transport Manager Name')}}</th>
                                <th>{{__('Operating Centre')}}</th>
                                <th>{{__('Vehicles')}}</th>
                                <th>{{__('Trailers')}}</th>
                              <!--// <th>{{__('Company Name')}}</th>-->
                                <th>{{__('Status')}}</th>
                                <th>{{__('Created By')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($contracts as $contract)
                                <tr class="font-style">
                                    <td class="action text-end">
                                        @can('edit depot')
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('depot.edit', $contract->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Depot')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan
                                        @if(Auth::user()->type == 'company')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['depot.destroy', $contract->id]]) !!}
                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                        @endif
                                        <!-- View button -->
                                        <div class="action-btn bg-warning ms-2">
                                            <button type="button" class="btn btn-sm d-inline-flex align-items-center" onclick='openViewModal(@json($contract))' data-bs-toggle="tooltip" title="{{__('View')}}">
                                                <i class="ti ti-eye text-white"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>{{ ucwords(strtolower($contract->name)) }}</td>
                                    <td>{{ strtoupper($contract->types->name ?? '') }}</td>
                                    <td>{{ $contract->licence_number }}</td>
                                    <td>{{ $contract->traffic_area }}</td>
                                    <td>{{ $contract->continuation_date }}</td>
                                    <td>{{ $contract->transport_manager_name }}</td>
                                    <td>{{ $contract->operating_centre }}</td>
                                    <td>{{ $contract->vehicles }}</td>
                                    <td>{{ $contract->trailers }}</td>
                                    <!--<td>{{ strtoupper($contract->types->name ?? '') }}</td>-->
                                    <td>{{ $contract->status }}</td>
                                    <td>{{ $contract->creator->username ?? '' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">{{ __('Depot Details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>{{ __('Name:') }}</strong> <span id="viewName"></span></p>
                    <p><strong>{{ __('Licence Number:') }}</strong> <span id="viewLicenceNumber"></span></p>
                    <p><strong>{{ __('Traffic Area:') }}</strong> <span id="viewTrafficArea"></span></p>
                    <p><strong>{{ __('Continuation Date:') }}</strong> <span id="viewContinuationDate"></span></p>
                    <p><strong>{{ __('Transport Manager Name:') }}</strong> <span id="viewManagerName"></span></p>
                    <p><strong>{{ __('Operating Centre:') }}</strong> <span id="viewOperatingCentre"></span></p>
                    <p><strong>{{ __('Vehicles:') }}</strong> <span id="viewVehicles"></span></p>
                    <p><strong>{{ __('Trailers:') }}</strong> <span id="viewTrailers"></span></p>
                    <p><strong>{{ __('Company Name:') }}</strong> <span id="viewCompanyName"></span></p>
                    <p><strong>{{ __('Status:') }}</strong> <span id="viewStatus"></span></p>
                    <p><strong>{{ __('Created By:') }}</strong> <span id="viewCreatedBy"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
