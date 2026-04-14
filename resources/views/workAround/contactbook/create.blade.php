{{ Form::open(['url' => 'create-contactbook']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('name', __('Name')) }}
                {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('company_id', __('Company')) }}
                {{ Form::select('company_id', array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('mobile_no', __('Mobile No')) }}
                {{ Form::text('mobile_no', '', ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('address', __('Address')) }}
                {{ Form::textarea('address', '', ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('designation', __('Designation')) }}
                {{ Form::text('designation', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
