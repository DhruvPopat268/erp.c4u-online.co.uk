@extends('layouts.admin')

@section('page-title')
    {{ __('Assign Policy Step-2') }}
@endsection

@push('script-page')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{__('Assign Policy')}}</a></li>
    <li class="breadcrumb-item">{{ __('Assign Policy Step-2') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <!-- Add any action buttons if needed -->
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Notification Area -->
                    <div id="notification" class="notification" style="display: none; position: fixed; top: 0; left: 0; margin-top: 30px; margin-left: 86%; padding: 10px; border-radius: 5px; z-index: 9999;">
                        <span id="notification-message"></span>
                    </div>

                    <!-- Selected Policies Box -->
                    <div class="selected-policies-box mt-4">
                        <h4>{{ __('Selected Policies') }}</h4>

                        <div class="row">

                            <!-- Left Side: Policy Lists -->
                            <div class="col-md-6">
                                <div class="row">
                                    <!-- Check if there are Bronze policies to display -->
                                    @if(!empty($policies['bronze']) && count($policies['bronze']) > 0)
                                        <div class="col-md-12 mb-3">
                                            <h6>{{ __('Browse Policies') }}</h6>
                                            <ul>
                                                @foreach ($policies['bronze'] as $policy)
                                                    <li>{{ $policy->bronze_policy_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Check if there are Silver policies to display -->
                                    @if(!empty($policies['silver']) && count($policies['silver']) > 0)
                                        <div class="col-md-12 mb-3">
                                            <h6>{{ __('Silver Policies') }}</h6>
                                            <ul>
                                                @foreach ($policies['silver'] as $policy)
                                                    <li>{{ $policy->bronze_policy_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Check if there are Gold policies to display -->
                                    @if(!empty($policies['gold']) && count($policies['gold']) > 0)
                                        <div class="col-md-12 mb-3">
                                            <h6>{{ __('Gold Policies') }}</h6>
                                            <ul>
                                                @foreach ($policies['gold'] as $policy)
                                                    <li>{{ $policy->bronze_policy_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right Side: Company and Driver List -->
                            <div class="col-md-6">
                                <h6 id="selected-company-name">{{ __('No Company Selected') }}</h6>
                                <ul id="selected-drivers-list">
                                    <!-- Selected drivers will be dynamically inserted here -->
                                </ul>
                            </div>
                        </div>
                    </div>



                    <!-- Company Selector -->
                    <div id="company-selector" class="mt-4">
                        <label for="company-name" class="form-label">{{ __('Select Company') }}</label>
    <select id="company-select" name="company_id" class="form-select">
        <option value="">{{ __('Select a Company') }}</option>
                            @foreach($companies->sortBy('name') as $company)
                                <option value="{{ $company->id }}">{{ strtoupper($company->name) }}</option>
                            @endforeach
                        </select>
                    </div>

<!-- Group Selector (Checkboxes) - Initially Hidden -->
<div id="group-selector" class="mt-4" style="display:none;">
    <label for="group-name" class="form-label">{{ __('Select Group(s)') }}</label>
    <div id="group-list">
        <!-- Groups will be populated dynamically -->
    </div>
</div>

<!-- Filter Button - Initially Hidden -->
<div id="filter-button-container" class="mt-4" style="display:none;">
    <button id="filter-button" class="btn btn-primary">{{ __('Filter Drivers') }}</button>
</div>

<!-- Driver Selector - Initially Hidden -->
<div id="driver-selector" class="mt-4" style="display:none;">
    <label for="drivers" class="form-label">{{ __('Select Driver(s)') }}</label>

    <!-- Select All Checkbox -->
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="select-all-drivers">
        <label class="form-check-label" for="select-all-drivers">
            {{ __('Select All Drivers') }}
        </label>
    </div>

    <div id="drivers-list">
        <!-- Drivers will be populated dynamically -->
    </div>
</div>


                    <!-- Buttons Row -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            {{ __('Previous Page') }}
                        </a>
                        <button id="assign-policy-button" class="btn btn-primary">
                            {{ __('Assign Policy') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader HTML -->
    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
        <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px; ">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <script>
       // Handle company selection to fetch groups
       document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('company-select').addEventListener('change', function () {
            const companyId = this.value;

            // Set the selected company name
            const selectedCompanyNameElement = document.getElementById('selected-company-name');
            const selectedCompanyOption = this.options[this.selectedIndex];

            if (companyId) {
                selectedCompanyNameElement.textContent = selectedCompanyOption.text; // Set the company name
            } else {
                selectedCompanyNameElement.textContent = '{{ __('No Company Selected') }}'; // Reset if no company is selected
            }

            if (!companyId) {
                document.getElementById('group-selector').style.display = 'none';
                document.getElementById('filter-button-container').style.display = 'none';
                document.getElementById('driver-selector').style.display = 'none';
                document.getElementById('group-list').innerHTML = '';
                return;
            }

            // Fetch groups for the selected company
            fetch(`{{ url('/get-groups-by-company') }}?company_id=${companyId}`)
                .then(response => response.json())
                .then(data => {
                    let groupListHtml = '';
                    let rowCount = 0;

                    data.forEach((group, index) => {
                        if (index % 4 === 0) { // Start a new row every 4 groups
                            if (index > 0) groupListHtml += '</div>'; // Close the previous row
                            groupListHtml += '<div class="row">'; // Open a new row
                        }

                        groupListHtml += `
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="${group.id}" id="group-${group.id}" name="groups[]">
                                    <label class="form-check-label" for="group-${group.id}">
                                        ${group.name}
                                    </label>
                                </div>
                            </div>
                        `;
                        rowCount++;
                    });

                    if (rowCount > 0) groupListHtml += '</div>'; // Close the last row
                    document.getElementById('group-list').innerHTML = groupListHtml;
                    document.getElementById('group-selector').style.display = 'block';
                    document.getElementById('filter-button-container').style.display = 'block';
                })
                .catch(error => console.error('Error fetching groups:', error));
        });


    // Handle filter button click to fetch drivers
    document.getElementById('filter-button').addEventListener('click', function () {
        const selectedGroups = Array.from(document.querySelectorAll('input[name="groups[]"]:checked')).map(input => input.value);

        if (selectedGroups.length === 0) {
            document.getElementById('driver-selector').style.display = 'none';
            document.getElementById('drivers-list').innerHTML = '';
            alert('Please select at least one group.');
            return;
        }

        // Fetch drivers for the selected groups
        fetch(`{{ url('/get-drivers-by-groups') }}?group_ids=${selectedGroups.join(',')}`)
            .then(response => response.json())
            .then(data => {
                let driverListHtml = '';
                    let rowCount = 0;

                    data.forEach((driver, index) => {
                        if (index % 3 === 0) {
                            if (index > 0) driverListHtml += '</div>';
                            driverListHtml += '<div class="row">';
                        }
                        driverListHtml += `
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="${driver.id}" id="driver-${driver.id}" name="drivers[]">
            <label class="form-check-label" for="driver-${driver.id}">
                ${driver.name} (${driver.group ? driver.group.name : ''})
            </label>
        </div>
    </div>
`;
                        rowCount++;
                    });

                    if (rowCount > 0) driverListHtml += '</div>';
                document.getElementById('drivers-list').innerHTML = driverListHtml;
                document.getElementById('driver-selector').style.display = 'block';
                })
                .catch(error => console.error('Error fetching drivers:', error));
        });


       document.getElementById('select-all-drivers').addEventListener('change', function () {
            const selectAllChecked = this.checked;
            const driverCheckboxes = document.querySelectorAll('input[name="drivers[]"]');

            driverCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllChecked;  // Check or uncheck all checkboxes
            });

            // Trigger change event on driver checkboxes to update the selected drivers list
            const event = new Event('change');
            document.getElementById('driver-selector').dispatchEvent(event);
        });

        // Handle driver selection logic
        document.getElementById('driver-selector').addEventListener('change', function() {
            const selectedDriverInputs = document.querySelectorAll('input[name="drivers[]"]:checked');
            const selectedDriversList = document.getElementById('selected-drivers-list');
            let driversHtml = '';

            selectedDriverInputs.forEach(input => {
                const driverName = input.nextElementSibling.textContent;
                driversHtml += `<li>${driverName}</li>`;
            });

            selectedDriversList.innerHTML = driversHtml;
        });

         // Show loader when starting the process
 function showLoader() {
    document.getElementById('loader').style.display = 'block';
}

    function hideLoader() {
        document.getElementById('loader').style.display = 'none';
    }




        document.getElementById('assign-policy-button').addEventListener('click', function() {
            const companyId = document.getElementById('company-select').value;
            const selectedDrivers = Array.from(document.querySelectorAll('input[name="drivers[]"]:checked')).map(input => input.value);
            const selectedGroups = Array.from(document.querySelectorAll('input[name="groups[]"]:checked')).map(input => input.value);
            const selectedPolicies = @json($selectedPolicies);

            if (!companyId) {
                showNotification('Please select a company.', 'danger');
                return;
            }

            if (selectedGroups.length === 0) { // Change this line
                showNotification('Please select a group.', 'danger');
                return;
            }

            if (selectedDrivers.length === 0) {
                showNotification('Please select at least one driver.', 'danger');
                return;
            }

                        showLoader();


            // Prepare the data to send
            const data = {
                company_id: companyId,
                groups: selectedGroups,
                drivers: selectedDrivers,
                policies: selectedPolicies
            };

            // Send data to the backend
            fetch('{{ route('assign-policy.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Policies assigned successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route('fors.assignpolicy.index') }}'; // Redirect to the desired route
                    }, 3000); // Redirect after the notification disappears
                } else {
                    showNotification('Failed to assign policies.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                                hideLoader(); // Hide loader if there's an error

                showNotification('An error occurred while assigning the policy.', 'danger');
            });
        });

        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            const messageElement = document.getElementById('notification-message');
            messageElement.textContent = message;
            notification.className = `notification bg-${type}`;
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }
    });
    </script>
@endsection

<style>
    .notification {
        padding: 10px;
        border-radius: 5px;
        color: white;
        font-size: 14px;
    }

    .alert-success {
        background-color: #48494B; /* Set the background color */
    }

    .alert-danger {
        background-color: #ff3a6e !important; /* Set the background color */
    }



    .selected-policies-box {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .select-driver-box {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background-color: #f9f9f9;
        width: 100%;

    }

    .form-check {
        margin-bottom: 10px;
    }

    .row {
        margin-bottom: 15px;
    }
</style>
