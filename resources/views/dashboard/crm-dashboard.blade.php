@extends('layouts.admin')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@push('script-page')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/recharts@2.10.0/umd/Recharts.min.js"></script>

    <style>
        .chart-container {
            width: 100%;
            height: 256px;
        }
    </style>
<script>
    let draggedElement = null;

    function dragStart(event) {
        draggedElement = event.target.closest('.dashboard-card'); // Get the card being dragged
        event.dataTransfer.effectAllowed = 'move'; // Allow move effect
        setTimeout(() => {
            draggedElement.style.opacity = '0.5'; // Dim the dragged element
        }, 0);
    }

    function allowDrop(event) {
        event.preventDefault(); // Allow dropping
    }

    function drop(event) {
        event.preventDefault(); // Prevent default behavior
        const container = document.getElementById('dashboard-cards-container');
        const dropTarget = event.target.closest('.dashboard-card'); // The card that is dropped onto

        if (draggedElement !== dropTarget && dropTarget) {
            // Insert the dragged element before the drop target
            container.insertBefore(draggedElement, dropTarget);
        }

        // Reset the dragged element's opacity
        draggedElement.style.opacity = '1';
    }

</script>

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

    var driverStatusUrls = [
    "{!! route('driver.index', ['driver_status' => 'Active','company_id' => request('company_id')]) !!}",
    "{!! route('driver.index', ['driver_status' => 'InActive','company_id' => request('company_id')]) !!}",
    "{!! route('driver.index', ['driver_status' => 'Archive','company_id' => request('company_id')]) !!}"
];

    var driverStatusChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Active', 'Inactive', 'Archived'],
            datasets: [{
                label: 'Driver Status',
                data: driverStatusData, // Use raw data for counts
                backgroundColor: [
                    'rgb(62, 202, 214)',
                    'rgb(255, 181, 0.2)',
                    'rgb(255, 58, 110)'
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
            },
        onClick: function(evt, elements) {
            if(elements.length > 0) {
                var index = elements[0].index;
                var url = driverStatusUrls[index];
                window.location.href = url;
            }
        }
    }
});

var ctx2 = document.getElementById('cpccardStatusChart').getContext('2d');
var cpccardData = [
    {{ $crm_data['expiring_soon_cpc_count'] }},
    {{ $crm_data['expired_cpc_count'] }}
];
var cpccardPercentages = calculatePercentages(cpccardData);

// Define the URLs for each slice
var cpccardUrls = [
    "{!! route('driver.index', ['cpc_status' => 'EXPIRING SOON','company_id' => request('company_id')]) !!}",
    "{!! route('driver.index', ['cpc_status' => 'EXPIRED','company_id' => request('company_id')]) !!}"
];


