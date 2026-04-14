{{ Form::model($group, ['route' => ['vehicle.group.update', $group->id], 'method' => 'PUT']) }}
    <div class="modal-body">
            <div class="form-group">
                {{ Form::label('company_id', __('Company Name'),['class'=>'form-label']) }}
                {{ Form::select('company_id', array_map('strtoupper', $companies->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
            </div>

            <div class="form-group" style="margin-top: 7px;">
                {{ Form::label('name', __('Name')) }}
                {{ Form::text('name', $group->name, array('class' => 'form-control')) }}
            </div>

    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>

{{ Form::close() }}

