{{ Form::model($profile, ['route' => ['profile.edit.store', $profile->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('name', __('Profile')) }}
                {{ Form::text('name', $profile->name, ['class' => 'form-control', 'required' => 'required']) }}
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
                {{ Form::label('description', __('Description')) }}
                {{ Form::textarea('description', $profile->description, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                {{ Form::label('mobile_app_enabled', __('Mobile App Enabled')) }}<br>
                {{ Form::radio('mobile_app_enabled', 'Yes', true) }} {{ __('Yes') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::radio('mobile_app_enabled', 'No') }} {{ __('No') }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
