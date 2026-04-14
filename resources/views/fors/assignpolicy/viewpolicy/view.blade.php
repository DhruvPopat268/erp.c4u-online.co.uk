@extends('layouts.admin')
@section('page-title')
{{ __('Policy Log') }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="#">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Policy Log') }}</li>
@endsection
@push('script-page')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function checkFiltersApplied() {
            var urlParams = new URLSearchParams(window.location.search);
            var policyId = urlParams.get('policy_id');
            var policyVersion = urlParams.get('policy_version');

            if (policyId === 'all') {
                $('#download_pdf_btn').hide();
            } else if (urlParams.has('company_id') || urlParams.has('policy_id') || urlParams.has('policy_version')) {
                $('#download_pdf_btn').show();
            } else {
                $('#download_pdf_btn').hide();
            }
        }

        checkFiltersApplied();

        $('#company_id').change(function() {
            var companyId = $(this).val();
            if (companyId) {
                $.ajax({
                    url: "{{ route('assign.policy.names') }}",
                   type: "GET",
                   data: { company_id: companyId },
                    success: function(data) {
                        var $policySelect = $('#policy_id');
                        $policySelect.empty();
                        $policySelect.append('<option value="">{{ __('Select Policy Name') }}</option>');
                        $policySelect.append('<option value="all">{{ __('Select All Policies') }}</option>');
                        $.each(data.policy_names, function(key, value) {
                            $policySelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                        checkFiltersApplied();
                    }
                });

                $.ajax({
                    url: "{{ route('assign.policy.versions') }}",
                   type: "GET",
                   data: { company_id: companyId },
                    success: function(data) {
                        var $versionSelect = $('#policy_version');
                        $versionSelect.empty();
                        $versionSelect.append('<option value="">{{ __('Select Policy Version') }}</option>');
                        $.each(data.policy_versions, function(key, value) {
                            $versionSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                        checkFiltersApplied();
                    }
                });
            } else {
                $('#policy_id').empty().append('<option value="">{{ __('Select Policy Name') }}</option>');
                $('#policy_version').empty().append('<option value="">{{ __('Select Policy Version') }}</option>');
                checkFiltersApplied();
            }
        });

        $('#policy_id').change(function() {
            var policyId = $(this).val();
            var companyId = $('#company_id').val();
            if (companyId && policyId) {
                $.ajax({
                    url: "{{ route('assign.policy.versions') }}",
                   type: "GET",
                   data: { company_id: companyId, policy_id: policyId },
                    success: function(data) {
                        var $versionSelect = $('#policy_version');
                        $versionSelect.empty();
                        $versionSelect.append('<option value="">{{ __('Select Policy Version') }}</option>');
                        $.each(data.policy_versions, function(key, value) {
                            $versionSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                        checkFiltersApplied();
                    }
                });
            } else {
                $('#policy_version').empty().append('<option value="">{{ __('Select Policy Version') }}</option>');
                checkFiltersApplied();
            }
        });

        $('#reset_filters').click(function() {
            window.location.href = "{{ route('fors.assignpolicy.view') }}";
        });





       $('form').submit(function() {
           checkFiltersApplied();
       });

   });

   $(document).ready(function() {

    var selectedPolicyId = '{{ request("policy_id") }}';
    var selectedVersion = '{{ request("policy_version") }}';

    var isCompanyUser = $('#company_id').length > 0;

    if (!isCompanyUser) {

        $.ajax({
            url: "{{ route('assign.policy.names') }}",
            type: "GET",
            success: function(data) {

                var $policySelect = $('#policy_id');
                $policySelect.empty();

                $policySelect.append('<option value="">{{ __('Select Policy Name') }}</option>');
                $policySelect.append('<option value="all">Select All Policies</option>');

                $.each(data.policy_names, function(key, value) {

                    let selected = (selectedPolicyId == key) ? 'selected' : '';

                    $policySelect.append(
                        '<option value="' + key + '" ' + selected + '>' + value + '</option>'
                    );
                });
            }
        });
    }

    if (!$('#company_id').length) {

    $.ajax({
        url: "{{ route('assign.policy.versions') }}",
        type: "GET",
        data: {
            policy_id: '{{ request("policy_id") }}'
        },
        success: function(data) {

            var $versionSelect = $('#policy_version');
            $versionSelect.empty();

            $versionSelect.append('<option value="">Select Policy Version</option>');

            $.each(data.policy_versions, function(key, value) {

                let selected = (selectedVersion == key) ? 'selected' : '';

                $versionSelect.append(
                    '<option value="' + key + '" ' + selected + '>' + value + '</option>'
                );
            });
        }
    });
}

});

   $(document).ready(function() {
        $(document).on('click', '.reassign-policy-btn', function() {
            var assignmentId = $(this).data('assignment-id');

            // Show the overlay and loader
            $('#overlay').show();
            $('#loader').show();

            $.ajax({
                url: "{{ route('policy.reassign') }}", // Ensure this is the correct route
                type: 'POST',
                data: {
                    assignment_id: assignmentId,
                    _token: "{{ csrf_token() }}" // Include CSRF token
                },
                success: function(response) {
                    // Store the response message and type in local storage
                    localStorage.setItem('responseMessage', response.message);
                    localStorage.setItem('responseType', response.success ? 'success' : 'error');

                    // Reload the page
                    window.location.reload();
                },
                error: function(xhr) {
                    // Store the generic error message in local storage
                    localStorage.setItem('responseMessage', 'Failed to reassign policy');
                    localStorage.setItem('responseType', 'error');

                    // Reload the page
                    window.location.reload();
                },
                complete: function() {
                    // Hide the overlay and loader when the request is complete
                    $('#overlay').hide();
                    $('#loader').hide();
                }
            });
        });

        // Display message logic
        var message = localStorage.getItem('responseMessage');
        var messageType = localStorage.getItem('responseType');

        if (message) {
            var messageClass = messageType === 'success' ? 'alert-success' : 'alert-danger';
            $('body').prepend('<div id="message" class="alert ' + messageClass + '" style="position: fixed; top: 28px; right: 55px;">' + message + '</div>');

            setTimeout(function() {
                $('#message').fadeOut('slow');
                localStorage.removeItem('responseMessage');
                localStorage.removeItem('responseType');
            }, 2000);
        }
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
@endpush
<style>


   .tboday {
   text-align: left;
   }
   .spinner-border {
   width: 3rem;
   height: 3rem;
   border-width: 0.3em;
   }
   #overlay {
   backdrop-filter: blur(5px); /* Apply blur effect */
   }
   #loader .spinner-border {
   border-color: #ffffff transparent transparent transparent;
   }
   #message {
   position: fixed;
   top: 10px;
   right: 10px;
   z-index: 9999;
   padding: 10px;
   border-radius: 5px;
   color: #fff;
   }
   .alert-success {
   background-color: #48494B !important;
   border-color: #48494B !important;
   }
   .alert-danger {
   background-color: #ff3a6e !important;
   }
</style>
<!--@section('breadcrumb')-->
<!--<li class="breadcrumb-item"><a href="#">{{ __('Dashboard') }}</a></li>-->
<!--<li class="breadcrumb-item">{{ __('View Assign Policy') }}</li>-->
<!--@endsection-->
@section('action-btn')
<div class="float-end">
   <!-- Add any action buttons if needed -->
</div>
@endsection
@section('content')
<div id="overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998; backdrop-filter: blur(5px);"></div>
<div id="loader" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
   <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
   </div>
</div>
@if (session('message'))
<div class="alert {{ session('alert-class', 'alert-info') }}" role="alert">
   {{ session('message') }}
</div>
@endif
<div class="row">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <form method="GET" action="{{ route('fors.assignpolicy.view') }}">
               <div class="row g-2 align-items-end mb-3">
                  @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                <div class="col-lg-2 col-md-3 col-sm-6">
                     <label for="company_id" class="form-label">
                        {{ __('Company') }}
                        <!--<span class="text-danger">*</span>-->
                    </label>
                     <select id="company_id" name="company_id" class="form-select" >
                        <option value="">{{ __('Select Company') }}</option>
                        @foreach($companies->sortBy('name') as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                        {{ strtoupper($company->name) }}
                        </option>
                        @endforeach
                     </select>
                  </div>
                      @endif
                  <div class="col-lg-2 col-md-3 col-sm-6">
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


                    <div class="col-lg-2 col-md-3 col-sm-6">
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
                  <div class="col-lg-2 col-md-3 col-sm-6">
                      <label for="policy_id" class="form-label">{{ __('Policy Name') }}
                      <!--<span class="text-danger">*</span>-->
                      </label>
                     <select id="policy_id" name="policy_id" class="form-select" >
                        <option value="">{{ __('Select Policy Name') }}</option>
                        <option value="all" {{ request('policy_id') == 'all' ? 'selected' : '' }}>{{ __('Select All Policies') }}</option>
                        @foreach($policyNames as $policyId => $policyName)
                        <option value="{{ $policyId }}" {{ request('policy_id') == $policyId ? 'selected' : '' }}>
                        {{ $policyName }}
                        </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-lg-2 col-md-3 col-sm-6">
                     <label for="policy_version" class="form-label">{{ __('Policy Version') }}
                     <!--<span class="text-danger">*</span>-->
                     </label>
                     <select id="policy_version" name="policy_version" class="form-select" >
                        <option value="">{{ __('Select Policy Version') }}</option>
                        @foreach($policyVersions as $version)
                        <option value="{{ $version }}" {{ request('policy_version') == $version ? 'selected' : '' }}>
                        {{ $version }}
                        </option>
                        @endforeach
                     </select>
                  </div>
                 <div class="col-lg-2 col-md-3 col-sm-6">
                     <label for="status" class="form-label">{{ __('Status') }}
                     <!--<span class="text-danger">*</span>-->
                     </label>
                     <select id="status" name="status" class="form-select" >
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('Select All Statuses') }}</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                        </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 d-flex align-items-end" style="margin-top: 10px;">
                     <button type="submit" class="btn btn-primary me-2">{{ __('Apply') }}</button>
                     <button type="button" id="reset_filters" class="btn btn-secondary me-2">{{ __('Reset Filters') }}</button>
                     <a id="download_pdf_btn" href="{{ route('assign.policy.pdf', request()->query()) }}" class="btn btn-success me-2" style="display:none;">{{ __('Generate Policy') }}</a>
                     <a href="{{ route('assign.policy.export', request()->query()) }}"
       class="btn btn-success"
       id="export_btn">
        <i class="fa fa-download"></i> {{ __('Export') }}
    </a>
                  </div>
               </div>
            </form>
            <div class="table-responsive mt-3">
               <table class="table datatable">
                  <thead>
                     <tr>
                        <th class="text-end">{{ __('Action') }}</th>
                        <th>{{ __('Status') }}</th>
                        <!--<th>{{ __('Policy Type') }}</th>-->
                        <th>{{ __('Policy Name') }}</th>
                        <th>{{ __('Driver Name') }}</th>
                        <th>{{ __('Company Name') }}</th>
                        <th>{{ __('Signature') }}</th>

                         <th>{{ __('Duration') }}</th>
                        <th>{{ __('Release Date') }}</th>
                        <th>{{ __('Version') }}</th>
                        <th>{{ __('Comment') }}</th>
                        <th>{{ __('Assign By') }}</th>
                     </tr>
                  </thead>
                  <tbody class="tboday">
                     @foreach($policyAssignments as $assignment)
                     <tr @if($assignment->status === 'Pending')
        style="background-color: #ff00005c; color: #000;"
    @elseif($assignment->status === 'Reassigned')
        style="background-color: #ffa5008c; color: #000;"
    @endif
    >
                        <td>

                           @php
   $reassignStatuses = ['Decline', 'Accept'];
@endphp

@if(in_array($assignment->status, $reassignStatuses))
<button class="btn btn-warning reassign-policy-btn"
        data-assignment-id="{{ $assignment->id }}" style="background-color: #48494B; border-color:#48494B ">
   {{ __('Re-Assign Policy') }}
</button>
@endif

                        </td>
                        <td>{{ $assignment->status }}</td>
<!--<td>-->
<!--    @if($assignment->policy_type === 'bronze')-->
<!--        Browse-->
<!--    @else-->
<!--        {{ ucfirst($assignment->policy_type) }}-->
<!--    @endif-->
<!--</td>-->
                        <td>{{ $assignment->policy_name ?? null }}</td>
                        <td>{{ $assignment->driver->name ?? null }}</td>
                        <td>{{ strtoupper($assignment->company->name ?? null) }}</td>
                        <td>
                           @if($assignment->signature)
                           <img src="{{ asset('storage/' . $assignment->signature) }}" alt="Signature" style="width: 100px; height: auto;">
                           @endif
                        </td>

                        <td>{{ $assignment->duration }}</td>
                        <td>{{ $assignment->reviewed_on }}</td>
                        <td>{{ $assignment->policy_version }}</td>
                        <td>{{ $assignment->comment }}</td>
                        <td>{{ $assignment->creator->username }}</td>
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
