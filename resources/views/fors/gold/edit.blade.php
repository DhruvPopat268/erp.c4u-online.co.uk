{{ Form::model($forsGold, ['route' => ['forsGold.update', $forsGold->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('gold_policy_name', __('Gold Policy Name')) }}
                {{ Form::text('gold_policy_name', $forsGold->gold_policy_name, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
