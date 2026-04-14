@extends('layouts.admin')

@section('page-title')
{{__('Driver ADD Api Logs')}}
@endsection

@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to enable or disable the delete button based on company selection
        function toggleDeleteButton() {
            let companyId = document.getElementById('company-dropdown').value;
            let deleteButton = document.getElementById('delete-data-btn');

            if (companyId) {
                deleteButton.disabled = false; // Enable the button if company is selected
            } else {
                deleteButton.disabled = true; // Disable the button if no company is selected
            }
        }

        // Function to apply filters
        function applyFilter() {
            let companyId = document.getElementById('company-dropdown').value;
            let userId = document.getElementById('user-dropdown').value;
            let fromDate = document.getElementById('from-date').value;
    let toDate = document.getElementById('to-date').value;

    let url = "{{ route('driver.apilogs') }}?company_id=" + companyId + "&created=" + userId
              + "&from_date=" + fromDate + "&to_date=" + toDate;
            window.location.href = url;
        }

        // Event listeners
        document.getElementById('apply-filter-btn').addEventListener('click', applyFilter);

        document.getElementById('reset-filter-btn').addEventListener('click', function () {
            document.getElementById('company-dropdown').selectedIndex = 0;
            document.getElementById('user-dropdown').selectedIndex = 0;
             document.getElementById('from-date').value = '';
    document.getElementById('to-date').value = '';
            window.location.href = "{{ route('driver.apilogs') }}";
        });

        // Handle delete data button click
        document.getElementById('delete-data-btn').addEventListener('click', function () {
            let companyId = document.getElementById('company-dropdown').value;

            // Check if company is selected, show alert if not
            if (!companyId) {
                alert('Please select a company before deleting logs.');
                return;
            }

            if (confirm('Are you sure you want to delete the filtered API logs?')) {
            let userId = document.getElementById('user-dropdown').value;

            document.getElementById('delete-company-id').value = companyId;
            document.getElementById('delete-user-id').value = userId;

                document.getElementById('delete-data-form').submit();
            }
        });

        // Initially disable the delete button if no company is selected
        toggleDeleteButton();

        // Enable/disable delete button when company dropdown changes
        document.getElementById('company-dropdown').addEventListener('change', toggleDeleteButton);
    });
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Driver Api Logs')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
       <a href="{{ route('export.api.logs', [
        'company_id' => request('company_id'),
        'created' => request('created'),
        'from_date' => request('from_date'),
        'to_date' => request('to_date')
    ]) }}"
    class="btn btn-success">
            Export
        </a>
        <button id="delete-data-btn" class="btn btn-danger">
            Delete Logs
        </button>
        <form id="delete-data-form" action="{{ route('delete.api.logs') }}" method="POST" style="display: none;">
            @csrf
    <input type="hidden" name="company_id" id="delete-company-id" value="">
    <input type="hidden" name="created" id="delete-user-id" value="">
        </form>

    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="float-start" style="width: 60%;">
<div class="d-flex mb-3" style="gap: 1rem;">
    <select id="company-dropdown" class="form-select" style="width: 100%;">
        <option value="">Select Company</option>
        @foreach($companies->sortBy('name') as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                {{ strtoupper($company->name) }}
            </option>
        @endforeach
    </select>

   <select id="user-dropdown" class="form-select" style="width: 100%;">
       <option value="">Select User</option>
       <option value="Auto Generator" {{ request('created') == 'Auto Generator' ? 'selected' : '' }}>Auto Generator</option>
               <option value="1.1" {{ request('created') == '1.1' ? 'selected' : '' }}>Automation</option>
       @foreach($users->sortBy('username') as $user)
           <option value="{{ $user->id }}" {{ request('created') == $user->id ? 'selected' : '' }}>
               {{ $user->username }}
           </option>
       @endforeach
   </select>
    <input type="date" id="from-date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
    <input type="date" id="to-date" class="form-control" placeholder="End Date" value="{{ request('to_date') }}">
</div>
<div class="d-flex" style="gap: 1rem;">
    <button id="apply-filter-btn" class="btn btn-primary">Filter</button>
    <button id="reset-filter-btn" class="btn btn-secondary">Reset Filter</button>
</div>

                    </div>
                     <div class="float-end" style="color: red">
                        @if(request('company_id') || request('created'))
                            @if(request('company_id'))
                                <strong>Company ({{ strtoupper($selectedCompanyName) }}):</strong>
                                <span><b>{{ $selectedCompanyApiCallCount }}</b></span>
                            @endif
                            <br>
                            @if(request('created'))
                                <strong>User ({{ $selectedUserName }}):</strong>
                                <span><b>{{ $selectedUserApiCallCount }}</b></span>
                            @endif
                        @else
                            <strong>Total API Call Count :</strong>
                            <span id="total-api-call-count"><b>{{ $totalApiCallCount }}</b></span>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Driver Licence Number</th>
                                   <th>Driver Name</th>
                                    <th>Depot Name</th>
                                    <th>Company Name</th>

                                    <th>Last LC Check</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apiLogs as $log)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $log->licence_no }}</td>
                                                                           <td class="text-center">{{ $log->drivers->name ?? '-' }}</td>
                                                                                <td class="text-center">{{ $log->drivers->depot->name ?? '-' }}</td>
                                        <td class="text-center">{{ !empty($log->companyDetails) ? strtoupper($log->companyDetails->name) : '' }}</td>
                                        <td class="text-center">{{ $log->last_lc_check }}</td>
<!--<td class="text-center">-->
<!--    @if($log->created === 'Auto Generator')-->
<!--        Auto Generator-->
<!--    @elseif(!empty($log->creator))-->
<!--        {{ $log->creator->username }}-->
<!--    @else-->
<!--        N/A-->
<!--    @endif-->
<!--</td>-->
<td class="text-center">{{ !empty($log->creator)? $log->creator->username : ($log->created == 1.1
                                    ? 'Automation' : ($log->created == 'Auto Generator' ? 'Auto Generator' : '')) }}
                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No logs found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
