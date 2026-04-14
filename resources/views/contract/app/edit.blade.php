{{ Form::model($accesslevel, ['route' => ['accesslevel.update', $accesslevel->id], 'method' => 'PUT']) }}
    <div class="modal-body">

        <!-- Company Name Dropdown -->
        <div class="form-group">
            {{ Form::label('company_id', __('Company Name'), ['class' => 'form-label']) }}
            {{ Form::select('company_id', $contractTypes->toArray(), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
        </div>

        <!-- Manager Access Checkboxes -->
        <div class="form-group">
            {{ Form::label('manager_access', __('Manager App Access'), ['class' => 'form-label']) }}
            <div class="row">
                <!-- Column 1 for Manager Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('manager_access[]', 'Driver', in_array('Driver', $accesslevel->manager_access ?? [])) }} {{ __('Driver') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Vehicle', in_array('Vehicle', $accesslevel->manager_access ?? [])) }} {{ __('Vehicle') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Walkaround', in_array('Walkaround', $accesslevel->manager_access ?? [])) }} {{ __('Walkaround') }}</label><br>
                </div>
                <!-- Column 2 for Manager Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('manager_access[]', 'Operating Centre', in_array('Operating Centre', $accesslevel->manager_access ?? [])) }} {{ __('Operating Centre') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Training', in_array('Training', $accesslevel->manager_access ?? [])) }} {{ __('Training') }}</label><br>
                    <label>{{ Form::checkbox('manager_access[]', 'Forward Planner', in_array('Forward Planner', $accesslevel->manager_access ?? [])) }} {{ __('Forward Planner') }}</label><br>
                </div>
            </div>
        </div>


        <div class="form-group">
            {{ Form::label('driver_access', __('Driver App Access'), ['class' => 'form-label']) }}
            <div class="row">
                <!-- Column 1 for Driver Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('driver_access[]', 'Walkaround', in_array('Walkaround', $accesslevel->driver_access ?? [])) }} {{ __('Walkaround') }}</label><br>
                <label>{{ Form::checkbox('driver_access[]', 'Vehicle', in_array('Vehicle', $accesslevel->driver_access ?? [])) }} {{ __('Vehicle') }}</label><br>
                </div>
                <!-- Column 2 for Driver Access -->
                <div class="col-md-6">
                    <label>{{ Form::checkbox('driver_access[]', 'Contact', in_array('Contact', $accesslevel->driver_access ?? [])) }} {{ __('Contact') }}</label><br>
                <label>{{ Form::checkbox('driver_access[]', 'Handbook', in_array('Handbook', $accesslevel->driver_access ?? [])) }} {{ __('Handbook') }}</label><br>
                <label>{{ Form::checkbox('driver_access[]', 'Training', in_array('Training', $accesslevel->driver_access ?? [])) }} {{ __('Training') }}</label><br>
                </div>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
