{{ Form::model($forsSilver, ['route' => ['forsSilver.update', $forsSilver->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('silver_policy_name', __('Silver Policy Name')) }}
                {{ Form::text('silver_policy_name', $forsSilver->silver_policy_name, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
