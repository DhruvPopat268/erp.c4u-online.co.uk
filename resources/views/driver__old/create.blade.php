{{ Form::open(array('url' => 'driver')) }}
    <div class="modal-body">
        <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('companyName', __('Company Name'), ['class' => 'form-label']) }}
            {{ Form::select('companyName', $contractTypes, null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
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
                {{ Form::label('ni_number', __('NI Number')) }}
                {{ Form::text('ni_number', '', ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('contact_no', __('Contact Number')) }}
                {{ Form::text('contact_no', '', ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('contact_email', __('Contact Email')) }}
                {{ Form::email('contact_email', '', ['class' => 'form-control']) }}
            </div>
         
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Submit') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
