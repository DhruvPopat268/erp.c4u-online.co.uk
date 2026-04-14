{{ Form::open(['route' => 'fleet.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate','id' => 'fleet']) }}
<div class="modal-body">

    <div class="form-group">
        {{ Form::label('company_id', __('Company'), ['class' => 'form-label']) }}
        {{ Form::select('company_id', ['' => 'Select a Company'] + $contractTypes->map(function ($company) {
            return strtoupper($company);
        })->toArray(), null, ['class' => 'form-control', 'required' => 'required', 'id' => 'company_select']) }}
    </div>

    <div class="form-group mt-3">
        {{ Form::label('group_id', __('Group'), ['class' => 'form-label']) }}
        <div id="group_checkbox_container" class="row"></div>
    </div>

    <div class="form-group mt-3">
        {{ Form::label('vehicle_ids', __('Select Vehicles'), ['class' => 'form-label']) }}
        <div id="select_all_vehicles_container" style="display: none;">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="select_all_vehicles">
                <label class="form-check-label" for="select_all_vehicles">
                    {{ __('Select All Vehicles') }}
                </label>
            </div>
        </div>
        <div id="vehicle_checkbox_container" class="row"></div>
    </div>

    <div class="form-group">
        {{ Form::label('planner_type', __('Planner Type')) }}
        {{ Form::text('planner_type', null, ['class' => 'form-control', 'placeholder' => __('Enter Planner Type Name'), 'required']) }}
        @if ($errors->has('planner_type'))
        <span class="text-danger">{{ $errors->first('planner_type') }}</span>
    @endif
    </div>

    <div class="form-group">
        {{ Form::label('start_date', __('Start date')) }}
        {{ Form::date('start_date', '', ['class' => 'form-control', 'required']) }}
    </div>

    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('every', __('Every')) }}
            {{ Form::number('every', '', ['class' => 'form-control', 'min' => 1, 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('interval', __('Interval')) }}
            {{ Form::select('interval', ['Day' => __('Day'), 'Week' => __('Week'), 'Month' => __('Month')], null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Interval')]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary" id="submit-btn">
    
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
    $('#company_select').change(function() {
        let companyId = $(this).val();
        $('#group_checkbox_container').empty();
        $('#vehicle_checkbox_container').empty();
        $('#select_all_vehicles_container').hide();

        if (companyId) {
            $.ajax({
                url: '{{ route("vehicle.groups.byCompany") }}',
                method: 'GET',
                data: { company_id: companyId },
                success: function(response) {
                    $.each(response.groups, function(index, group) {
                        $('#group_checkbox_container').append(`
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input group-checkbox" type="checkbox" name="group_id[]" value="${index}" id="group_${index}">
                                    <label class="form-check-label" for="group_${index}">${group}</label>
                                </div>
                            </div>
                        `);
                    });

                    $('.group-checkbox').change(function() {
                        let groupIds = $('.group-checkbox:checked').map(function() {
                            return $(this).val();
                        }).get();

                        $('#vehicle_checkbox_container').empty();
                        $('#select_all_vehicles_container').hide();

                        if (groupIds.length > 0) {
                            $.ajax({
                                url: '{{ route("vehicle.byGroup") }}',
                                method: 'GET',
                                data: { company_id: companyId, group_id: groupIds },
                                success: function(response) {
                                    $.each(response.vehicles, function(index, vehicle) {
                                        $('#vehicle_checkbox_container').append(`
                                         <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input vehicle-checkbox" type="checkbox" name="vehicle_ids[]" value="${index}" id="vehicle_${index}">
                                                <label class="form-check-label" for="vehicle_${index}">${vehicle || 'Vehicle Name Not Found'}</label>
                                            </div>
                                            </div>
                                        `);
                                    });
                                    $('#select_all_vehicles_container').show();
                                }
                            });
                        }
                    });
                }
            });
        }
    });

    $('#select_all_vehicles').change(function() {
        $('.vehicle-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#vehicle_checkbox_container').on('change', '.vehicle-checkbox', function() {
        $('#select_all_vehicles').prop('checked', $('.vehicle-checkbox:checked').length === $('.vehicle-checkbox').length);
    });
    
    $('#fleet').on('submit', function() {
        $('#submit-btn').prop('disabled', true);
        $('#loader').show();
    });
});
</script>
