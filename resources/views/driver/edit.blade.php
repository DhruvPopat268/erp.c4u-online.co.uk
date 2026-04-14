{{ Form::model($driver, ['route' => ['driver.update', $driver->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('driver_licence_no', __('Driver Licence No')) }}
                {{ Form::text('driver_licence_no', $driver->driver_licence_no, [
                    'class' => 'form-control',
                    $driver->driver_licence_status === 'Valid' || $driver->driver_licence_status === 'VALID' ? 'readonly' : ''
                ]) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('companyName', __('Company Name'),['class'=>'form-label']) }}
                {{ Form::select('companyName', array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
            </div>

            <div class="form-group col-md-6" style="margin-top: 7px;">
                {{ Form::label('driver_status', __('Driver Status')) }}
                {{ Form::select('driver_status', ['Active' => 'Active', 'InActive' => 'InActive', 'Archive' => 'Archive'], $driver->driver_status, ['class' => 'form-control']) }}
            </div>

        </div>

        <div class="row">
                     <div class="form-group col-md-6">
                {{ Form::label('automation', __('Automated Licence Check')) }}
                {{ Form::select('automation', ['Yes' => 'Yes', 'No' => 'No'], $driver->automation, ['class' => 'form-control']) }}
            </div>
             <div class="form-group col-md-6">
                {{ Form::label('ni_number', __('Driver Number')) }}
                {{ Form::text('ni_number', $driver->ni_number, array('class' => 'form-control')) }}
            </div>
            </div>

        <div class="row">
              <div class="form-group col-md-6">
                {{ Form::label('first_names', __('First Name')) }}
                {{ Form::text('first_names', $driver->first_names, array('class' => 'form-control')) }}
            </div>
                        <div class="form-group col-md-6">
                {{ Form::label('last_name', __('Surname')) }}
                {{ Form::text('last_name', $driver->last_name, array('class' => 'form-control')) }}
            </div>

            </div>

        <div class="row">

             <div class="form-group col-md-6">
        {{ Form::label('driver_dob', __('Driver DOB')) }}
        @php
            // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
            if (!empty($driver->driver_dob) && $driver->driver_dob != '-') {
                try {
                    $driverDOB = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->driver_dob)->format('Y-m-d');
                } catch (\Exception $e) {
                    $driverDOB = null;
                }
            } else {
                $driverDOB = null;
            }
        @endphp
        {{ Form::date('driver_dob', $driverDOB, ['class' => 'form-control', 'id' => 'driver_dob', 'onchange' => 'calculateAge(this.value)']) }}
    </div>
            <div class="form-group col-md-6">
                {{ Form::label('username', __('Username')) }}
                {{ Form::text('username', old('username', $driver->driverUser->username ?? ''), ['class' => 'form-control', ]) }}
            </div>
        </div>


        {{--  <div class="row">
            <div class="form-group">
                {{ Form::label('post_code', __('Post Code')) }}
                {{ Form::text('post_code', $driver->post_code, array('class' => 'form-control' )) }}
            </div>
        </div>  --}}
       <div class="row">

        <div class="form-group col-md-6">
            {{ Form::label('contact_no', __('Contact No')) }}
        @php
            // Remove any leading '+' or '44' from the contact number for display
            $displayContactNo = ltrim($driver->contact_no, '+');
            $displayContactNo = ltrim($displayContactNo, '44');
        @endphp
        {{ Form::text('contact_no', $displayContactNo, ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('contact_email', __('Contact Email')) }}
        {{ Form::text('contact_email', $driver->contact_email, array('class' => 'form-control',)) }}
    </div>


    </div>
    <div class="row">


        <div class="form-group col-md-6">
        {{ Form::label('group_id', __('Driver Groups'), ['class' => 'form-label']) }}
        {{ Form::select('group_id', ['' => __('Select a company first')], $driver->group_id, ['class' => 'form-control','required' => true ]) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('depot_id', __('Depot Name'), ['class' => 'form-label']) }}
        {{ Form::select('depot_id', ['' => __('Select a company first')], $driver->depot_id, ['class' => 'form-control','required' => true ]) }}
    </div>
    
    <div class="form-group">
        {{ Form::label('depot_access_status', __('Depot Change Allowed')) }}
        {{ Form::select('depot_access_status', ['Yes' => 'Yes', 'No' => 'No'], $driver->depot_access_status, ['class' => 'form-control']) }}
    </div>
    
    <div class="form-group">
        {{ Form::label('consent_form_status', __('Consent Form')) }}
        {{ Form::select('consent_form_status', ['Yes' => 'Yes', 'No' => 'No'], $driver->consent_form_status, ['class' => 'form-control']) }}
    </div>
</div>

{{-- <div class="row">
    <div class="form-group">
        {{ Form::label('driver_age', __('Driver Age')) }}
        @php
            // Calculate driver_age if driver_dob is set
            if (!empty($driver->driver_dob) && $driver->driver_dob != '-') {
                try {
                    $driverAge = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->driver_dob)->age;
                } catch (\Exception $e) {
                    $driverAge = null;
                }
            } else {
                $driverAge = null;
            }
        @endphp
        {{ Form::text('driver_age', $driverAge, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'driver_age']) }}
    </div>
</div>

        <div class="row">
            <div class="form-group">
                {{ Form::label('driver_address', __('Driver Address')) }}
                {{ Form::text('driver_address', $driver->driver_address, array('class' => 'form-control' )) }}
            </div>
        </div>  --}}


    {{--  <div class="row">
        <div class="form-group">
            {{ Form::label('driver_licence_no', __('Driver Licence No')) }}
            {{ Form::text('driver_licence_no', $driver->driver_licence_no, array('class' => 'form-control')) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group" style="display: none">
            {{ Form::label('driver_licence_status', __('Driver Licence Status')) }}
            {{ Form::select('driver_licence_status', ['VALID' => 'VALID', 'EXPIRING SOON' => 'EXPIRING SOON','EXPIRED' => 'EXPIRED'], $driver->driver_licence_status, ['class' => 'form-control']) }}

        </div>
    </div>
    <div class="row">
        <div class="form-group">
            {{ Form::label('driver_licence_expiry', __('Driver Licence expiry')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                $driverLicenceExpiry = !empty($driver->driver_licence_expiry) ? \Carbon\Carbon::createFromFormat('d/m/Y', $driver->driver_licence_expiry)->format('Y-m-d') : null;
            @endphp
            {{ Form::date('driver_licence_expiry', $driverLicenceExpiry, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="row">
        <div class="form-group" style="display: none">
            {{ Form::label('cpc_status', __('CPC Status')) }}
            {{ Form::select('cpc_status', ['VALID' => 'VALID', 'EXPIRING SOON' => 'EXPIRING SOON','EXPIRED' => 'EXPIRED'], $driver->cpc_status, ['class' => 'form-control']) }}

        </div>
    </div>
    <div class="row">
        <div class="form-group">
            {{ Form::label('cpc_validto', __('CPC valid to')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                if (!empty($driver->cpc_validto) && $driver->cpc_validto != '-') {
                    $cpcValidTo = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->cpc_validto)->format('Y-m-d');
                } else {
                    $cpcValidTo = null;
                }
                @endphp
            {{ Form::date('cpc_validto', $cpcValidTo, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            {{ Form::label('tacho_card_no', __('Tacho Card No')) }}
            {{ Form::text('tacho_card_no', $driver->tacho_card_no, array('class' => 'form-control')) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group" style="display: none">
            {{ Form::label('tacho_card_status', __('Tacho card status')) }}
            {{ Form::select('tacho_card_status', ['VALID' => 'VALID', 'EXPIRING SOON' => 'EXPIRING SOON','EXPIRED' => 'EXPIRED'], $driver->tacho_card_status, ['class' => 'form-control']) }}

        </div>
    </div>
    <div class="row">
        <div class="form-group">
            {{ Form::label('tacho_card_valid_from', __('Tacho card valid from')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
            if (!empty($driver->tacho_card_valid_from) && $driver->tacho_card_valid_from != '-') {
                $tachoCardValidFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->tacho_card_valid_from)->format('Y-m-d');
            } else {
                $tachoCardValidFrom = null;
            }
            @endphp

            {{ Form::date('tacho_card_valid_from', $tachoCardValidFrom, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            {{ Form::label('tacho_card_valid_to', __('Tacho card valid to')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                if (!empty($driver->tacho_card_valid_to) && $driver->tacho_card_valid_to != '-') {
                    $tachoCardValidTo = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->tacho_card_valid_to)->format('Y-m-d');
                } else {
                    $tachoCardValidTo = null;
                }

                @endphp
            {{ Form::date('tacho_card_valid_to', $tachoCardValidTo, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            {{ Form::label('latest_lc_check', __('Latest LC Check')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                if (!empty($driver->latest_lc_check) && $driver->latest_lc_check != '-') {
                    $latestLcCheck = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->latest_lc_check)->format('Y-m-d');
                } else {
                    $latestLcCheck = null;
                }
                @endphp
            {{ Form::date('latest_lc_check', $latestLcCheck, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            {{ Form::label('comment', __('Comment')) }}
            {{ Form::text('comment', $driver->comment, array('class' => 'form-control')) }}
        </div>
    </div>  --}}

            <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('token_valid_from_date', __('Licence Valid From')) }}
                @php
                    // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                    if (!empty($driver->token_valid_from_date) && $driver->token_valid_from_date != '-') {
                        $licenceValidFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->token_valid_from_date)->format('Y-m-d');
                    } else {
                        $licenceValidFrom = null;
                    }
                    @endphp
                {{ Form::date('token_valid_from_date', $licenceValidFrom, ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('driver_licence_expiry', __('Licence Valid To')) }}
                @php
                    // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                    if (!empty($driver->driver_licence_expiry) && $driver->driver_licence_expiry != '-') {
                        $licenceValidTo = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->driver_licence_expiry)->format('Y-m-d');
                    } else {
                        $licenceValidTo = null;
                    }
                    @endphp
                {{ Form::date('driver_licence_expiry', $licenceValidTo, ['class' => 'form-control']) }}
            </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('tacho_card_valid_from', __('Driver Tachograph Valid From')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                if (!empty($driver->tacho_card_valid_from) && $driver->tacho_card_valid_from != '-') {
                    $TachographValidFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->tacho_card_valid_from)->format('Y-m-d');
                } else {
                    $TachographValidFrom = null;
                }
                @endphp
            {{ Form::date('tacho_card_valid_from', $TachographValidFrom, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tacho_card_valid_to', __('Driver Tachograph Valid To')) }}
            @php
                // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
                if (!empty($driver->tacho_card_valid_to) && $driver->tacho_card_valid_to != '-') {
                    $TachographValidTo = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->tacho_card_valid_to)->format('Y-m-d');
                } else {
                    $TachographValidTo = null;
                }
                @endphp
            {{ Form::date('tacho_card_valid_to', $TachographValidTo, ['class' => 'form-control']) }}
        </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {{ Form::label('dqc_issue_date', __('Driver Qualification Card (CPC) Valid From')) }}
        @php
            // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
            if (!empty($driver->dqc_issue_date) && $driver->dqc_issue_date != '-') {
                $CPCValidFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->dqc_issue_date)->format('Y-m-d');
            } else {
                $CPCValidFrom = null;
            }
            @endphp
        {{ Form::date('dqc_issue_date', $CPCValidFrom, ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('cpc_validto', __('Driver Qualification Card (CPC) Valid To')) }}
        @php
            // Convert dd/mm/yyyy to Y-m-d format for HTML5 date input
            if (!empty($driver->cpc_validto) && $driver->cpc_validto != '-') {
                $CPCValidTo = \Carbon\Carbon::createFromFormat('d/m/Y', $driver->cpc_validto)->format('Y-m-d');
            } else {
                $CPCValidTo = null;
            }
            @endphp
        {{ Form::date('cpc_validto', $CPCValidTo, ['class' => 'form-control']) }}
    </div>
</div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>

{{ Form::close() }}
<script>
    function calculateAge(dob) {
        if (dob) {
            const today = new Date();
            const birthDate = new Date(dob);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById('driver_age').value = age;
        } else {
            document.getElementById('driver_age').value = '';
        }
    }
</script>

<script>
    $(document).ready(function() {
        // Fetch initial groups if company is already selected
        var initialCompanyId = $('select[name="companyName"]').val();
        if (initialCompanyId) {
            fetchGroups(initialCompanyId, '{{ $driver->group_id }}');
        }

        if (initialCompanyId) {
            fetchDepots(initialCompanyId, '{{ $driver->depot_id }}');
        }

        // Event listener for companyName change
        $('select[name="companyName"]').change(function() {
            var companyId = $(this).val();  // Get selected company ID
            fetchGroups(companyId);
        });

        $('select[name="companyName"]').change(function() {
            var companyId = $(this).val();  // Get selected company ID
            fetchDepots(companyId);
        });

        function fetchGroups(companyId, selectedGroupId = null) {
            if (companyId) {
                // Fetch the filtered groups via AJAX
                $.ajax({
                    url: "{{ url('get-groups-by-company') }}/" + companyId,
                    type: 'GET',
                    success: function(data) {
                        var group_idSelect = $('select[name="group_id"]');
                        group_idSelect.empty();  // Clear the current options

                        // Add a default "Select Group" option
                        group_idSelect.append('<option value="">{{ __("Select Group") }}</option>');

                        // Add new options to the dropdown
                        $.each(data, function(key, value) {
                            var isSelected = (key == selectedGroupId) ? 'selected' : '';
                            group_idSelect.append('<option value="'+ key +'" '+ isSelected +'>'+ value +'</option>');
                        });
                    }
                });
            } else {
                // If no company is selected, reset the group_id dropdown
                $('select[name="group_id"]').html('<option value="">{{ __("Select a company first") }}</option>');
            }
        }

        function fetchDepots(companyId, selectedGroupId = null) {
            if (companyId) {
                // Fetch the filtered groups via AJAX
                $.ajax({
                    url: "{{ url('get-depots-by-company') }}/" + companyId,
                    type: 'GET',
                    success: function(data) {
                        var depot_idSelect = $('select[name="depot_id"]');
                        depot_idSelect.empty();  // Clear the current options

                        // Add a default "Select Group" option
                        depot_idSelect.append('<option value="">{{ __("Select Depot") }}</option>');

                        // Add new options to the dropdown
                        $.each(data, function(key, value) {
                            var isSelected = (key == selectedGroupId) ? 'selected' : '';
                            depot_idSelect.append('<option value="'+ key +'" '+ isSelected +'>'+ value +'</option>');
                        });
                    }
                });
            } else {
                // If no company is selected, reset the group_id dropdown
                $('select[name="depot_id"]').html('<option value="">{{ __("Select a company first") }}</option>');
            }
        }
    });
</script>
