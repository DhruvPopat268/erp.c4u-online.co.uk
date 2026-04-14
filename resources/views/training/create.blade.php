{{ Form::open(array('url' => 'training', 'id' => 'training')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('training_type_id', __('Training Type'), ['class' => 'form-label']) }}
            {{ Form::select('training_type_id', $trainingTypes, null, ['class' => 'form-control', 'placeholder' => __('Select Training Type'), 'id' => 'training_type_select']) }}
        </div>

        <div class="form-group mt-3">
            {{ Form::label('training_course_id', __('Training Course'), ['class' => 'form-label']) }}
            {{ Form::select('training_course_id', [], null, ['class' => 'form-control', 'placeholder' => __('Select Training Course'), 'id' => 'training_course_select', 'disabled' => 'disabled']) }}
        </div>

<div class="form-group">
    {{ Form::label('companyName', __('Company'), ['class' => 'form-label']) }}
    {{ Form::select('companyName', ['' => 'Select a Company'] + $contractTypes->map(function ($company) {
        return strtoupper($company);
    })->toArray(), null, ['class' => 'form-control', 'required' => 'required', 'id' => 'company_select']) }}
</div>


        <div class="form-group mt-3">
            {{ Form::label('group_id', __('Group'), ['class' => 'form-label']) }}
            <div id="group_checkbox_container">
                <!-- Group checkboxes will be dynamically appended here -->
            </div>
        </div>

        <div class="form-group mt-3">
            {{ Form::label('driver_id', __('Select Drivers'), ['class' => 'form-label']) }}
            <div id="select_all_drivers_container" style="display: none;">
                <!-- Select All Checkbox -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select_all_drivers">
                    <label class="form-check-label" for="select_all_drivers">
                        {{ __('Select All Drivers') }}
                    </label>
                </div>
            </div>
            <div id="driver_checkbox_container">
                <!-- Driver checkboxes will be dynamically appended here -->
            </div>
        </div>

        <div class="form-group mt-3">
            {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
            {{ Form::date('from_date', null, ['class' => 'form-control','required' => 'required', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}
        </div>

        <div class="form-group mt-3">
            {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
            {{ Form::date('to_date', null, ['class' => 'form-control','required' => 'required', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}
        </div>

        <div class="form-group mt-3">
            {{ Form::label('from_time', __('From Time'), ['class' => 'form-label']) }}
            {{ Form::time('from_time', null, ['class' => 'form-control','required' => 'required']) }}
        </div>
        <div class="form-group mt-3">
            {{ Form::label('to_time', __('To Time'), ['class' => 'form-label']) }}
            {{ Form::time('to_time', null, ['class' => 'form-control','required' => 'required']) }}
        </div>

        <div class="form-group mt-3">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary" id="submit-btn">

    <!-- Loader HTML -->
    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
        <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}

<script>
$(document).ready(function() {
    $('#training_type_select').change(function() {
        let trainingTypeId = $(this).val();

        $('#training_course_select').empty().prop('disabled', true);
        if (trainingTypeId) {
            $.ajax({
                url: '{{ route('training.courses') }}',
                method: 'GET',
                data: { training_type_id: trainingTypeId },
                success: function(response) {
                    $('#training_course_select').append($('<option>', {
                        value: '',
                        text: '{{ __('Select Training Course') }}'
                    }));
                    $.each(response.courses, function(index, course) {
                        $('#training_course_select').append($('<option>', {
                            value: course.id,
                            text: `${course.name} (${course.duration} days)`
                        }));
                    });
                    $('#training_course_select').prop('disabled', false);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }
    });

    $('#company_select').change(function() {
        let companyId = $(this).val();

        // Clear previous checkboxes
        $('#group_checkbox_container').empty();
        $('#driver_checkbox_container').empty();
        $('#select_all_drivers_container').hide();

        if (companyId) {
            $.ajax({
                url: '{{ route('groups.byCompany') }}',
                method: 'GET',
                data: { company_id: companyId },
                success: function(response) {
                    $.each(response.groups, function(index, group) {
                        let checkbox = `
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input group-checkbox" type="checkbox" name="group_id[]" value="${index}" id="group_${index}">
                                    <label class="form-check-label" for="group_${index}">
                                        ${group}
                                    </label>
                                </div>
                            </div>
                        `;
                        $('#group_checkbox_container').append(checkbox);
                    });

                    // Attach change event to group checkboxes
                    $('.group-checkbox').change(function() {
                        let groupIds = $('.group-checkbox:checked').map(function() {
                            return $(this).val();
                        }).get();

                        $('#driver_checkbox_container').empty();
                        $('#select_all_drivers_container').hide();

                        if (groupIds.length > 0) {
                            $.ajax({
                                url: '{{ route('drivers.byGroup') }}',
                                method: 'GET',
                                data: { company_id: companyId, group_id: groupIds },
                                success: function(response) {
                                    $.each(response.drivers, function(index, driver) {
                                        let driverName = driver ? driver : 'Driver Name Not Found';
                                        let driverCheckbox = `
                                            <div class="form-check">
                                                <input class="form-check-input driver-checkbox" type="checkbox" name="driver_id[]" value="${index}" id="driver_${index}">
                                                <label class="form-check-label driver-name" for="driver_${index}">
                                                    ${driverName}
                                                </label>
                                            </div>
                                        `;
                                        $('#driver_checkbox_container').append(driverCheckbox);
                                    });
                                    // Show the "Select All Drivers" checkbox if drivers are loaded
                                    $('#select_all_drivers_container').show();
                                },
                                error: function(xhr) {
                                    console.error(xhr.responseText);
                                }
                            });
                        }
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }
    });

    // Select All Drivers functionality
    $('#select_all_drivers').change(function() {
        let isChecked = $(this).is(':checked');
        $('.driver-checkbox').prop('checked', isChecked);
    });

    // Update Select All checkbox state
    $('#driver_checkbox_container').on('change', '.driver-checkbox', function() {
        if (!$(this).is(':checked')) {
            $('#select_all_drivers').prop('checked', false);
        } else if ($('.driver-checkbox:checked').length === $('.driver-checkbox').length) {
            $('#select_all_drivers').prop('checked', true);
        }
    });

    $('#training').on('submit', function() {
        $('#submit-btn').prop('disabled', true);
        $('#loader').show();
    });
});

</script>
<style>
      #group_checkbox_container {
        display: flex;
        flex-wrap: wrap;
    }

    #group_checkbox_container .form-check {
        width: 30%; /* Adjust this percentage to control spacing */
        margin-bottom: 10px; /* Adds some space between rows */
    }

        #driver_checkbox_container {
            display: flex;
            flex-wrap: wrap;
        }

        #driver_checkbox_container .form-check {
            width: 50%; /* Adjust this percentage to control spacing */
            margin-bottom: 10px; /* Adds some space between rows */
        }
        /* Make driver names uppercase */
    .driver-name {
        text-transform: uppercase;
    }
</style>
