{{ Form::open(array('url' => 'contract')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp

    {{-- end for ai module--}}
    <div class="row">

          <div class="form-group col-md-6">
            {{ Form::label('companyName', __('Company Name'."*"), ['class' => 'form-label']) }}
            {{ Form::select('companyName', ['' => __('Select a company')] + array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}


        </div>
<div class="form-group col-md-6">
    {{ Form::label('group_id', __('Vehicle Groups'."*"), ['class' => 'form-label']) }}
    {{ Form::select('group_id', ['' => __('Select a company first')], null, ['class' => 'form-control', 'required' => 'required']) }}
</div>

<div class="form-group col-md-6">
    {{ Form::label('depot_id', __('Depot Name'."*"), ['class' => 'form-label']) }}
    {{ Form::select('depot_id', ['' => __('Select a company first')], null, ['class' => 'form-control', 'required' => 'required']) }}
</div>
        <div class="form-group col-md-6">
            {{ Form::label('vehicle_status', __('Vehicle Status'."*"),['class'=>'form-label']) }}
            {{ Form::select('vehicle_status', [
            '' => 'Select Vehicle Status',
                'Owned' => 'Owned',
                'Rented' => 'Rented',
                'Leased' => 'Leased',
                'Contract Hire' => 'Contract Hire',
                'Depot Transfer' => 'Depot Transfer',
                'Archive' => 'Archive'
            ], null, ['class' => 'form-control', 'id' => 'vehicle_status', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6" id="archive_options" style="display:none;">
            {{ Form::label('archive_reason', __('Archive Reason'), ['class' => 'form-label']) }}
            {{ Form::select('archive_reason', [
                '' => 'Select Reason',
                'Sold' => 'Sold',
                'Scrapped' => 'Scrapped',
                'Write off' => 'Write off',
                'In repair/VOR' => 'In repair/VOR',
                'Other' => 'Other'
            ], null, ['class' => 'form-control', 'id' => 'archive_reason']) }}
            <input type="text" id="archive_other_text" name="archive_other" class="form-control" placeholder="Please specify" style="display:none; margin-top: 10px;">
        </div>
        <input type="hidden" id="combined_status" name="combined_status">



        <div class="form-group col-md-12">
            {{ Form::label('registrationNumber', __('Registration Number'),['class'=>'form-label']) }}
            {{ Form::text('registrationNumber', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
                <div class="form-group col-md-12">
            {{ Form::label('vehicle_nick_name', __('Vehicle ID'),['class'=>'form-label']) }}
            {{ Form::text('vehicle_nick_name', '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('tacho_calibration', __('Tacho Calibration Expiry'),['class'=>'form-label']) }}
            {{ Form::date('tacho_calibration', '', array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'))) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('dvs_pss_permit_expiry', __('DVS/PSS Permit Expiry'),['class'=>'form-label']) }}
            {{ Form::date('dvs_pss_permit_expiry', '', array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'))) }}
        </div>
<div class="form-group col-md-12">
            {{ Form::label('insurance_type', __('Insurance Type')) }}
            <div class="row">
                @foreach(['Motor Insurance'] as $index => $insurance)
                    <div class="col-md-6">
                        <div class="form-check">
                            {{ Form::checkbox('insurance_type[]', $insurance, null, ['class' => 'form-check-input', 'id' => 'insurance_'.$index]) }}
                            {{ Form::label('insurance_'.$index, $insurance, ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

         <div class="form-group col-md-12">
            {{ Form::label('insurance', __('Insurance'),['class'=>'form-label']) }}
            {{ Form::date('insurance', '', array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'))) }}
        </div>
                <div class="form-group col-md-12">
            {{ Form::label('date_of_inspection', __('Date Of Inspection'),['class'=>'form-label']) }}
            {{ Form::date('date_of_inspection', '', array('class' => 'form-control', 'id' => 'date_of_inspection')) }}
        </div>
       <div class="form-group col-md-12" id="pmi_interval_wrapper" style="display: none;">
            {{ Form::label('PMI_intervals', __('PMI Intervals (In Week)')) }}
            {{ Form::select('PMI_intervals', ['' => 'Select week', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10'], null, ['class' => 'form-control',  'id' => 'PMI_intervals']) }}
        </div>
         <div class="form-group col-md-12">
            {{ Form::label('PMI_due', __('PMI Due'),['class'=>'form-label']) }}
            {{ Form::text('PMI_due', '', array('class' => 'form-control', 'id' => 'PMI_due', 'readonly'=> 'readonly' )) }}
        </div>



        <div class="form-group col-md-12">
            {{ Form::label('odometer_reading', __('Odometer Reading'),['class'=>'form-label']) }}
            {{ Form::text('odometer_reading', '', array('class' => 'form-control' )) }}
        </div>
        <!-- <div class="form-group col-md-12">-->
        <!--    {{ Form::label('brake_test_due', __('Brake Test Due'),['class'=>'form-label']) }}-->
        <!--    {{ Form::date('brake_test_due', '', array('class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d'))) }}-->
        <!--</div>-->

      <!--zenish -->
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>
$(document).ready(function () {
    // Initially hide PMI Intervals
    $('#pmi_interval_wrapper').hide();

    function calculatePMIDue() {
        const inspectionDate = $('#date_of_inspection').val();
        const interval = $('#PMI_intervals').val();
        if (inspectionDate && interval) {
            const dueDate = new Date(inspectionDate);
            dueDate.setDate(dueDate.getDate() + (7 * parseInt(interval)));
            const day = String(dueDate.getDate()).padStart(2, '0');
            const month = String(dueDate.getMonth() + 1).padStart(2, '0');
            const year = dueDate.getFullYear();
            $('#PMI_due').val(`${month}-${day}-${year}`);
        } else {
            $('#PMI_due').val('');
        }
    }

    // When the Date of Inspection is selected or changed
    $('#date_of_inspection').on('change', function () {
        const dateSelected = $(this).val();
        if (dateSelected) {
            $('#pmi_interval_wrapper').show();
        } else {
            $('#pmi_interval_wrapper').hide();
            $('#PMI_intervals').val('');
            $('#PMI_due').val('');
        }
        calculatePMIDue();
    });

    // If PMI interval is selected or changed
    $('#PMI_intervals').on('change', function () {
        calculatePMIDue();
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
        // Event listener for companyName change
        $('select[name="companyName"]').change(function() {
            var companyId = $(this).val();  // Get selected company ID

            if(companyId) {
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
                            group_idSelect.append('<option value="'+ key +'">'+ value +'</option>');
                        });
                    }
                });
            } else {
                // If no company is selected, reset the group_id dropdown
                $('select[name="group_id"]').html('<option value="">{{ __("Select a company first") }}</option>');
            }
        });
         // Show loader on submit button click
        $('#driver-form').on('submit', function() {
            $('#submit-btn').prop('disabled', true); // Disable submit button
            $('#loader').show(); // Show loader
        });
    });

    $('select[name="companyName"]').change(function() {
        var companyId = $(this).val();  // Get selected company ID

        if(companyId) {
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
                        depot_idSelect.append('<option value="'+ key +'">'+ value +'</option>');
                    });
                }
            });
        } else {
            // If no company is selected, reset the group_id dropdown
            $('select[name="depot_id"]').html('<option value="">{{ __("Select a company first") }}</option>');
        }
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Show/hide archive options based on vehicle status
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
                $.each(data, function (i, item) {
                    $('#project_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                if (data == '') {
                    $('#project_id').empty();
                }
            }
        });
    }




    $(document).ready(function() {
        $('.device-dropdown').change(function() {
            if ($(this).val() === 'other') {
                $('.insurance_other').show();
            } else {
                $('.insurance_other').hide();
            }
        });
    });
</script>