var cpccardStatusChart = new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: ['Expiring Soon', 'Expired'],
        datasets: [{
            label: 'CPC Card Status',
            data: cpccardData,
            backgroundColor: [
                'rgb(255, 181, 0.2)',
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
        },
        onClick: function(evt, elements) {
            if(elements.length > 0) {
                var index = elements[0].index;
                var url = cpccardUrls[index];
                window.location.href = url;
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

    var tachocardUrls = [
    "{!! route('driver.index', ['tacho_card_status' => 'EXPIRING SOON','company_id' => request('company_id')]) !!}",
    "{!! route('driver.index', ['tacho_card_status' => 'EXPIRED','company_id' => request('company_id')]) !!}"
];

    var tachocardStatusChart = new Chart(ctx3, {
        type: 'pie',
        data: {
            labels: ['Expiring Soon', 'Expired'],
            datasets: [{
                label: 'Tacho Card Status',
                data: tachocardData,
                backgroundColor: [
                    'rgb(255, 181, 0.2)',
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
            },
        onClick: function(evt, elements) {
            if(elements.length > 0) {
                var index = elements[0].index;
                var url = tachocardUrls[index];
                window.location.href = url;
            }
        }
    }
});
});

    </script>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Timeframe selection
        function setTimeframe(timeframe) {
            document.getElementById('day-btn').classList.remove('bg-blue-600', 'text-white');
            document.getElementById('month-btn').classList.remove('bg-blue-600', 'text-white');
            document.getElementById('year-btn').classList.remove('bg-blue-600', 'text-white');

            document.getElementById('day-btn').classList.add('bg-gray-200');
            document.getElementById('month-btn').classList.add('bg-gray-200');
            document.getElementById('year-btn').classList.add('bg-gray-200');

            document.getElementById(timeframe + '-btn').classList.remove('bg-gray-200');
            document.getElementById(timeframe + '-btn').classList.add('bg-blue-600', 'text-white');

            // Update all timeframe texts
            const timeframeTexts = document.querySelectorAll('#timeframe-text');
            timeframeTexts.forEach(el => el.textContent = timeframe);

            // Here you would typically reload data based on the timeframe
            console.log('Timeframe changed to:', timeframe);
        }



        // PCN Pie Chart
         {{--   const pcnPieCtx = document.getElementById('pcnPieChart').getContext('2d');
        const pcnPieChart = new Chart(pcnPieCtx, {
            type: 'pie',
            data: {
                labels: ['DVSA', 'Local Council', 'Police', 'Other'],
                datasets: [{
                    data: [35, 25, 15, 10],
                    backgroundColor: [
                        '#8884d8',
                        '#83a6ed',
                        '#8dd1e1',
                        '#82ca9d'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${percentage}% (${value})`;
                            }
                        }
                    }
                }
            }
        });  --}}

        const ctx = document.getElementById('pcnPieChart').getContext('2d');

    const data = {
                labels: ['DVSA', 'Local Council', 'Police', 'Other'],
        datasets: [{
            label: 'Issuing Authority',
            data: @json(array_values($crm_data['pcn_issuing_counts'])),
            backgroundColor: [
                '#8884d8',
                        '#83a6ed',
                        '#8dd1e1',
                        '#82ca9d'
            ],
            borderWidth: 1
        }]
    };

    const config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.chart._metasets[context.datasetIndex].total;
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    };
 new Chart(ctx, config);
        // PCN Line Chart
        // Get current year dynamically
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth(); // 0-11
const monthlyPCNs = @json($crm_data['monthly_pcns']);
const monthlyWorkaround = @json($crm_data['monthly_total_workaround']);
const monthlyPendingWorkaround = @json($crm_data['monthly_pending_workaround']);
const monthlyCompletedWorkaround = @json($crm_data['monthly_completed_workaround']);
const monthlyClosedPCNs = @json($crm_data['monthly_closed_pcns']);
const monthlyOutstandingPCNs = @json($crm_data['monthly_outstanding_pcns']);
const monthlyData = @json($crm_data['monthly_chart_data']);

// Generate labels for current year up to current month
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const currentYearLabels = monthNames.slice(0, currentMonth + 1).map(month => `${month} ${currentYear}`);

// Generate sample data based on current month count
const generateData = (baseValue, variance) => {
    return Array.from({length: currentMonth + 1}, (_, i) =>
        Math.floor(baseValue + Math.random() * variance + (i * 2))
    );
};

 const pcnLineCtx = document.getElementById('pcnLineChart').getContext('2d');
    const pcnLineChart = new Chart(pcnLineCtx, {
        type: 'line',
        data: {
            labels: currentYearLabels,
            datasets: [
                {
                    label: 'Total PCNs',
                    data: monthlyPCNs,
                    borderColor: '#8884d8',
                    backgroundColor: 'rgba(136, 132, 216, 0.1)',
                    tension: 0.1,
                    fill: true
                },
                {
                    label: 'Closed PCNs',
                    data: monthlyClosedPCNs,
                    borderColor: '#82ca9d',
                    backgroundColor: 'rgba(130, 202, 157, 0.1)',
                    tension: 0.1,
                    fill: true
                },
                {
                    label: 'Pending',
                    data: monthlyOutstandingPCNs, // Replace if you have real data
                    borderColor: '#ffc658',
                    backgroundColor: 'rgba(255, 198, 88, 0.1)',
                    tension: 0.1,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `PCN Tracking - Current Year ${currentYear}`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of PCNs'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            }
        }
    });

           // Workaround Chart
const workaroundCtx = document.getElementById('workaroundChart').getContext('2d');
const workaroundChart = new Chart(workaroundCtx, {
    type: 'line',
    data: {
        labels: currentYearLabels,
        datasets: [
            {
                label: 'Total Walkarounds',
                data: monthlyWorkaround,
                borderColor: '#8884d8',
                backgroundColor: 'rgba(136, 132, 216, 0.1)',
                tension: 0.1,
                fill: true
            },
            {
                label: 'Pending',
                data: monthlyPendingWorkaround,
                borderColor: '#ffc658',
                backgroundColor: 'rgba(255, 198, 88, 0.1)',
                tension: 0.1,
                fill: true
            },
            {
                label: 'Completed',
                data: monthlyCompletedWorkaround,
                borderColor: '#82ca9d',
                backgroundColor: 'rgba(130, 202, 157, 0.1)',
                tension: 0.1,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true
                }
            }
        }
    });
        // Gender Bar Chart
        const genderBarCtx = document.getElementById('genderBarChart').getContext('2d');
        const genderBarChart = new Chart(genderBarCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Accept', 'Reassigned'],
                datasets: [{
                    label: 'Count',
                    data: [{{ $crm_data['policy_pending_status'] }}, {{ $crm_data['policy_accept_status'] }}, {{ $crm_data['policy_reassigned_status'] }}],
                    backgroundColor: [
                        '#0088FE',
                        '#FF8042',
                        '#00C49F'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Training Chart
        const trainingCtx = document.getElementById('trainingChart').getContext('2d');
        const trainingChart = new Chart(trainingCtx, {
            type: 'bar',
            data: {
                labels: currentYearLabels,
                datasets: [
                    {
                        label: 'Completed',
                        data: monthlyData.Complete,
                        backgroundColor: '#4ade80',
                        stack: 'a'
                    },
                    {
                        label: 'Pending',
                        data: monthlyData.Pending,
                        backgroundColor: '#facc15',
                        stack: 'a'
                    },
                    {
                        label: 'Declined',
                        data: monthlyData.Decline,
                        backgroundColor: '#f87171',
                        stack: 'a'
                    },

                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        });

        // CPC Chart (Doughnut)

    const validCount = {{ $crm_data['valid_cpc_count'] ?? 0 }};
    const expiredCount = {{ $crm_data['expired_cpc_count'] ?? 0 }};
    const totalCount = validCount + expiredCount;
    const validPercentage = totalCount > 0 ? Math.round((validCount / totalCount) * 100) : 0;
    const expiredPercentage = 100 - validPercentage;

    const cpcCtx = document.getElementById('cpcChart').getContext('2d');
    const cpcChart = new Chart(cpcCtx, {
        type: 'doughnut',
        data: {
            labels: ['Valid', 'Expired'],
            datasets: [{
                data: [validPercentage, expiredPercentage],
                backgroundColor: [
                    '#3b82f6',
                    '#eee'
                ],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Set percentage in center manually
    document.getElementById('validPercentage').innerText = validPercentage + '%';


    </script>



<style>
    .dashboard-card {
    cursor: grab;
    transition: transform 0.2s ease;
}

.dashboard-card:active {
    cursor: grabbing;
    opacity: 0.7;
}
 :root {
            --primary-color: #3498db;
            --danger-color: #ff3a6e;
            --warning-color: #ffb500;
            --success-color: #2ecc71;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #333;
            --light-text: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .dashboard-title {
            font-size: 24px;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-display {
            font-size: 14px;
            color: var(--light-text);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .company-selector {
            margin-bottom: 20px;
        }

        .company-selector select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            outline: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-title {
            font-size: 16px;
            color: var(--light-text);
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .trend-up {
            color: var(--success-color);
        }

        .trend-down {
            color: var(--danger-color);
        }

        .trend-neutral {
            color: var(--light-text);
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .chart-container {
            position: relative;
            height: 200px;
            width: 100%;
        }

        .status-legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .status-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .status-button {
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            color: var(--text-color);
            font-weight: 600;
            background-color: #f0f0f0;
            border: none;
        }

        .status-count {
            font-size: 20px;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .active-status {
            background-color: #e3f2fd;
            color: #3498db;
        }

        .inactive-status {
            background-color: #fff8e1;
            color: #ffb500;
        }

        .archive-status {
            background-color: #ffebee;
            color: #ff3a6e;
        }

        .valid-status {
            background-color: #e3f2fd;
            color: #3498db;
        }

        .expiring-status {
            background-color: #fff8e1;
            color: #ffb500;
        }

        .expired-status {
            background-color: #ffebee;
            color: #ff3a6e;
        }

        .icon-container {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .icon-blue {
            background-color: #3498db;
        }

        .icon-green {
            background-color: #2ecc71;
        }

        .icon-orange {
            background-color: #f39c12;
        }

        .icon-red {
            background-color: #e74c3c;
        }
</style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('PTC') }}</li>
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
                                       {{ strtoupper($company->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>

    </div>

             <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Workarounds & Defects Section -->
<div class="bg-white rounded-lg shadow p-6">

            <div class="flex items-center mb-4">
                <i data-lucide="alert-triangle" class="mr-2 text-orange-500"></i>
                <h2 class="text-xl font-bold text-gray-800">Walkarounds & Defects</h2>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
        {{-- Total Walkarounds --}}
        <a href="{{ route('viewworkaround.index', ['company_id' => request('company_id'), 'filter' => 'total']) }}"
           class="block bg-blue-50 p-4 rounded-lg hover:bg-blue-100 transition">
                    <p class="text-sm text-gray-500">Total Walkarounds</p>
                    <p class="text-2xl font-bold">{{ $crm_data['total_work_around_stores'] }}</p>
        </a>

        {{-- Pending --}}
        <a href="{{ route('viewworkaround.index', ['company_id' => request('company_id'), 'filter' => 'pending']) }}"
           class="block bg-yellow-50 p-4 rounded-lg hover:bg-yellow-100 transition">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-bold">{{ $crm_data['pending_uploaded_date'] }}</p>
        </a>

        {{-- Completed --}}
        <a href="{{ route('viewworkaround.index', ['company_id' => request('company_id'), 'filter' => 'completed']) }}"
           class="block bg-green-50 p-4 rounded-lg hover:bg-green-100 transition">
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-bold">{{ $crm_data['complated_uploaded_date'] }}</p>
        </a>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
        {{-- Defects Found --}}
        <a href="{{ route('viewworkaround.index', ['company_id' => request('company_id'), 'filter' => 'defects_found']) }}"
           class="block bg-red-50 p-4 rounded-lg hover:bg-red-100 transition">
                    <p class="text-sm text-gray-500">Defects Found</p>
                    <p class="text-2xl font-bold">{{ $crm_data['defects_count'] }}</p>
        </a>

        {{-- Rectified Defects --}}
        <a href="{{ route('viewworkaround.index', ['company_id' => request('company_id'), 'filter' => 'rectified']) }}"
           class="block bg-indigo-50 p-4 rounded-lg hover:bg-indigo-100 transition">
                    <p class="text-sm text-gray-500">Rectified Defects</p>
                    <p class="text-2xl font-bold">{{ $crm_data['rectified'] }}</p>
        </a>
            </div>

            <div class="chart-container">
                <canvas id="workaroundChart"></canvas>
            </div>
        </div>


        <!-- PCN Section -->
<div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <i data-lucide="file-text" class="mr-2 text-blue-500"></i>
                <h2 class="text-xl font-bold text-gray-800">Penalty Charge Notices (PCN)</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- 🔵 Total PCNs -->
        <a href="{{ route('pcn.index', ['company_id' => request('company_id')]) }}"
           class="bg-blue-50 p-4 rounded-lg block hover:bg-blue-100 transition">
                    <p class="text-sm text-gray-500">Total PCNs</p>
            <p class="text-2xl font-bold text-blue-700">{{ $crm_data['total_pcns'] }}</p>
        </a>

        <!-- 🟢 Resolved PCNs -->
        <a href="{{ route('pcn.index', ['company_id' => request('company_id'), 'status' => 'Closed']) }}"
           class="bg-green-50 p-4 rounded-lg block hover:bg-green-100 transition">
                    <p class="text-sm text-gray-500">Resolved PCNs</p>
            <p class="text-2xl font-bold text-green-700">{{ $crm_data['pcn_status'] }}</p>
        </a>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="chart-container">
                    <canvas id="pcnPieChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="pcnLineChart"></canvas>
                </div>
            </div>
        </div>


        <!-- FORS Policy & Driver Gender Section -->
        <div class="bg-white rounded-lg shadow p-6 cursor-pointer"
     onclick="window.location.href='{{ route('fors.assignpolicy.view', ['company_id' => request('company_id')]) }}'">
            <div class="flex items-center mb-4">
                <i data-lucide="users" class="mr-2 text-purple-500"></i>
                <h2 class="text-xl font-bold text-gray-800">Policy Library</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-purple-50 p-4 rounded-lg flex flex-col items-center justify-center">
                    <p class="text-sm text-gray-500 mb-2">Number of policies assigned</p>
                    <div class="text-center">
                        <p class="text-4xl font-bold"> {{ $crm_data['policy_pending_status'] + $crm_data['policy_accept_status'] + $crm_data['policy_reassigned_status'] }}</p>
                        <p class="text-lg font-medium mt-2">{{ $crm_data['total_driver'] }} of {{ $crm_data['policy_accept_status'] }} Total Accepted</p>
                    </div>
                    <div class="mt-4 bg-gray-200 rounded-full h-4 w-full">
                       @php
                            $total = $crm_data['policy_pending_status'] + $crm_data['policy_accept_status'] + $crm_data['policy_reassigned_status'];
                            $accepted = $crm_data['policy_accept_status'];
                            $percentage = $total > 0 ? ($accepted / $total) * 100 : 0;
                        @endphp

                        <div class="bg-gray-300 h-4 rounded-full w-full">
                            <div class="bg-purple-500 h-4 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>

                        <!--<p class="mt-2 text-sm text-gray-700">{{ $accepted }} of {{ $total }} Drivers</p>-->
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="genderBarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Driver Training Section -->
<div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <i data-lucide="award" class="mr-2 text-green-500"></i>
                <h2 class="text-xl font-bold text-gray-800">Driver Training</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- ✅ Completed --}}
        <div class="bg-green-50 p-4 rounded-lg cursor-pointer hover:bg-green-100 transition"
             onclick="window.location.href='{{ route('training.history.index', ['company_id' => request('company_id'), 'status' => 'Complete']) }}'">
                    <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold text-green-700">{{ $crm_data['training_complete_status'] }}</p>
                    <div class="mt-2 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 80%"></div>
                    </div>
                </div>

        {{-- ✅ Pending --}}
        <div class="bg-yellow-50 p-4 rounded-lg cursor-pointer hover:bg-yellow-100 transition"
             onclick="window.location.href='{{ route('training.history.index', ['company_id' => request('company_id'), 'status' => 'Pending']) }}'">
                    <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $crm_data['training_pending_status'] }}</p>
                    <div class="mt-2 bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: 15%"></div>
                    </div>
                </div>

        {{-- ✅ Declined --}}
        <div class="bg-red-50 p-4 rounded-lg cursor-pointer hover:bg-red-100 transition"
             onclick="window.location.href='{{ route('training.history.index', ['company_id' => request('company_id'), 'status' => 'Decline']) }}'">
                    <p class="text-sm text-gray-500">Declined</p>
            <p class="text-2xl font-bold text-red-700">{{ $crm_data['training_decline_status'] }}</p>
                    <div class="mt-2 bg-gray-200 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: 5%"></div>
                    </div>
                </div>
                {{--  <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Resigned</p>
                    <p class="text-2xl font-bold">7</p>
                    <div class="mt-2 bg-gray-200 rounded-full h-2">
                        <div class="bg-gray-500 h-2 rounded-full" style="width: 2%"></div>
                    </div>
                </div>  --}}
            </div>

            <div class="chart-container">
                <canvas id="trainingChart"></canvas>
            </div>
        </div>

        <!-- Vehicle Compliance & Expiries -->
        @php
                $overallValid = $crm_data['total_taxDueDate_status_valid']
                            + $crm_data['total_annual_test_status_valid']
                            + $crm_data['total_tacho_status_valid']
                            + $crm_data['total_dvs_pss_status_valid']
                            + $crm_data['total_insurance_status_valid']
                            + $crm_data['total_PMI_status_valid']
                            + $crm_data['total_brake_test_status_valid'];

                $overallExpired = $crm_data['total_taxDueDate_status_expired']
                                + $crm_data['total_annual_test_status_expired']
                                + $crm_data['total_tacho_status_expired']
                                + $crm_data['total_dvs_pss_status_expired']
                                + $crm_data['total_insurance_status_expired']
                                + $crm_data['total_PMI_status_expired']
                                + $crm_data['total_brake_test_status_expired'];

                $overallExpiring = $crm_data['total_taxDueDate_status_expiring_soon']
                                + $crm_data['total_annual_test_status_expiring_soon']
                                + $crm_data['total_tacho_status_expiring_soon']
                                + $crm_data['total_dvs_pss_status_expiring_soon']
                                + $crm_data['total_insurance_status_expiring_soon']
                                + $crm_data['total_PMI_status_expiring_soon']
                                + $crm_data['total_brake_test_status_expiring_soon'];

                $overallTotal = $overallValid + $overallExpired + $overallExpiring;
                $overallCompliance = $overallTotal > 0 ? ($overallValid / $overallTotal) * 100 : 0;
            @endphp
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <i data-lucide="truck" class="mr-2 text-indigo-500"></i>
            <h2 class="text-xl font-bold text-gray-800">Vehicle Compliance & Expiries</h2>
        </div>
        <div class="text-sm text-gray-500">
            Total Vehicles: <span class="font-bold text-indigo-600">{{ $crm_data['total_vehicle'] }}</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <!-- Total Active -->
    <div
        class="bg-indigo-50 p-4 rounded-lg cursor-pointer hover:bg-indigo-100 transition"
        onclick="window.location.href='{{ route('contract.index', ['company_id' => request('company_id')]) }}'">
            <p class="text-sm text-gray-500">Total Active</p>
            <p class="text-2xl font-bold text-indigo-700">{{ $crm_data['total_vehicle'] }}</p>
        </div>

    <!-- Active -->
   <div
    class="bg-green-50 p-4 rounded-lg text-center cursor-pointer hover:bg-green-100 transition"
    onclick="window.location.href='{{ route('contract.index', [
        'company_id' => request('company_id'),
        'vehicle_status' => 'active_status'
    ]) }}'">
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold">
                    {{ $crm_data['total_vehicle'] - $crm_data['archived_Archive_count'] }}
                </p>
            </div>


    <!-- Archived -->
    <div
        class="bg-gray-200 p-4 rounded-lg text-center cursor-pointer hover:bg-gray-300 transition"
        onclick="window.location.href='{{ route('contract.index', ['company_id' => request('company_id'), 'vehicle_status' => 'Archive']) }}'">
                <p class="text-sm text-gray-500">Archived</p>
                <p class="text-2xl font-bold">{{ $crm_data['archived_Archive_count'] }}</p>
            </div>

    <!-- Overall Compliance -->
            <div class="bg-red-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Overall Compliance</p>
                <p class="text-2xl font-bold">{{ number_format($overallCompliance, 1) }}%</p>
                {{--  <p class="text-xs text-gray-500 mt-1">4.1% of fleet</p>  --}}
            </div>

        {{--  <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Fully Compliant</p>
            <p class="text-2xl font-bold text-green-700">98</p>
            <div class="text-xs text-gray-500 mt-1">
                <span class="text-green-500">81.7%</span> of fleet
            </div>
        </div>  --}}
        {{--  <div class="bg-yellow-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Issues Pending</p>
            <p class="text-2xl font-bold text-yellow-700">12</p>
            <div class="text-xs text-gray-500 mt-1">
                <span class="text-yellow-500">10.0%</span> of fleet
            </div>
        </div>  --}}
        {{--  <div class="bg-red-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Non-Compliant</p>
            <p class="text-2xl font-bold text-red-700">10</p>
            <div class="text-xs text-gray-500 mt-1">
                <span class="text-red-500">8.3%</span> of fleet
            </div>
        </div>  --}}
    </div>

    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Expiry Status</h3>


            <!--<div class="text-sm font-medium text-indigo-600">-->
            <!--    Overall Compliance: <span class="font-bold">{{ number_format($overallCompliance, 1) }}%</span>-->
            <!--</div>-->

        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Type</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Valid</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Expired</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Expiring Soon</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $valid = $crm_data['total_taxDueDate_status_valid'];
                        $expired = $crm_data['total_taxDueDate_status_expired'];
                        $expiringSoon = $crm_data['total_taxDueDate_status_expiring_soon'];
                        $total = $valid + $expired + $expiringSoon;
                        $validPercent = $total > 0 ? ($valid / $total) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Road Tax</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $valid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $expired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $expiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($validPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($validPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                   @php
                        $motValid = $crm_data['total_annual_test_status_valid'];
                        $motExpired = $crm_data['total_annual_test_status_expired'];
                        $motExpiringSoon = $crm_data['total_annual_test_status_expiring_soon'];
                        $motTotal = $motValid + $motExpired + $motExpiringSoon;
                        $motValidPercent = $motTotal > 0 ? ($motValid / $motTotal) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">MOT</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $motValid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $motExpired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $motExpiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($motValidPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($motValidPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                    @php
                        $tachoValid = $crm_data['total_tacho_status_valid'];
                        $tachoExpired = $crm_data['total_tacho_status_expired'];
                        $tachoExpiringSoon = $crm_data['total_tacho_status_expiring_soon'];
                        $tachoTotal = $tachoValid + $tachoExpired + $tachoExpiringSoon;
                        $tachoValidPercent = $tachoTotal > 0 ? ($tachoValid / $tachoTotal) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Tacho Calibration</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $tachoValid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $tachoExpired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $tachoExpiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($tachoValidPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($tachoValidPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                     @php
                        $dvsValid = $crm_data['total_dvs_pss_status_valid'];
                        $dvsExpired = $crm_data['total_dvs_pss_status_expired'];
                        $dvsExpiringSoon = $crm_data['total_dvs_pss_status_expiring_soon'];
                        $dvsTotal = $dvsValid + $dvsExpired + $dvsExpiringSoon;
                        $dvsValidPercent = $dvsTotal > 0 ? ($dvsValid / $dvsTotal) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">DVS PSS Permit Expiry</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $dvsValid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $dvsExpired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $dvsExpiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($dvsValidPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($dvsValidPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                    @php
                        $insuranceValid = $crm_data['total_insurance_status_valid'];
                        $insuranceExpired = $crm_data['total_insurance_status_expired'];
                        $insuranceExpiringSoon = $crm_data['total_insurance_status_expiring_soon'];
                        $insuranceTotal = $insuranceValid + $insuranceExpired + $insuranceExpiringSoon;
                        $insuranceValidPercent = $insuranceTotal > 0 ? ($insuranceValid / $insuranceTotal) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Insurance</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $insuranceValid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $insuranceExpired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $insuranceExpiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($insuranceValidPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($insuranceValidPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                      @php
                        $pmiValid = $crm_data['total_PMI_status_valid'];
                        $pmiExpired = $crm_data['total_PMI_status_expired'];
                        $pmiExpiringSoon = $crm_data['total_PMI_status_expiring_soon'];
                        $pmiTotal = $pmiValid + $pmiExpired + $pmiExpiringSoon;
                        $pmiValidPercent = $pmiTotal > 0 ? ($pmiValid / $pmiTotal) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">PMI Due</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $pmiValid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $pmiExpired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $pmiExpiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($pmiValidPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($pmiValidPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                   @php
                        $brakeValid = $crm_data['total_brake_test_status_valid'];
                        $brakeExpired = $crm_data['total_brake_test_status_expired'];
                        $brakeExpiringSoon = $crm_data['total_brake_test_status_expiring_soon'];
                        $brakeTotal = $brakeValid + $brakeExpired + $brakeExpiringSoon;
                        $brakeValidPercent = $brakeTotal > 0 ? ($brakeValid / $brakeTotal) * 100 : 0;
                    @endphp

                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">Brake Test Due</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $brakeValid }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $brakeExpired }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $brakeExpiringSoon }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full bg-green-500" style="width: {{ number_format($brakeValidPercent, 2) }}%"></div>
                                </div>
                                <span class="text-xs font-medium">{{ number_format($brakeValidPercent, 1) }}%</span>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    {{--  <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Vehicle Status</h3>
            <div class="text-sm">
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> Active
                <span class="inline-block w-3 h-3 bg-gray-300 rounded-full mx-1 ml-3"></span> Inactive
                <span class="inline-block w-3 h-3 bg-gray-500 rounded-full mx-1 ml-3"></span> Archived
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold">{{ $crm_data['total_vehicle'] }}</p>
                <p class="text-xs text-gray-500 mt-1">79.2% of fleet</p>
            </div>
            <div class="bg-gray-100 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Inactive</p>
                <p class="text-2xl font-bold">20</p>
                <p class="text-xs text-gray-500 mt-1">16.7% of fleet</p>
            </div>
            <div class="bg-gray-200 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Archived</p>
                <p class="text-2xl font-bold">{{ $crm_data['archived_Archive_count'] }}</p>
                <p class="text-xs text-gray-500 mt-1">4.1% of fleet</p>
            </div>
        </div>
    </div>  --}}

    {{--  <!-- Monthly Maintenance Summary -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-3">Monthly Maintenance</h3>
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Scheduled</p>
                    <p class="text-xl font-bold text-blue-700">18</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-xl font-bold text-green-700">12</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-xl font-bold text-yellow-700">6</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Compliance</p>
                    <p class="text-xl font-bold text-blue-700">66.7%</p>
                </div>
            </div>
        </div>
    </div>  --}}
</div>

        <!-- Driver Cards & Certificates -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <i data-lucide="file-check" class="mr-2 text-teal-500"></i>
                <h2 class="text-xl font-bold text-gray-800">Driver Cards & Certificates</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-lg shadow cursor-pointer"
     onclick="window.location.href='{{ route('driver.index', ['company_id' => request('company_id')]) }}'">
                    <div class="p-4 border-b border-gray-200 bg-teal-50">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i data-lucide="clock" class="w-5 h-5 mr-2 text-teal-600"></i>
                            Tachograph Cards
                        </h3>
                    </div>
                    @php
                        $valid = $crm_data['active_driver_count']; // or whatever 'valid' source
                        $expired = $crm_data['expired_cpc_count'];
                        $expiring = $crm_data['expiring_soon_tacho_count'];

                        $total = $valid + $expired + $expiring;

                        $validPercent = $total > 0 ? ($valid / $total) * 100 : 0;
                        $expiredPercent = $total > 0 ? ($expired / $total) * 100 : 0;
                        $expiringPercent = $total > 0 ? ($expiring / $total) * 100 : 0;
                    @endphp


                    <div class="p-4">
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <div class="bg-green-100 p-2 rounded text-center">
                                <p class="text-xs text-gray-600">Valid</p>
                                <p class="text-xl font-bold text-green-800">{{ $valid }}</p>
                            </div>
                            <div class="bg-yellow-100 p-2 rounded text-center">
                                <p class="text-xs text-gray-600">Expiring</p>
                                <p class="text-xl font-bold text-yellow-800">{{ $expiring }}</p>
                            </div>
                            <div class="bg-red-100 p-2 rounded text-center">
                                <p class="text-xs text-gray-600">Expired</p>
                                <p class="text-xl font-bold text-red-800">{{ $expired }}</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="mb-1 text-xs flex justify-between">
                                <span>Status</span>
                                <span>{{ number_format($validPercent, 1) }}% Valid</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 flex overflow-hidden">
                                <div class="bg-green-600 h-2" style="width: {{ $validPercent }}%"></div>
                                <div class="bg-yellow-500 h-2" style="width: {{ $expiringPercent }}%"></div>
                                <div class="bg-red-600 h-2" style="width: {{ $expiredPercent }}%"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow cursor-pointer"
     onclick="window.location.href='{{ route('driver.index', ['company_id' => request('company_id')]) }}'">
                    <div class="p-4 border-b border-gray-200 bg-blue-50">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i data-lucide="award" class="w-5 h-5 mr-2 text-blue-600"></i>
                            CPC Certificates
                        </h3>
                    </div>
                    <div class="p-4">
                        @php
                            $valid = $crm_data['valid_cpc_count'] ?? 0;
                            $expired = $crm_data['expired_cpc_count'] ?? 0;
                            $total = $valid + $expired;
                            $validPercentage = $total > 0 ? round(($valid / $total) * 100) : 0;
                        @endphp

                    <div class="flex justify-center mb-4"   >
                    <div class="relative h-32 w-32">
                        <canvas id="cpcChart"></canvas>
                        <div class="absolute top-0 left-0 w-full h-full flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-3xl font-bold">{{ $validPercentage }}%</div>
                                <div class="text-xs text-gray-500">Valid</div>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-green-100 p-2 rounded text-center">
                            <p class="text-xs text-gray-600">Valid</p>
                            <p class="text-xl font-bold text-green-800">{{ $valid }}</p>
                        </div>
                        <div class="bg-red-100 p-2 rounded text-center">
                            <p class="text-xs text-gray-600">Expired</p>
                            <p class="text-xl font-bold text-red-800">{{ $expired }}</p>
                        </div>
                    </div>

                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg shadow cursor-pointer"
     onclick="window.location.href='{{ route('depot.index', ['company_id' => request('company_id')]) }}'">
                    <div class="p-4 border-b border-gray-200 bg-purple-50">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i data-lucide="truck" class="w-5 h-5 mr-2 text-purple-600"></i>
                            Depot Status
                        </h3>
                    </div>

                    @php
                        $active = $crm_data['active_depot_status_count'];
                        $inactive = $crm_data['Inactive_status_count'];
                        $pending = $crm_data['pending_status_count'] ?? 0; // Example third parameter, adjust name accordingly

                        $total = $active + $inactive;

                        $activePercent = $total > 0 ? ($active / $total) * 100 : 0;
                        $inactivePercent = $total > 0 ? ($inactive / $total) * 100 : 0;
                    @endphp


                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Active</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $active }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Inactive</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $inactive }}
                                </span>
                            </div>
                            {{--  <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Pending</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $pending }}
                                </span>
                            </div>  --}}
                        </div>

                        <div class="mt-4">
                            <div class="mb-1 text-xs flex justify-between">
                                <span>Health</span>
                                <span>{{ number_format($activePercent, 1) }}% Active</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 flex overflow-hidden">
                                <div class="bg-green-600 h-2" style="width: {{ $activePercent }}%"></div>
                                <div class="bg-gray-600 h-2" style="width: {{ $inactivePercent }}%"></div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

                <br>
                <br>

                <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Expiry Status</h3>
                <div class="text-sm font-medium text-indigo-600">
                    Overall Compliance: <span class="font-bold">91.5%</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">

                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Type</th>
                            {{--  <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Active</th>  --}}
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Expired</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Expiring Soon</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">Tachograph Cards</td>
                            {{--  <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $crm_data['active_driver_count'] }}</span>
                            </td>  --}}
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $crm_data['expired_cpc_count'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $crm_data['expiring_soon_tacho_count'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $expired = $crm_data['expired_cpc_count'];
                                    $expiringSoon = $crm_data['expiring_soon_tacho_count'];
                                    $total = $expired + $expiringSoon;
                                    $statusPercentage = $total > 0 ? round(($expiringSoon / $total) * 100, 2) : 0;
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: {{ $statusPercentage }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium">{{ $statusPercentage }}%</span>
                                </div>
                            </td>
                        </tr>


                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">CPC Certificates</td>
                            {{--  <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $crm_data['valid_cpc_count'] }}</span>
                            </td>  --}}
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $crm_data['expired_cpc_count'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $crm_data['expiring_soon_cpc_count'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $expired = $crm_data['expired_cpc_count'];
                                    $expiringSoon = $crm_data['expiring_soon_cpc_count'];
                                    $total = $expired + $expiringSoon;
                                    $statusPercentage = $total > 0 ? round(($expiringSoon / $total) * 100, 2) : 0;
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: {{ $statusPercentage }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium">{{ $statusPercentage }}%</span>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
         <div class="overflow-x-auto">
         <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Type</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Active</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Inactive</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $total = $crm_data['active_depot_status_count'] + $crm_data['Inactive_status_count'];
                            $active = $crm_data['active_depot_status_count'];
                            $percentage = $total > 0 ? round(($active / $total) * 100) : 0;
                        @endphp

                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">Depot Status</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">{{ $active }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $crm_data['Inactive_status_count'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium">{{ $percentage }}%</span>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
                <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold">Driver Status</h3>
                    <div class="text-sm">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> Active
                        <span class="inline-block w-3 h-3 bg-gray-300 rounded-full mx-1 ml-3"></span> Inactive
                        {{--  <span class="inline-block w-3 h-3 bg-gray-500 rounded-full mx-1 ml-3"></span> Archived  --}}
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg text-center cursor-pointer"
     onclick="window.location.href='{{ route('driver.index', ['driver_status' => 'Active','company_id' => request('company_id')]) }}'">
                        <p class="text-sm text-gray-500">Active</p>
                        <p class="text-2xl font-bold">{{ $crm_data['active_driver_count'] }}</p>
                        {{--  <p class="text-xs text-gray-500 mt-1">79.2% of fleet</p>  --}}
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg text-center cursor-pointer"
     onclick="window.location.href='{{ route('driver.index', ['driver_status' => 'InActive','company_id' => request('company_id')]) }}'">
                        <p class="text-sm text-gray-500">Inactive</p>
                        <p class="text-2xl font-bold">{{ $crm_data['inactive_driver_count'] }}</p>
                        {{--  <p class="text-xs text-gray-500 mt-1">16.7% of fleet</p>  --}}
                    </div>
                    {{--  <div class="bg-gray-200 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-500">Archived</p>
                        <p class="text-2xl font-bold">{{ $crm_data['archived_Archive_count'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">4.1% of fleet</p>
                    </div>  --}}
                </div>
            </div>

        </div>





         </div>



    <div class="row" id="dashboard-cards-container" style="margin-top:2%;">



        <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <!--<small class="text-muted">{{ __('Driver Status') }}</small>-->
                                    <h6 class="m-0">{{ __('Driver Status') }}</h6>
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

        <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <!--<small class="text-muted">{{ __('CPC Card Status') }}</small>-->
                                    <h6 class="m-0">{{ __('CPC Card Status') }}</h6>
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

        <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <!--<small class="text-muted">{{ __('Tacho Card Status') }}</small>-->
                                    <h6 class="m-0">{{ __('Tacho Card Status') }}</h6>
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


        @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
        <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
            <a href="{{ route('contractType.index') }}" class="card-link">
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
            </a>
        </div>
    @endif


    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['cpc_status' => 'EXPIRING SOON','company_id' => request('company_id')]) }}" class="text-decoration-none">
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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['cpc_status' => 'EXPIRED','company_id' => request('company_id')]) }}" class="text-decoration-none">
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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['tacho_card_status' => 'EXPIRING SOON','company_id' => request('company_id')]) }}" class="text-decoration-none">

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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['tacho_card_status' => 'EXPIRED','company_id' => request('company_id')]) }}" class="text-decoration-none">

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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['driver_status' => 'Active','company_id' => request('company_id')]) }}" class="text-decoration-none">
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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['driver_status' => 'InActive','company_id' => request('company_id')]) }}" class="text-decoration-none">
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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('driver.index', ['driver_status' => 'Archive','company_id' => request('company_id')]) }}" class="text-decoration-none">

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
    </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id')]) }}" class="card-link">
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
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
            <a href="{{ route('depot.index', ['company_id' => request('company_id')]) }}" class="card-link">

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
                                <p>Total Authorisation Vehicles: {{ $crm_data['total_no_of_vehicles'] }}</p>
                            @endif
                            <h4 class="m-0">{{ $crm_data['total_operating_centers'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>


        <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">

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
        <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
            @if(auth()->user()->hasRole('company') || auth()->user()->hasRole('PTC manager'))
                <a href="{{ route('driver.apilogs', ['company_id' => request('company_id')]) }}" class="card-link">
            @else
                <span class="card-link" style="pointer-events: none; cursor: not-allowed;">
            @endif
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
                            <h4 class="m-0">
                                
                                  @if(auth()->user()->hasRole('company') || auth()->user()->hasRole('PTC manager'))
    {{ $selectedCompanyApiCallCount }}
@else
    {{ $crm_data['driver_api_log_count'] ?? 'N/A' }}
    
@endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            @if(auth()->user()->hasRole('company') || auth()->user()->hasRole('PTC manager'))
                </a>
            @else
                </span>
            @endif
        </div>


    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'tacho_calibration','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Tacho Calibration Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_tacho_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'tacho_calibration','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Tacho Calibration Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_tacho_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>


    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'dvs_pss_permit_expiry','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('DVS/PSS Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_dvs_pss_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'dvs_pss_permit_expiry','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('DVS/PSS Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_dvs_pss_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'insurance','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Insurance Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_insurance_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'insurance','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Insurance Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_insurance_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'PMI_due','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('PMI Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_PMI_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'PMI_due','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('PMI Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_PMI_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'brake_test_due','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Brake Test Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_brake_test_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'brake_test_due','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Brake Test Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_brake_test_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'taxDueDate','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Road Tax Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_taxDueDate_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'taxDueDate','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('Road Tax Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_taxDueDate_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'annual_test_expiry_date','filter_value' => 'expiry_soon']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('MOT Status Expiring Soon') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_annual_test_status_expiring_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-12 dashboard-card" draggable="true" ondragstart="dragStart(event)" ondragover="allowDrop(event)" ondrop="drop(event)">
        <a href="{{ route('contract.index', ['company_id' => request('company_id'),'filter_column' => 'annual_test_expiry_date','filter_value' => 'expiry']) }}" class="text-decoration-none">

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
                                <h6 class="m-0">{{ __('MOT Status Expired') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{ $crm_data['total_annual_test_status_expired'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>

    </div>

</div>


@endsection
