@extends('layouts.admin')
@section('page-title')
{{__('Manage Driver')}}
@endsection
@push('script-page')
<script>
    function showUpdateModal(url) {
        document.getElementById('updateForm').action = url;
        new bootstrap.Modal(document.getElementById('updateModal')).show();
    }

    function showLoader() {
        document.getElementById('loader').style.display = 'block';
    }

    function showChangePasswordModal(url) {
        document.getElementById('changePasswordForm').action = url;
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    }


    document.addEventListener('DOMContentLoaded', function() {
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const submitButton = document.getElementById('submitButton');

        function validatePasswords() {
            if (newPasswordInput.value === confirmPasswordInput.value && newPasswordInput.value !== '') {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }


        // Add event listeners for password fields
        newPasswordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
    });


    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('company_id').addEventListener('change', function() {
            // Submit the form when the dropdown value changes
            document.getElementById('filterForm').submit();
        });

        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 500); // Duration for fade-out effect
            }, 5000); // Duration to show error message
        }
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const newPasswordField = document.getElementById('new_password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordField = document.getElementById('confirm_password');

        toggleNewPassword.addEventListener('click', function() {
            if (newPasswordField.type === 'password') {
                newPasswordField.type = 'text';
                toggleNewPassword.querySelector('i').classList.remove('fa-eye');
                toggleNewPassword.querySelector('i').classList.add('fa-eye-slash');
            } else {
                newPasswordField.type = 'password';
                toggleNewPassword.querySelector('i').classList.remove('fa-eye-slash');
                toggleNewPassword.querySelector('i').classList.add('fa-eye');
            }
        });

        toggleConfirmPassword.addEventListener('click', function() {
            if (confirmPasswordField.type === 'password') {
                confirmPasswordField.type = 'text';
                toggleConfirmPassword.querySelector('i').classList.remove('fa-eye');
                toggleConfirmPassword.querySelector('i').classList.add('fa-eye-slash');
            } else {
                confirmPasswordField.type = 'password';
                toggleConfirmPassword.querySelector('i').classList.remove('fa-eye-slash');
                toggleConfirmPassword.querySelector('i').classList.add('fa-eye');
            }
        });
    });

    $(document).ready(function() {
       var selectedCompanyId = $('#company_id').length ? $('#company_id').val() : null;
        var selectedDepotId = '{{ request("depot_id") }}'; // Get selected depot from request
        var selectedGroupId = '{{ request("group_id") }}';

        function loadDepots(companyId, selectedDepotId = null) {
            if (companyId) {
                $('#depot_id').html('<option value="">{{__("Loading...")}}</option>');
                $('#depot_id').prop('disabled', true);

                $.ajax({
                    url: '{{ route("get.depots.by.company") }}'
                    , type: 'GET'
                    , data: {
                        company_id: companyId
                    }
                    , success: function(data) {
                        $('#depot_id').html('<option value="">{{__("Select Depot")}}</option>');

                        $.each(data, function(key, depot) {
                            let selected = selectedDepotId == depot.id ? 'selected' : '';
                            $('#depot_id').append('<option value="' + depot.id + '" ' + selected + '>' + depot.name.toUpperCase() + '</option>');
                        });

                        $('#depot_id').prop('disabled', false);
                    }
                });
            } else {
                $('#depot_id').html('<option value="">{{__("Select a Company First")}}</option>');
                $('#depot_id').prop('disabled', true);
            }
        }

        function loadGroups(companyId, selectedGroupId = null) {
            if (companyId) {
                $('#group_id').html('<option value="">{{__("Loading...")}}</option>');
                $('#group_id').prop('disabled', true);

                $.ajax({
                    url: '{{ route("get.driver.group.by.company") }}'
                    , type: 'GET'
                    , data: {
                        company_id: companyId
                    }
                    , success: function(data) {

                        $('#group_id').html('<option value="">{{__("Select Group")}}</option>');

                        $.each(data, function(key, group) {

                            let selected = selectedGroupId == group.id ? 'selected' : '';

                            $('#group_id').append(
                                '<option value="' + group.id + '" ' + selected + '>' + group.name.toUpperCase() + '</option>'
                            );

                        });

                        $('#group_id').prop('disabled', false);
                    }
                });
            } else {
                $('#group_id').html('<option value="">{{__("Select a Company First")}}</option>');
                $('#group_id').prop('disabled', true);
            }
        }

        // Load depots if a company is already selected (after form submission)
        if (selectedCompanyId) {
            loadDepots(selectedCompanyId, selectedDepotId);
        }

        if (selectedCompanyId) {
            loadGroups(selectedCompanyId, selectedGroupId);
        }

        // Handle company selection change
        $('#company_id').on('change', function() {
            var companyId = $(this).val();
            loadDepots(companyId);
        });

        $('#company_id').on('change', function() {
            var companyId = $(this).val();
            loadGroups(companyId);
        });

        // Ensure depot remains enabled after selection
        $('#depot_id').on('change', function() {
            if ($(this).val()) {
                $(this).prop('disabled', false);
            }
        });

        $('#group_id').on('change', function() {
            if ($(this).val()) {
                $(this).prop('disabled', false);
            }
        });
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const bulkActionBtn = document.getElementById('bulk-action-btn');
        const bulkEditBtn = document.getElementById('bulk-edit-btn');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

        function updateBulkActionState() {
            const anyChecked = document.querySelectorAll('.select-item:checked').length > 0;
            bulkActionBtn.disabled = !anyChecked;
        }

        // Handle "Select All"
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.select-item').forEach(cb => cb.checked = this.checked);
                updateBulkActionState();
            });
        }

        // Event delegation for dynamic checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('select-item')) {
                if (!e.target.checked && selectAll) {
                    selectAll.checked = false;
                }
                updateBulkActionState();
            }
        });

        // Bulk edit popup
        bulkEditBtn.addEventListener('click', function() {
            let selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
                .map(cb => cb.value);

            if (selectedIds.length > 0) {
                let url = "{{ route('selected.driver.edit', ':ids') }}".replace(':ids', selectedIds.join(','));

                // Simulate the exact same type of link click your single edit button uses
                let tempLink = document.createElement('a');
                tempLink.setAttribute('href', '#');
                tempLink.setAttribute('data-url', url);
                tempLink.setAttribute('data-ajax-popup', 'true');
                tempLink.setAttribute('data-size', 'md');
                tempLink.setAttribute('data-title', 'Edit Selected Drivers');

                // Append to DOM so event listeners catch it
                document.body.appendChild(tempLink);
                tempLink.click();
                tempLink.remove();
            } else {
                alert('Please select at least one driver.');
            }
        });

        // Bulk delete action

        bulkDeleteBtn.addEventListener('click', function() {
            let selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
                .map(cb => cb.value);

            if (selectedIds.length > 0) {
                if (confirm('The driver’s data will be permanently deleted and cannot be recovered. Are you sure you want to continue?')) {
                    fetch("{{ route('selected.driver.delete') }}", {
                            method: 'POST'
                            , headers: {
                                'Content-Type': 'application/json'
                                , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                            , body: JSON.stringify({
                                ids: selectedIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert(data.message || 'Error deleting selected drivers.');
                            }
                        })
                        .catch(err => alert('Error deleting selected drivers.'));
                }
            } else {
                alert('Please select at least one driver.');
            }
        });


    });

