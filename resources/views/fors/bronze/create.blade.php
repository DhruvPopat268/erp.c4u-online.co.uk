{{ Form::open(array('url' => 'forsBronze')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('bronze_policy_name', __('Policy Name')) }}
                {{ Form::text('bronze_policy_name', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>


    </div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
