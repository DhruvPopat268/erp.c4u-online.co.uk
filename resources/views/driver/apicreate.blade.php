{{ Form::open(array('url' => 'driver')) }}
    <div class="modal-body">
        <div class="row">
<div class="form-group col-md-6">
    {{ Form::label('companyName', __('Company Name'), ['class' => 'form-label']) }}
    {{ Form::select('companyName', ['' => __('Select a company')] + array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
</div>

        <div class="form-group col-md-6">
             {{ Form::label('driver_status', __('Driver Status'), ['class' => 'form-label']) }}
                {{ Form::select('driver_status', ['Active' => 'Active','InActive' => 'InActive', 'Archive' => 'Archive'], null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('drivingLicenceNumber', __('Driver Licence No')) }}
                {{ Form::text('drivingLicenceNumber', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('ni_number', __('Driver Number')) }}
                {{ Form::text('ni_number', '', ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('contact_no', __('Contact Number')) }}
                {{ Form::text('contact_no', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('contact_email', __('Contact Email')) }}
                {{ Form::email('contact_email', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('group_id', __('Driver Groups'), ['class' => 'form-label']) }}
                {{ Form::select('group_id', ['' => __('Select a company first')], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Submit') }}" class="btn btn-primary" id="submit-btn">

        <!-- Loader HTML -->
        <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
            <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px; ">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

    </div>
{{ Form::close() }}
<script>

    $(document).ready(function() {
        // Event listener for companyName change
        $('select[name="companyName"]').change(function() {
            var companyId = $(this).val();  // Get selected company ID

            if(companyId) {
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
</script>

