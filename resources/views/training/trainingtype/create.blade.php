{{Form::open(array('url'=>'trainingType/create'))}}
<div class="modal-body">

    <div class="row">
            <div class="form-group">
                {{Form::label('name',__('Name'),['class'=>'form-label'])}}
                {{Form::text('name',null,array('class'=>'form-control'))}}
            </div>
            <div class="form-group">
                {{ Form::label('company_id', __('Company Name'."*"), ['class' => 'form-label']) }}
                {{ Form::select('company_id', ['' => __('Select a company')] + array_map('strtoupper', $contractTypes->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
    
            </div>
    </div>


</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{Form::close()}}
