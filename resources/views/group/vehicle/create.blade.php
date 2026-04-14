{{ Form::open(array('url' => 'vehicle/group/store')) }}
    <div class="modal-body">
        <div class="form-group">
            {{ Form::label('company_id', __('Company Name'), ['class' => 'form-label']) }}
            {{ Form::select('company_id', array_map('strtoupper', $group->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
        </div>

            <div class="form-group">
                {{ Form::label('name', __('Name')) }}
                {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>

    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Submit') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
