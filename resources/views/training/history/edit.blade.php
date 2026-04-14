{{ Form::model($training, ['route' => ['traininghistory.update', $training->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">

            <div class="form-group mt-3">
                {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                {{ Form::date('from_date', \Carbon\Carbon::createFromFormat('d/m/Y', $training->from_date)->format('Y-m-d'), ['class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}
            </div>

            <div class="form-group mt-3">
                {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                {{ Form::date('to_date', \Carbon\Carbon::createFromFormat('d/m/Y', $training->to_date)->format('Y-m-d'), ['class' => 'form-control', 'min' => \Carbon\Carbon::now()->format('Y-m-d')]) }}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
