{{ Form::model($planner_file, ['route' => ['planner.document.name.update', $planner_file], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class'=>'needs-validation', 'novalidate']) }}
<div class="modal-body">
    {{-- start for ai module--}}
   
    {{-- end for ai module--}}

    <div class="row">
        <div class="col-sm-6 col-md-12">
            <div class="form-group">
                {{ Form::label('image_name', __('File Name (without extension)'), ['class' => 'form-label']) }}
                {{ Form::text('image_name', pathinfo($planner_file->file_path, PATHINFO_FILENAME), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
