{{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">


        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('companyname', __('Company Name'), ['class' => 'form-label']) }}
                {{ Form::select('companyname', array_map('strtoupper', $companyName->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
                @error('companyname')
                <small class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
 @if(\Auth::user()->type != 'super admin')
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('depot_id', __('Depot Name'), ['class' => 'form-label']) }}
                <div id="depot-checkboxes">
                    <p class="text-muted">{{ __('Select a company first') }}</p>
                </div>
                @error('depot_id')
<small class="text-danger">{{ $message }}</small>
@enderror

            </div>
        </div>
 @endif
    </div>
@if(\Auth::user()->type != 'super admin')
<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('vehicle_group_id', __('Vehicle Group'), ['class' => 'form-label']) }}
            <div id="vehicle-group-checkboxes">
                <p class="text-muted">Select a company first</p>
            </div>
            @error('vehicle_group_id')
<small class="text-danger">{{ $message }}</small>
@enderror

        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('driver_group_id', __('Driver Group'), ['class' => 'form-label']) }}
            <div id="driver-group-checkboxes">
                <p class="text-muted">Select a company first</p>
            </div>
            @error('driver_group_id')
<small class="text-danger">{{ $message }}</small>
@enderror

        </div>
    </div>
</div>
@endif



    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('username', __('Name'), ['class' => 'form-label']) }}
                {{Form::text('username',null,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
                @error('username')
                <small class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>

        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email')))}}
                @error('email')
                <small class="invalid-email" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        @if(\Auth::user()->type != 'super admin')
        <div class="form-group col-md-12">
            {{ Form::label('role', __('User Role'),['class'=>'form-label']) }}
            {!! Form::select('role', $roles, $user->roles,array('class' => 'form-control select','required'=>'required')) !!}
            @error('role')
            <small class="invalid-role" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
        @endif
        @if(!$customFields->isEmpty())
        <div class="col-md-6">
            <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                @include('customFields.formBuilder')
            </div>
        </div>
        @endif
    </div>

</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

{{ Form::close() }}

<script>
$(document).ready(function() {

    let initialCompanyId = $('select[name="companyname"]').val();

    let selectedDepots = @json($user->depot_id ?? []);
    let selectedVehicleGroups = @json($user->vehicle_group_id ?? []);
    let selectedDriverGroups = @json($user->driver_group_id ?? []);

    if (initialCompanyId) {
        fetchAll(initialCompanyId);
    }

    $(document).on('changed.bs.select change', 'select[name="companyname"]', function () {
        let companyId = $(this).val();
        fetchAll(companyId);
    });

    function fetchAll(companyId) {
        fetchCheckboxes("{{ route('depots.by.company', ':id') }}", '#depot-checkboxes', 'depot_id[]', selectedDepots, companyId);
        fetchCheckboxes("{{ route('vehicle.groups.by.company', ':id') }}", '#vehicle-group-checkboxes', 'vehicle_group_id[]', selectedVehicleGroups, companyId);
        fetchCheckboxes("{{ route('driver.groups.by.company', ':id') }}", '#driver-group-checkboxes', 'driver_group_id[]', selectedDriverGroups, companyId);
    }

    function fetchCheckboxes(url, container, name, selected, companyId) {
        $.get(url.replace(':id', companyId), function(data) {

            let html = '';
            $.each(data, function(key, value) {
                let checked = selected.includes(key.toString()) ? 'checked' : '';
                html += `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="${name}" value="${key}" ${checked}>
                        <label class="form-check-label">${value.toUpperCase()}</label>
                    </div>
                `;
            });

            $(container).html(html || '<p class="text-muted">No data found</p>');
        });
    }
});
</script>
<script>
$('form').on('submit', function(e) {

    let depotChecked = $('input[name="depot_id[]"]:checked').length > 0;
    let vehicleGroupChecked = $('input[name="vehicle_group_id[]"]:checked').length > 0;
    let driverGroupChecked = $('input[name="driver_group_id[]"]:checked').length > 0;

    $('.validation-error').remove();

    if (!depotChecked) {
        $('#depot-checkboxes').after('<small class="text-danger validation-error">Depot is required</small>');
        e.preventDefault();
    }

    if (!vehicleGroupChecked) {
        $('#vehicle-group-checkboxes').after('<small class="text-danger validation-error">Vehicle group is required</small>');
        e.preventDefault();
    }

    if (!driverGroupChecked) {
        $('#driver-group-checkboxes').after('<small class="text-danger validation-error">Driver group is required</small>');
        e.preventDefault();
    }
});
</script>