</script>

<script>
    function exportSelected() {
        let selectedIds = [];
        document.querySelectorAll('.select-item:checked').forEach(cb => {
            selectedIds.push(cb.value);
        });

        // Get the button safely
        const btn = document.getElementById('exportBtn');
        if (btn) {
            btn.disabled = true;
        }
        let url = "{{ route('driverDataexport.export') }}" +
            "?company_id={{ request()->input('company_id') }}" +
            "&driver_status={{ request()->input('driver_status') }}" +
            "&cpc_status={{ request()->input('cpc_status') }}" +
            "&tacho_card_status={{ request()->input('tacho_card_status') }}" +
            "&depot_id={{ request()->input('depot_id') }}" +
            "&group_id={{ request()->input('group_id') }}";

        // If some are selected → pass IDs
        if (selectedIds.length > 0) {
            url += "&ids=" + selectedIds.join(',');
        }
        // If none selected → export all (don’t add ids)

        window.location.href = url;

        // Re-enable button after 2s (if exists)
        setTimeout(() => {
            if (btn) {
                btn.disabled = false;
            }
        }, 2000);
    }

</script>

@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Driver')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
<div class="float-end">
    @can('import driver')
    <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('driver.file.import') }}" data-ajax-popup="true" data-title="{{__('Import Driver CSV file')}}" class="btn btn-sm btn-primary">
        Import </a>
    @endcan
    @can('create driver')
    {{-- <a href="{{ route('driverDataexport.export', ['company_id' => request('company_id'),'driver_status' => request('driver_status'),'cpc_status' => request('cpc_status'),'tacho_card_status' => request('tacho_card_status'),]) }}"
    data-bs-toggle="tooltip"title="{{ __('Export') }}"class="btn btn-sm btn-primary">
    <i class="ti ti-download"></i>
    </a> --}}

    <a href="javascript:void(0)" id="exportBtn" onclick="exportSelected()" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Export')}}">
        <i class="ti ti-download"></i>
    </a>
    @endcan
    @can('create driver')
    <a href="#" data-size="md" data-url="{{ route('driver.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Driver')}}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
    @endcan
    {{-- <a href="#" data-size="md" data-url="{{ route('driver.bulkimport') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Bulk Import')}}" class="btn btn-sm btn-success">
    <i class="ti ti-upload"></i> {{__('Bulk Import')}}
    </a> --}}
    @php
    $isCompanyOrPTC = Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager');
    @endphp

    @if(($isCompanyOrPTC && request('company_id')) || !$isCompanyOrPTC)
    <div class="btn-group me-1">
        <button id="bulk-action-btn" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" disabled>
            <i class="ti ti-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a href="javascript:void(0)" id="bulk-edit-btn" class="dropdown-item">Edit Selected</a>
            </li>
            @if(\Auth::user()->type == 'company')
            <li>
                <a href="javascript:void(0)" id="bulk-delete-btn" class="dropdown-item text-danger">Delete Selected</a>
            </li>
            @endif
        </ul>
    </div>
    @endif


