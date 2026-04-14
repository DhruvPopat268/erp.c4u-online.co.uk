{{ Form::open(array('url' => 'depot')) }}
    <div class="modal-body">

<div class="form-group">
    {{ Form::label('companyName', __('Company Name'), ['class' => 'form-label']) }}
    {{ Form::select('companyName', array_map('strtoupper', collect($contractTypes->toArray())->sort()->toArray()), null, ['class' => 'form-control', 'data-toggle="select"', 'required' => 'required']) }}
</div>



        <div class="row">
            <div class="form-group">
                {{ Form::label('name', __('Depot Name')) }}
                {{ Form::text('name', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('licence_number', __('Licence Number')) }}
                {{ Form::text('licence_number', '', array('class' => 'form-control')) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('traffic_area', __('Traffic Area')) }}
                {{ Form::select('traffic_area', ['London and the South East of England' => 'London and the South East of England', 'East of England' => 'East of England','West Midlands' => 'West Midlands','West of England' => 'West of England','North East of England' => 'North East of England', 'North West of England' => 'North West of England','Scotland' => 'Scotland','Wales' => 'Wales','Northern Ireland' => 'Northern Ireland','N/A' => 'N/A'], null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('continuation_date', __('Depot O-license date')) }}
                {{ Form::date('continuation_date', '', array('class' => 'form-control')) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('transport_manager_name', __('Transport Manager Name')) }}
                {{ Form::text('transport_manager_name', '', array('class' => 'form-control')) }}
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                {{ Form::label('operating_centre', __('Operating Centre')) }}
                {{ Form::textarea('operating_centre', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('vehicles', __('Vehicles')) }}
                {{ Form::number('vehicles', '', array('class' => 'form-control')) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('trailers', __('Trailers')) }}
                {{ Form::number('trailers', '', array('class' => 'form-control' )) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('status', __('Status')) }}
                {{ Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
