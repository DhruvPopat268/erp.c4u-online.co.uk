{{ Form::open(array('url' => 'forsGold')) }}
    <div class="modal-body">


        <div class="row">
            <div class="form-group">
                {{ Form::label('gold_policy_name', __('Gold Policy Name')) }}
                {{ Form::text('gold_policy_name', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
        </div>

    </div>
{{ Form::close() }}
