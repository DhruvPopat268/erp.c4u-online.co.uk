{{ Form::open(['url' => 'create-question']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('name', __('Description')) }}
                {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('description', __('Explanation')) }}
                {{ Form::textarea('description', '', ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('question_type', __('Description Type')) }}<br>
                {{ Form::radio('question_type', 'Yes/No', true) }} {{ __('Yes/No') }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::radio('question_type', 'Yes/No/N-A') }} {{ __('Yes/No/N-A') }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('select_reasonimage', __('Reason/Image Upload')) }}<br>
                {{ Form::radio('select_reasonimage', 'Yes', true) }} {{ __('Yes') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::radio('select_reasonimage', 'No') }} {{ __('No') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::radio('select_reasonimage', 'None') }} {{ __('None') }}
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                {{ Form::label('defect_options', __('Defect Options')) }}<br>

                <div class="row">
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Air Leak') }} {{ __('Air Leak') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Cracked') }} {{ __('Cracked') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Broken') }} {{ __('Broken') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Damaged') }} {{ __('Damaged') }}</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Faulty') }} {{ __('Faulty') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Leaking') }} {{ __('Leaking') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Missing') }} {{ __('Missing') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Noisy') }} {{ __('Noisy') }}</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Puncture') }} {{ __('Puncture') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Worn') }} {{ __('Worn') }}</label>
                    </div>
                    <div class="col-md-4">
                        <label>{{ Form::checkbox('defect_options[]', 'Multiple Faults') }} {{ __('Multiple Faults') }}</label>
                    </div>
                    <div class="col-md-3">
                        <label>{{ Form::checkbox('defect_options[]', 'Other', false, ['id' => 'other-checkbox']) }} {{ __('Other') }}</label>
                    </div>
                </div>

                <!-- This text box will be shown when 'Other' is selected -->
                <div class="row" id="other-textbox-row" style="display: none;">
                    <div class="col-md-12">
    {{ Form::text('other_defect', 'other', ['class' => 'form-control', 'placeholder' => 'Please specify', 'id' => 'other-textbox']) }}
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        $('#other-checkbox').change(function() {
            if($(this).is(':checked')) {
                $('#other-textbox-row').show();
            } else {
                $('#other-textbox-row').hide();
                $('#other-textbox').val(''); // Clear the textbox if "Other" is unchecked
            }
        });
    });
</script>