</div>
@endsection



@section('content')
@if (session('errorArray'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="d-flex justify-content-between align-items-center">
        <span>Skipped Records:</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </h5>
    <ul>
        @foreach (session('errorArray') as $error)
        <li>
            <strong>Error:</strong> {{ $error['error'] }}<br>
            <strong>Data:</strong> {{ implode(', ', $error['data']) }}
        </li>
        @endforeach
    </ul>
</div>
@php
// Remove the session data so it doesn't show up after reload
session()->forget('errorArray');
@endphp
@endif

@if ($errors->any())
<div id="error-message" class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    {{-- <div class="col-3">
            @include('layouts.depot_setup')
        </div>  --}}
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-12">
            <!-- Filter Form -->

            <form method="GET" action="{{ route('driver.index') }}">
                <div class="row">
                    @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
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




                    @endif
                    <!-- Depot Selection -->
                    <div class="col-md-4">
                        <label for="depot_id">{{__('Filter by Depot')}}</label>

                        <select name="depot_id" id="depot_id" class="form-control">

                            @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                            <option value="">{{__('Select a Company First')}}</option>

                            @else

                            <option value="">{{__('All Depots')}}</option>

                            @foreach($depots as $depot)

                            <option value="{{ $depot->id }}" {{ request('depot_id') == $depot->id ? 'selected' : '' }}>
                                {{ strtoupper($depot->name) }}
                            </option>

                            @endforeach

                            @endif

                        </select>

                    </div>


                    <div class="col-md-4">
                        <label for="group_id">{{__('Filter by Group')}}</label>

                        <select name="group_id" id="group_id" class="form-control">

                            @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))

                            <option value="">{{__('Select a Company First')}}</option>

                            @else

                            <option value="">{{__('All Groups')}}</option>

                            @foreach($groups as $group)

                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                {{ strtoupper($group->name) }}
                            </option>

                            @endforeach

                    @endif

                        </select>

                    </div>
                   <div class="col-md-4 mt-3">
                        <label for="driver_status">{{__('Filter by Driver Status')}}</label>
                        <select name="driver_status" id="driver_status" class="form-control">
                            <option value="">{{__('All Statuses')}}</option>
                            <option value="Active" {{ request('driver_status', 'Active') == 'Active' ? 'selected' : '' }}>{{__('Active')}}</option>
                            <option value="InActive" {{ request('driver_status') == 'InActive' ? 'selected' : '' }}>{{__('InActive')}}</option>
                            <option value="Archive" {{ request('driver_status') == 'Archive' ? 'selected' : '' }}>{{__('Archive')}}</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                        <a href="{{ route('driver.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                    </div>
                </div>
            </form>


            {{-- <div class="alphabet-filter-box">
            <div class="letters-box">
                <p>Surname Filter &nbsp;&nbsp;&nbsp;&nbsp;
                    @foreach(range('A', 'Z') as $char)
                        @if(in_array($char, $availableLetters))
                            <!-- Available letter, show in blue -->
                            <a href="{{ route('driver.index', ['surname_filter' => $char]) }}" class="letter-link available">
            {{ $char }}
            </a>
            @else
            <!-- Unavailable letter, show in default color -->
            <span class="letter-link unavailable">
                {{ $char }}
            </span>
            @endif
            @endforeach
            <a href="{{ route('driver.index') }}" class="letter-link available" style="color: red;">All</a>
            </p>
        </div>
    </div>

    <style>
        .alphabet-filter-box {
            margin: 20px 0;
            text-align: left;
        }

        .letters-box {
            display: inline-block;
            padding: 3px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .letters-box p {
            display: inline;
            /* Keep text and links on the same line */
            font-weight: bold;
            margin: 0;
        }

        .letter-link {
            margin: 0 5px;
            padding: 3px 8px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .letter-link.available {
            color: black;
            /* Blue for available letters */

        }

        .letter-link.unavailable {
            color: #8d8b8b;
            /* Default color for unavailable letters */

            cursor: default;
            /* Non-clickable unavailable letters */
        }

        .letter-link.available:hover {
            background-color: #007bff;
            color: #fff;
        }

    </style> --}}
</div>
</div>
<div class="col-9" style="width: 100%">
    <div class="card">
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>
                                <a href="#">
                                    <input type="checkbox" id="select-all">
                                </a>
                            </th>
                            <th class="text-end ">{{__('Action')}}</th>
                            <th>{{__('Driver Number')}}</th>
                            <th style="text-align: center;">{{__(' Driver Name')}}</th>
                            <th>{{ __('Username') }}</th>
                            <th>{{ __('Group') }}</th>
                            <th>{{ __('Depot') }}</th>
                            <th>{{ __('Depot Change Allowed') }}</th>
                            <th>{{__('Driver Licence No')}}</th>
                            <th>{{__('Driver Licence Status')}}</th>
                            <th>{{__('Company Name')}}</th>
                            <th>{{__('Automated Licence Check')}}</th>
                            <th>{{__('Driver Status')}}</th>
                            <th>{{__('Consent Form')}}</th>
                            <th>{{__('Post Code')}}</th>
                            <th>{{__('Contact No')}}</th>
                            <th>{{__('Contact Email')}}</th>
                            <th>{{__('Driver DOB')}}</th>
                            <th>{{__('Driver Age')}}</th>
                            <th>{{__('Driver Address')}}</th>

                            <th>{{__('Driver Licence expiry')}}</th>
                            <!--<th>{{__('CPC Status')}}</th>-->
                            <th>{{__('CPC valid to')}}</th>
                            <th>{{__('Tacho Card No')}}</th>
                            <th>{{__('Tacho card valid from')}}</th>
                            <!--<th>{{__('Tacho Card Status')}}</th>-->
                            <th>{{__('Tacho card valid to')}}</th>
                            <th>{{__('Last LC Check')}}</th>
                            <th>{{__('Next LC Check')}}</th>
                            <th>{{__('Endorsement Point')}}</th>
                            <th>{{__('Driver Last login At')}}</th>
                            <th>{{ __('Created') }} </th>


                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contracts as $contract)

                        <tr class="font-style">
                            <td>
                                <input type="checkbox" class="select-item" value="{{ $contract->id }}">
                            </td>
                            <td class="action text-end">

                                @can('edit driver')
                                <div class="action-btn bg-info ms-2">
                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('driver.edit', $contract->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Driver') }}">
                                        <i class="ti ti-pencil text-white"></i>
                                    </a>
                                </div>
                                @endcan

                                @if(strtolower($contract->driver_licence_status) == 'valid')

                                <div class="action-btn bg-warning ms-2">
                                    <a href="{{ route('driver.show',$contract->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                                </div>
                                @endif
                                @php
                                // Assuming you have a role-checking function or a role attribute on the user
                                $userHasCompanyRole = auth()->user()->hasRole('company');
                                @endphp

                                @if ($userHasCompanyRole)
                                <div class="action-btn bg-danger ms-2">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['driver.destroy', $contract->id]]) !!}
                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="ti ti-trash text-white"></i>
                                    </a>
                                    {!! Form::close() !!}
                                </div>
                                @endif

                                @if($contract->types && $contract->types->lc_check_status === 'Enable' && $contract->consent_form_status === 'Yes')
                                <div class="action-btn ms-2" style="background-color: #0aa500;">
                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" onclick="showUpdateModal('{{ route('driver.updateSpecific', $contract->id) }}')" data-bs-toggle="tooltip" title="{{ __('action is CHARGEABLE') }}">
                                        <i class="ti ti-refresh text-white"></i>
                                    </a>
                                </div>
                                @endif
                                @if($contract->driverUser)
                                <div class="action-btn bg-secondary ms-2">
                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" onclick="showChangePasswordModal('{{ route('driveruser.changePassword', $contract->driverUser->id) }}')" data-bs-toggle="tooltip" title="{{__('Change Password')}}">
                                        <i class="ti ti-key text-white"></i>
                                    </a>
                                </div>
                                @endif
                                @if(strtolower($contract->driver_licence_status) == 'valid')
                                <div class="action-btn bg-primary ms-2">
                                    <a href="{{ route('driver.history', $contract->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('View History')}}">
                                        <i class="ti ti-history text-white"></i>
                                    </a>
                                </div>
                                @endif
                            </td>
                            <td>{{ $contract->ni_number }}</td>
                            <td>{{ strtoupper($contract->name) }}</td>
                            <td>{{ !empty($contract->driverUser) ? $contract->driverUser->username : '-' }}</td> <!-- New column value -->
                            <td>{{ $contract->group ? $contract->group->name : '-' }}</td>
                            <td>{{ $contract->depot ? ucfirst(strtolower($contract->depot->name)) : '-' }}</td>
                            <td>{{ $contract->depot_access_status }}</td>

                            <td>{{ $contract->driver_licence_no }}</td>
                            <td>
                                @if($contract->driver_licence_status === 'VALID' || $contract->driver_licence_status === 'Valid')
                                <span style="color: green; font-weight:bold;">{{ $contract->driver_licence_status }}</span>
                                @elseif($contract->driver_licence_status === 'EXPIRED' || $contract->driver_licence_status === 'Expired')
                                <span style="color: red; font-weight:bold;">{{ $contract->driver_licence_status }}</span>
                                @elseif($contract->driver_licence_status === 'EXPIRING SOON' || $contract->driver_licence_status === 'Expiring Soon')
                                <span style="color: orange; font-weight:bold;">{{ $contract->driver_licence_status }}</span>
                                @else
                                {{ $contract->driver_licence_status }}
                                @endif
                            </td>
                            <td>{{ !empty($contract->types) ? ucwords(strtolower($contract->types->name)) : '' }}</td>
                            <td>{{ $contract->automation }}</td>
                            <td>{{ $contract->driver_status }}</td>
                            <td>

                                @if($contract->consent_form_status === 'Yes')
                                <span style="color: green; font-weight:bold;">{{ $contract->consent_form_status }}</span>
                                @elseif($contract->consent_form_status === 'No')
                                <span style="color: red; font-weight:bold;">{{ $contract->consent_form_status }}</span>
                                @elseif($contract->consent_form_status === 'Expiry')
                                <span style="color: red; font-weight:bold;">{{ $contract->consent_form_status }}</span>
                                @else
                                {{ $contract->consent_form_status }}
                                @endif
                            </td>
                            <td>{{ $contract->post_code }}</td>
                            <td>{{ $contract->contact_no }}</td>
                            <td>{{ $contract->contact_email }}</td>
                            <td>{{ $contract->driver_dob }}</td>
                            <td>
                                @if (!empty($contract->driver_age))
                                {{ $contract->driver_age }} Years
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $contract->driver_address }}</td>



                            {{-- <td>
                                        @if(is_null($contract->driver_licence_expiry))
                                        @else
                                            {{ Carbon::parse($contract->driver_licence_expiry)->format('d/m/Y') }}
                            @endif
                            </td> --}}
                            <td>{{ $contract->driver_licence_expiry }}</td>

                            <!--<td>-->
                            <!--    @if($contract->cpc_status === 'VALID' || $contract->cpc_status === 'Valid')-->
                            <!--        <span style="color: green; font-weight:bold;">{{ $contract->cpc_status }}</span>-->
                            <!--    @elseif($contract->cpc_status === 'EXPIRED' || $contract->cpc_status === 'Expired')-->
                            <!--        <span style="color: red; font-weight:bold;">{{ $contract->cpc_status }}</span>-->
                            <!--    @elseif($contract->cpc_status === 'EXPIRING SOON' || $contract->cpc_status === 'Expiring Soon')-->
                            <!--        <span style="color: orange; font-weight:bold;">{{ $contract->cpc_status }}</span>-->
                            <!--    @else-->
                            <!--        {{ $contract->cpc_status }}-->
                            <!--    @endif-->
                            <!--</td>-->
                            <td>{{ $contract->cpc_validto }}</td>

                            <td>{{ $contract->tacho_card_no }}</td>

                            <td>
                                @if($contract->tacho_card_valid_from === '-')
                                -
                                @else
                                {{ $contract->tacho_card_valid_from }}
                                @endif
                            </td>
                            <!--<td>-->
                            <!--    @if($contract->tacho_card_status === 'VALID' || $contract->tacho_card_status === 'Valid')-->
                            <!--        <span style="color: green; font-weight:bold;">{{ $contract->tacho_card_status }}</span>-->
                            <!--    @elseif($contract->tacho_card_status === 'EXPIRED' || $contract->tacho_card_status === 'Expired')-->
                            <!--        <span style="color: red; font-weight:bold;">{{ $contract->tacho_card_status }}</span>-->
                            <!--    @elseif($contract->tacho_card_status === 'EXPIRING SOON' || $contract->tacho_card_status === 'Expiring Soon')-->
                            <!--        <span style="color: orange; font-weight:bold;">{{ $contract->tacho_card_status }}</span>-->
                            <!--    @else-->
                            <!--        {{ $contract->tacho_card_status }}-->
                            <!--    @endif-->
                            <!--</td>-->
                            <td>
                                @if($contract->tacho_card_valid_to === '-')
                                -
                                @else
                                {{ $contract->tacho_card_valid_to }}
                                @endif
                            </td>

                            <td>
                                @if(is_null($contract->latest_lc_check))

                                @else
                                {{ $contract->latest_lc_check }}
                                @endif
                            </td>
                            <td> {{ $contract->next_lc_check }}</td>

                            <td class="text-center">
                                @php
                                $endorsements = json_decode($contract->endorsements, true); // Decode the JSON data
                                $penaltyPoints = $endorsements[0]['penaltyPoints'] ?? null; // Access the penaltyPoints value
                                @endphp
                                {{ $penaltyPoints ?? 0 }}
                            </td>
                            <td>
                                @if(
                                !empty($contract->driverUser) &&
                                !empty($contract->driverUser->last_login_at) &&
                                $contract->driverUser->last_login_at != '0000-00-00 00:00:00'
                                )
                                {{ \Carbon\Carbon::parse($contract->driverUser->last_login_at)->format('d-m-Y H:i') }}
                                @else
                                -
                                @endif
                            </td>


                            <td>
                                {{ !empty($contract->creator) ? $contract->creator->username : ($contract->created_by == 1.1 ? 'Automation' : '') }}
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

<!-- Confirmation Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">{{__('Confirm Update')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('This Licence check is payable, Do you want to carry out ?') }}

                <!-- Loader HTML -->
                <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
                    <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px; ">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form id="updateForm" method="POST" action="">
                    @csrf
                    @method('POST')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary" onclick="showLoader()">{{__('Update')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">{{__('Change Password')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" method="POST" action="">
                    @csrf
                    @method('POST')
                    <div class="mb-3 position-relative">
                        <label for="new_password" class="form-label">{{__('New Password')}}</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <button type="button" class="position-absolute end-0 top-50 translate-middle-y" id="toggleNewPassword" style="border-color: transparent;margin-top: 3.5%;background-color: transparent;margin-right: 10px;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="confirm_password" class="form-label">{{__('Confirm Password')}}</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="position-absolute end-0 top-50 translate-middle-y" id="toggleConfirmPassword" style="border-color: transparent;margin-top: 3.5%;background-color: transparent;margin-right: 10px;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary" id="submitButton" disabled>{{__('Change Password')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
