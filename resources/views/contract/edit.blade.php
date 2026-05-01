{{ Form::model($contract, array('route' => array('contract.update', $contract->id), 'method' => 'PUT')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp

    {{-- end for ai module--}}
    <div class="row">
         <div class="form-group col-md-12">
            {{ Form::label('vehicle_nick_name', __('Vehicle ID'),['class'=>'form-label']) }}
            {{ Form::text('vehicle_nick_name', $contract->vehicle_nick_name, array('class' => 'form-control')) }}
        </div>
        @php
            $fixedStatuses = ['Owned', 'Rented', 'Leased', 'Contract Hire', 'Depot Transfer', 'Archive'];
            $currentStatus = $contract->vehicle_status;
            $isCustomStatus = $currentStatus && !in_array($currentStatus, $fixedStatuses);
            $statusOptions = [
                'Owned'          => 'Owned',
                'Rented'         => 'Rented',
                'Leased'         => 'Leased',
                'Contract Hire'  => 'Contract Hire',
                'Depot Transfer' => 'Depot Transfer',
                'Archive'        => 'Archive',
            ];
            if ($isCustomStatus) {
                $statusOptions[$currentStatus] = 'Other (' . $currentStatus . ')';
            }
        @endphp
        <div class="form-group col-md-12">
            {{ Form::label('vehicle_status', __('Vehicle Status'), ['class' => 'form-label']) }}
            {{ Form::select('vehicle_status', $statusOptions, $currentStatus, ['class' => 'form-control', 'id' => 'vehicle_status']) }}
        </div>

        <div class="form-group col-md-12" id="archive_options" style="{{ $contract->vehicle_status == 'Archive' ? '' : 'display: none;' }}">
            {{ Form::label('archive_reason', __('Archive Reason'), ['class' => 'form-label']) }}
            {{ Form::select('archive_reason', [
                '' => 'Select Reason',
                'Sold' => 'Sold',
                'Scrapped' => 'Scrapped',
                'Write off' => 'Write off',
                'In repair/VOR' => 'In repair/VOR',
                'Other' => 'Other'
            ], $archiveReason, ['class' => 'form-control', 'id' => 'archive_reason']) }}
        </div>

        <div class="form-group col-md-12" id="archive_other_text" style="{{ ($archiveReason == 'Other') ? '' : 'display: none;' }}">
            {{ Form::label('archive_other_text', __('Please specify'), ['class' => 'form-label']) }}
            {{ Form::text('archive_other_text', $archiveOtherText, ['class' => 'form-control', 'placeholder' => 'Please specify']) }}
        </div>


        <!-- <div class="form-group col-md-12">-->
        <!--    @if($contract->tacho_calibration)-->
        <!--        {{ Form::label('tacho_calibration', __('Tacho Calibration Expiry'), ['class' => 'form-label']) }}-->
        <!--        {{ Form::date('tacho_calibration', $contract->tacho_calibration, ['class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'), 'readonly' => true]) }}-->
        <!--        <small style="color: #361eb3">This value cannot be edited directly. To make changes, please update it in the Forward Planner.</small>-->
        <!--    @else-->
        <!--        {{ Form::label('tacho_calibration', __('Tacho Calibration Expiry'), ['class' => 'form-label']) }}-->
        <!--        {{ Form::date('tacho_calibration', null, ['class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}-->
        <!--    @endif-->
        <!--</div>-->
        <!--     <div class="form-group col-md-12">-->
        <!--        @if($contract->dvs_pss_permit_expiry)-->
        <!--    {{ Form::label('dvs_pss_permit_expiry', __('DVS/PSS Permit Expiry'),['class'=>'form-label']) }}-->
        <!--    {{ Form::date('dvs_pss_permit_expiry', $contract->dvs_pss_permit_expiry, array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'), 'readonly' => true)) }}-->
        <!--    <small style="color: #361eb3">This value cannot be edited directly. To make changes, please update it in the Forward Planner.</small>-->
        <!--    @else-->
        <!--    {{ Form::label('dvs_pss_permit_expiry', __('DVS/PSS Permit Expiry'),['class'=>'form-label']) }}-->
        <!--    {{ Form::date('dvs_pss_permit_expiry', null, ['class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}-->
        <!--@endif-->
        <!--</div>-->
        
                <div class="form-group col-md-12">

            {{ Form::label('tacho_calibration', __('Tacho Calibration Expiry'), ['class' => 'form-label']) }}

            @php
            // Editable if: field is null OR editFlags says true
            $isEditable = is_null($contract->tacho_calibration)
            ? true
            : ($editFlags['Tacho Calibration'] ?? false);

            $tachoAttributes = [
            'class' => 'form-control',
            'min' => \Carbon\Carbon::now()->format('Y-m-d'),
            ];

            if (!$isEditable) {
            $tachoAttributes['readonly'] = true;
            }
            @endphp

            {{ Form::date('tacho_calibration', $contract->tacho_calibration, $tachoAttributes) }}

            @if(!$isEditable && !is_null($contract->tacho_calibration))
            <small style="color:#361eb3">
                This value cannot be edited directly.
                To make changes, please update it in the Forward Planner (Tacho Calibration).
            </small>
            @endif

        </div>




        <div class="form-group col-md-12">

            {{ Form::label('dvs_pss_permit_expiry', __('DVS/PSS Permit Expiry'), ['class' => 'form-label']) }}

            @php
            $isEditable = is_null($contract->dvs_pss_permit_expiry)
            ? true
            : ($editFlags['DVS/PSS Permit Expiry'] ?? false);

            $attributes = [
            'class' => 'form-control',
            'min' => \Carbon\Carbon::now()->format('Y-m-d'),
            ];

            if (!$isEditable) {
            $attributes['readonly'] = true;
            }
            @endphp

            {{ Form::date('dvs_pss_permit_expiry', $contract->dvs_pss_permit_expiry, $attributes) }}

            @if(!$isEditable && !is_null($contract->dvs_pss_permit_expiry))
            <small style="color:#361eb3">
                This value cannot be edited directly.
                Update it from the Forward Planner (DVS/PSS Permit).
            </small>
            @endif

        </div>
        
        
        <div class="form-group col-md-12">
            {{ Form::label('insurance_type', __('Insurance Type'), ['class' => 'form-label']) }}
            <div class="row">
                @php
                    // Define insurance options
                    $insuranceOptions = ['Motor Insurance'];
                    // Decode the insurance types stored in the database into an array
                    $selectedInsurance = json_decode($contract->insurance_type, true) ?? []; // Default to empty array if null
                @endphp

                @foreach($insuranceOptions as $index => $insurance)
                    <div class="col-md-6">
                        <div class="form-check">
                            <!-- Check if the insurance option is selected, then mark it as checked -->
                            {{ Form::checkbox('insurance_type[]', $insurance, in_array($insurance, $selectedInsurance), ['class' => 'form-check-input', 'id' => 'insurance_'.$index]) }}
                            {{ Form::label('insurance_'.$index, $insurance, ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- <div class="form-group col-md-12" style="margin-top: -10px;">-->
        <!--    @if($contract->insurance)-->
        <!--    {{ Form::label('insurance', __('Insurance'),['class'=>'form-label']) }}-->
        <!--    {{ Form::date('insurance', $contract->insurance, array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'), 'readonly' => true)) }}-->
        <!--                    <small style="color: #361eb3">This value cannot be edited directly. To make changes, please update it in the Forward Planner.</small>-->
        <!--    @else-->
        <!--    {{ Form::label('insurance', __('Insurance'),['class'=>'form-label']) }}-->
        <!--    {{ Form::date('insurance', null, ['class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}-->
        <!--@endif-->

        <!--</div>-->
        
          <div class="form-group col-md-12" style="margin-top: -10px;">
            {{ Form::label('insurance', __('Insurance'), ['class' => 'form-label']) }}

            @php
            $isEditable = is_null($contract->insurance)
            ? true
            : ($editFlags['Insurance'] ?? false);

            $attributes = [
            'class' => 'form-control',
            'min' => \Carbon\Carbon::now()->format('Y-m-d'),
            ];

            if (!$isEditable) {
            $attributes['readonly'] = true;
            }
            @endphp

            {{ Form::date('insurance', $contract->insurance, $attributes) }}

            @if(!$isEditable && !is_null($contract->insurance))
            <small style="color:#361eb3">
                This value cannot be edited directly.
                Update it from the Forward Planner (Insurance).
            </small>
            @endif


        </div>
        
        
<!--                <div class="form-group col-md-12">-->
<!--                    @if($contract->date_of_inspection)-->
<!--            {{ Form::label('date_of_inspection', __('Date Of Inspection'),['class'=>'form-label']) }}-->
<!--            {{ Form::date('date_of_inspection', $contract->date_of_inspection, array('class' => 'form-control', 'id' => 'date_of_inspection', 'readonly' => true)) }}-->
<!--            <small style="color: #361eb3">This value cannot be edited directly. To make changes, please update it in the Forward Planner.</small>-->
<!--            @else-->
<!--            {{ Form::label('date_of_inspection', __('Date Of Inspection'),['class'=>'form-label']) }}-->
<!--            {{ Form::date('date_of_inspection', null, ['class' => 'form-control']) }}-->
<!--        @endif-->

<!--        </div>-->
<!--<div class="form-group col-md-12" id="pmi_interval_wrapper" style="display: none;">-->
<!--            @if($contract->PMI_intervals)-->
<!--            {{ Form::label('PMI_intervals', __('PMI Intervals (In Week)')) }}-->
<!--            {{ Form::text('PMI_intervals', $contract->PMI_intervals ? $contract->PMI_intervals : 'Select week', ['class' => 'form-control', 'id' => 'PMI_intervals','readonly' => true]) }}-->
<!--        <small style="color: #361eb3">This value cannot be edited directly. To make changes, please update it in the Forward Planner.</small>-->
<!--    @else-->
<!--    {{ Form::label('PMI_intervals', __('PMI Intervals (In Week)')) }}-->
<!--    {{ Form::select('PMI_intervals', ['' => 'Select week', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10'], $contract->PMI_intervals, ['class' => 'form-control',  'id' => 'PMI_intervals']) }}-->

<!--@endif-->

<!--</div>-->

        <div class="form-group col-md-12">

            @php
            $isEditable = is_null($contract->date_of_inspection)
            ? true
            : ($editFlags['PMI Due'] ?? false);

            $attr = ['class' => 'form-control'];
            if (!$isEditable) $attr['readonly'] = true;
            @endphp

            {{ Form::label('date_of_inspection', __('Date Of Inspection'), ['class' => 'form-label']) }}

            {{ Form::date('date_of_inspection', $contract->date_of_inspection, $attr) }}

            @if(!$isEditable && !is_null($contract->date_of_inspection))
            <small style="color:#361eb3">
                This value cannot be edited directly.
                To make changes, please update it in the Forward Planner (PMI Due).
            </small>
            @endif

        </div>

        <div class="form-group col-md-12" id="pmi_interval_wrapper" style="display: none;">

            {{ Form::label('PMI_intervals', __('PMI Intervals (In Week)')) }}

            @php
            $isEditable = is_null($contract->date_of_inspection)
            ? true
            : ($editFlags['PMI Due'] ?? false);
            @endphp

            @if($isEditable)
            {{ Form::select('PMI_intervals', [
            '' => 'Select week',
            '1' => '1','2' => '2','3' => '3','4' => '4','5' => '5',
            '6' => '6','7' => '7','8' => '8','9' => '9','10' => '10'
          ], $contract->PMI_intervals, ['class' => 'form-control']) }}
            @else
            {{ Form::text('PMI_intervals', $contract->PMI_intervals, [
            'class' => 'form-control',
            'readonly' => true
          ]) }}
            <small style="color:#361eb3">
                This value cannot be edited directly.
                To make changes, please update it in the Forward Planner (PMI Due).
            </small>
            @endif

        </div>


        <div class="form-group col-md-12">
            {{ Form::label('PMI_due', __('PMI Due'),['class'=>'form-label']) }}
            {{ Form::text('PMI_due', $contract->PMI_due, array('class' => 'form-control', 'id' => 'PMI_due', 'readonly'=> 'readonly')) }}
        </div>



        <div class="form-group col-md-12">
            {{ Form::label('odometer_reading', __('Odometer Reading'),['class'=>'form-label']) }}
            {{ Form::text('odometer_reading', $contract->odometer_reading, array('class' => 'form-control')) }}
        </div>
        <!-- <div class="form-group col-md-12">-->
        <!--    {{ Form::label('brake_test_due', __('Brake Test Due'),['class'=>'form-label']) }}-->
        <!--    {{ Form::date('brake_test_due', $contract->brake_test_due, array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'))) }}-->
        <!--</div>-->
        <div class="form-group col-md-6">
            {{ Form::label('companyName', __('Company Name'),['class'=>'form-label']) }}
            {{ Form::select('companyName', array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('group_id', __('Vehicle Groups'), ['class' => 'form-label']) }}
            {{ Form::select('group_id', ['' => __('Select a company first')], $contract->group_id, ['class' => 'form-control','required' => true]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('depot_id', __('Depots name'), ['class' => 'form-label']) }}
            {{ Form::select('depot_id', ['' => __('Select a company first')], $contract->depot_id, ['class' => 'form-control', 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('taxDueDate', __('Road Tax'), ['class' => 'form-label']) }}
            @if($contract->taxDueDate)
                {{ Form::text('taxDueDate', \Carbon\Carbon::createFromFormat('d F Y', $contract->taxDueDate)->format('Y-m-d'), [
                    'class' => 'form-control',
                    'readonly' => true
                ]) }}
            @else
                {{ Form::date('taxDueDate', null, [
                    'class' => 'form-control',
                    'min' => \Carbon\Carbon::now()->format('Y-m-d')
                ]) }}
            @endif
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('annual_test_expiry_date', __('MOT'), ['class' => 'form-label']) }}
@if(isset($contract->vehicle) && $contract->vehicle->annual_test_expiry_date)
                {{ Form::text('annual_test_expiry_date', \Carbon\Carbon::parse($contract->vehicle->annual_test_expiry_date)->format('Y-m-d'), [
                    'class' => 'form-control',
                    'readonly' => true
                ]) }}
            @else
                {{ Form::date('annual_test_expiry_date', null, [
                    'class' => 'form-control',
                    'min' => \Carbon\Carbon::now()->format('Y-m-d')
                ]) }}
            @endif
        </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>
$(document).ready(function () {
    const inspectionDateInput = $('#date_of_inspection');
    const pmiWrapper = $('#pmi_interval_wrapper');

    function togglePmiInterval() {
        const dateSelected = inspectionDateInput.val();
        if (dateSelected) {
            pmiWrapper.show();
        } else {
            pmiWrapper.hide();
            $('#PMI_intervals').val('');
            $('#PMI_due').val('');
        }
    }

    // ✅ Initial check on page load
    togglePmiInterval();

    // ✅ Recheck on date change
    inspectionDateInput.on('change', togglePmiInterval);

    // ✅ Update PMI Due date
    $('#PMI_intervals').change(function () {
        const interval = $(this).val();
        const inspectionDate = inspectionDateInput.val();
        if (interval && inspectionDate) {
            const dueDate = new Date(inspectionDate);
            dueDate.setDate(dueDate.getDate() + (7 * parseInt(interval)));

            const day = String(dueDate.getDate()).padStart(2, '0');
            const month = String(dueDate.getMonth() + 1).padStart(2, '0');
            const year = dueDate.getFullYear();

            $('#PMI_due').val(`${day}-${month}-${year}`);
        }
    });
});
    if ($(".multi-select").length > 0) {
        $( $(".multi-select") ).each(function( index,element ) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#'+id, {
                    removeItemButton: true,
                }
            );
        });
    }
    $(document).ready(function() {
        // Fetch initial groups if company is already selected
        var initialCompanyId = $('select[name="companyName"]').val();
        if (initialCompanyId) {
            fetchGroups(initialCompanyId, '{{ $contract->group_id }}');
        }

        if (initialCompanyId) {
            fetchDepots(initialCompanyId, '{{ $contract->depot_id }}');
        }

        $('select[name="companyName"]').change(function() {
            var companyId = $(this).val();  // Get selected company ID
            fetchDepots(companyId);
        });

        // Event listener for companyName change
        $('select[name="companyName"]').change(function() {
            var companyId = $(this).val();  // Get selected company ID
            fetchGroups(companyId);
        });

        function fetchGroups(companyId, selectedGroupId = null) {
            if (companyId) {
                // Fetch the filtered groups via AJAX
                $.ajax({
                    url: "{{ url('get-vehicle-groups-by-company') }}/" + companyId,
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

<script type="text/javascript">

    $(document).ready(function() {
        $('#vehicle_status').change(function() {
            if ($(this).val() === 'Archive') {
                $('#archive_options').show();
            } else {
                $('#archive_options').hide();
                $('#archive_reason').val(''); // Reset the archive reason dropdown
                $('#archive_other_text').hide(); // Hide the text input as well
                $('#archive_other_text').val(''); // Clear the input
            }
            updateCombinedStatus(); // Update combined status when vehicle status changes
        });

        $('#archive_reason').change(function() {
            if ($(this).val() === 'Other') {
                $('#archive_other_text').show().focus(); // Show the input for specifying the reason
            } else {
                $('#archive_other_text').hide();
                $('#archive_other_text').val(''); // Clear the input if not 'Other'
            }
            updateCombinedStatus(); // Update combined status when archive reason changes
        });

        $('#archive_other_text').on('input', function() {
            updateCombinedStatus(); // Update combined status when text input changes
        });

        // Function to update the combined status
        function updateCombinedStatus() {
            var vehicleStatus = $('#vehicle_status').val();
            var archiveReason = $('#archive_reason').val();
            var otherText = $('#archive_other_text').val().trim();
            var combinedValue = vehicleStatus;

            if (vehicleStatus === 'Archive') {
                if (archiveReason === 'Other' && otherText) {
                    combinedValue += ` (${otherText})`; // Use the input text for "Other"
                } else if (archiveReason) {
                    combinedValue += ` (${archiveReason})`; // Use selected reason
                }
            }

            $('#combined_status').val(combinedValue); // Set the hidden input value
        }
    });

    $( ".client_select" ).change(function() {

        var client_id = $(this).val();
        getparent(client_id);
    });

    function getparent(bid) {

        $.ajax({
            url: `{{ url('contract/clients/select')}}/${bid}`,
            type: 'GET',
            success: function (data) {
                console.log(data);
                $("#project_id").html('');
                $('#project_id').append('<select class="form-control" id="project_id" name="project_id[]"  ></select>');
                //var sdfdsfd = JSON.parse(data);
                $.each(data, function (i, item) {
                    //console.log(item.name);
                    $('#project_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                // var multipleCancelButton = new Choices('#project_id', {
                //     removeItemButton: true,
                // });

                if (data == '') {
                    $('#project_id').empty();
                }
            }
        });
    }

 $('#PMI_intervals').change(function() {
        var interval = $(this).val();
        var inspectionDate = $('#date_of_inspection').val(); // Get the date_of_inspection value
        if (interval && inspectionDate) {
            var dueDate = new Date(inspectionDate); // Use the selected date_of_inspection
            dueDate.setDate(dueDate.getDate() + (7 * interval));

            var day = String(dueDate.getDate()).padStart(2, '0');
            var month = String(dueDate.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            var year = dueDate.getFullYear();

            var formattedDate = day + '-' + month + '-' + year;
            $('#PMI_due').val(formattedDate);
        }
    });

    $(document).ready(function() {
        $('#insurance_type_select').change(function() {
            var selectedValue = $(this).val();
            if (selectedValue === 'other') {
                $('#other_insurance_type_field').show();
            } else {
                $('#other_insurance_type_field').hide();
            }
        });

        // Initially check and show insurance_other field if 'other' is selected
        if ($('#insurance_type_select').val() === 'other') {
            $('#other_insurance_type_field').show();
        }
    });




</script>
