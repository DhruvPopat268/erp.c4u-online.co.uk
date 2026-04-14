{{ Form::open(array('route' => ['training.course.store', $trainingType->id], 'method' => 'POST')) }}
<div class="modal-body">

    <div class="row">
 <div class="form-group">
                {{Form::label('name',__('Name'),['class'=>'form-label'])}}
                {{Form::text('name',null,array('class'=>'form-control'))}}
            </div>
        <div class="form-group">
            {{Form::label('duration',__('Course Duration (in Days)'),['class'=>'form-label'])}}
            {{Form::number('duration',null,array('class'=>'form-control'))}}
        </div>


    </div>


</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{Form::close()}}
