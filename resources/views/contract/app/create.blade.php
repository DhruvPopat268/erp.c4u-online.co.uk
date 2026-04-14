{{ Form::open(array('url' => 'accesslevel')) }}
    <div class="modal-body">

        <!-- Company Name Dropdown -->
        <div class="form-group">
            {{ Form::label('company_id', __('Company Name'), ['class' => 'form-label']) }}
            {{ Form::select('company_id', ['' => __('Select a company')] + array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
        </div>

        <!-- Manager Access Checkbox -->
        <div class="form-group">
            {{ Form::label('manager_access', __('Manager APP Access'), ['class' => 'form-label']) }}
            <div class="row">
                <!-- Column 1 for Manager Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('manager_access[]', 'Driver') }} {{ __('Driver') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Vehicle') }} {{ __('Vehicle') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Walkaround') }} {{ __('Walkaround') }}</label><br>
                </div>
                <!-- Column 2 for Manager Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('manager_access[]', 'Operating Centre') }} {{ __('Operating Centre') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Training') }} {{ __('Training') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Forward Planner') }} {{ __('Forward Planner') }}</label><br>
                </div>
            </div>
        </div>

        <!-- Driver Access Checkbox -->
        <div class="form-group">
            {{ Form::label('driver_access', __('Driver APP Access'), ['class' => 'form-label']) }}
            <div class="row">
                <!-- Column 1 for Driver Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('driver_access[]', 'Walkaround') }} {{ __('Walkaround') }}</label><br>
                    <label>{{ Form::checkbox('driver_access[]', 'Vehicle') }} {{ __('Vehicle') }}</label><br>
                </div>
                <!-- Column 2 for Driver Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('driver_access[]', 'Contact') }} {{ __('Contact') }}</label><br>
                    <label>{{ Form::checkbox('driver_access[]', 'Handbook') }} {{ __('Handbook') }}</label><br>
                    <label>{{ Form::checkbox('driver_access[]', 'Training') }} {{ __('Training') }}</label><br>
                </div>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
