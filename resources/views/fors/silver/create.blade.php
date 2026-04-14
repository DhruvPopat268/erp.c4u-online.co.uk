{{ Form::open(array('url' => 'forsSilver')) }}
    <div class="modal-body">


        <div class="row">
            <div class="form-group">
                {{ Form::label('silver_policy_name', __('Silver Policy Name')) }}
                {{ Form::text('silver_policy_name', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
        </div>

    </div>
{{ Form::close() }}
