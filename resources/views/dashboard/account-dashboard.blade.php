@extends('layouts.admin')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to calculate percentages
    function calculatePercentages(data) {
        let total = data.reduce((acc, val) => acc + val, 0);
        return data.map(value => ((value / total) * 100).toFixed(2));
    }

    // Pie Chart for Driver Status
    var ctx1 = document.getElementById('driverStatusChart').getContext('2d');
    var driverStatusData = [
        {{ $crm_data['active_driver_count'] }},
        {{ $crm_data['inactive_driver_count'] }},
        {{ $crm_data['archived_driver_count'] }}
    ];
    var driverStatusPercentages = calculatePercentages(driverStatusData);

    var driverStatusChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Active', 'Inactive', 'Archived'],
            datasets: [{
                label: 'Driver Status',
                data: driverStatusData, // Use raw data for counts
                backgroundColor: [
                    'rgb(62, 202, 214)',
                    'rgb(255, 58, 110)',
                    'rgb(255, 181, 0.2)'
                ],
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            var dataset = tooltipItem.dataset;
                            var dataIndex = tooltipItem.dataIndex;
                            var label = dataset.label || '';
                            var count = dataset.data[dataIndex];
                            var percentage = driverStatusPercentages[dataIndex];
                            return `Driver Status Count: ${count}\n\n\n${label}: ${percentage}%`;
                        }
                    }
                }
            }
        }
    });

    // Pie Chart for CPC Card Status
    var ctx2 = document.getElementById('cpccardStatusChart').getContext('2d');
    var cpccardData = [
        {{ $crm_data['expiring_soon_cpc_count'] }},
        {{ $crm_data['expired_cpc_count'] }}
    ];
    var cpccardPercentages = calculatePercentages(cpccardData);

    var cpccardStatusChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: ['Expiring Soon', 'Expired'],
            datasets: [{
                label: 'CPC Card Status',
                data: cpccardData,
                backgroundColor: [
                    'rgb(62, 202, 214)',
                    'rgb(255, 58, 110)',
                ],
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            var dataset = tooltipItem.dataset;
                            var dataIndex = tooltipItem.dataIndex;
                            var label = dataset.label || '';
                            var count = dataset.data[dataIndex];
                            var percentage = cpccardPercentages[dataIndex];
                            return `Cpc Status Count: ${count}\n\n\n${label}: ${percentage}%`;
                        }
                    }
                }
            }
        }
    });

    // Pie Chart for Tacho Card Status
    var ctx3 = document.getElementById('tachocardStatusChart').getContext('2d');
    var tachocardData = [
        {{ $crm_data['expiring_soon_tacho_count'] }},
        {{ $crm_data['expired_tacho_count'] }}
    ];
    var tachocardPercentages = calculatePercentages(tachocardData);

    var tachocardStatusChart = new Chart(ctx3, {
        type: 'pie',
        data: {
            labels: ['Expiring Soon', 'Expired'],
            datasets: [{
                label: 'Tacho Card Status',
                data: tachocardData,
                backgroundColor: [
                    'rgb(62, 202, 214)',
                    'rgb(255, 58, 110)',
                ],
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            var dataset = tooltipItem.dataset;
                            var dataIndex = tooltipItem.dataIndex;
                            var label = dataset.label || '';
                            var count = dataset.data[dataIndex];
                            var percentage = tachocardPercentages[dataIndex];
                            return `Tacho Status Count: ${count}\n\n\n${label}: ${percentage}%`;
                        }
                    }
                }
            }
        }
    });
});

    </script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <!--<li class="breadcrumb-item">{{ __('CRM') }}</li>-->
@endsection
@section('content')
    <div class="row">
        <!-- Dropdown for selecting company -->
        <div class="col-lg-12">
            @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                <form method="GET" action="{{ route('crm.dashboard') }}">
                    <div class="form-group">
                        <label for="company_id">{{ __('Select Company') }}</label>
                        <select name="company_id" id="company_id" class="form-select" onchange="this.form.submit()">
                            <option value="">{{ __('All Companies') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ $selectedCompanyId == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="row">

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Driver Status') }}</small>
                                    <h6 class="m-0">{{ __('Distribution') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <canvas id="driverStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('CPC Card Status') }}</small>
                                    <h6 class="m-0">{{ __('Distribution') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <canvas id="cpccardStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Tacho Card Status') }}</small>
                                    <h6 class="m-0">{{ __('Distribution') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <canvas id="tachocardStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-layout-2"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Company') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['total_company'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-notebook"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Vehicle') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['total_vehicle'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-truck"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Vehicle') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['total_vehicle'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i  class="fas fa-warehouse" style='font-size:15px'></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Operating Centre') }}</h6>
                                </div>
                            </div>
                        </div>
                       <div class="col-auto text-end">
    @if ($selectedCompanyId)
        <p>Total Authorisation Vehicles: 
            {{ isset($crm_data['total_no_of_vehicles']) ? $crm_data['total_no_of_vehicles'] : 'N/A' }}
        </p>
    @endif
    <h4 class="m-0">
        {{ isset($crm_data['total_operating_centers']) ? $crm_data['total_operating_centers'] : 'N/A' }}
    </h4>
</div>

                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-steering-wheel" style="color: #ffffff;"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Driver') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['total_driver'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">

                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-notebook"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Driver Add API Count') }} </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <span style="font-size: x-small;"><b>({{ $selectedCompanyName }})</b></span>
                            <h4 class="m-0">{{ $selectedCompanyApiCallCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-warning">
                                    <i class="fas fa-hourglass-end"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('CPC Status Expiring Soon') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['expiring_soon_cpc_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('CPC Status Expired') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['expired_cpc_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-warning">
                                    <i class="fas fa-hourglass-end"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Tacho Card Status Expiring Soon') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['expiring_soon_tacho_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Tacho Card Status Expired') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['expired_tacho_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">

                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-notebook"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Driver Active Status') }} </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['active_driver_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">

                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-notebook"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Driver InActive Status') }} </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['inactive_driver_count'] }}</h4>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">

                                <div class="theme-avtar bg-danger">
                                    <i class="ti ti-notebook"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Driver Archive Status') }} </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $crm_data['archived_driver_count'] }}</h4>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
