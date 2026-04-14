{{ Form::model($question, ['route' => ['question.edit.store', $question->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('name', __('Description')) }}
                {{ Form::text('name', $question->name, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('description', __('Explanation')) }}
                {{ Form::textarea('description', $question->description, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('question_type', __('Description Type')) }}<br>
                {{ Form::radio('question_type', 'Yes/No', $question->question_type == 'Yes/No') }} {{ __('Yes/No') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::radio('question_type', 'Yes/No/N-A', $question->question_type == 'Yes/No/N-A') }} {{ __('Yes/No/N-A') }}
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('select_reasonimage', __('Select Reason Image')) }}<br>
                {{ Form::radio('select_reasonimage', 'Yes', $question->select_reasonimage == 'Yes') }} {{ __('Yes') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::radio('select_reasonimage', 'No', $question->select_reasonimage == 'No') }} {{ __('No') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                {{ Form::radio('select_reasonimage', 'None', $question->select_reasonimage == 'None') }} {{ __('None') }}

            </div>
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('defect_options', __('Defect Options')) }}<br>
                <!--@php-->
                <!--    $defectOptions = json_decode($question->defect_options, true) ?? [];-->
                <!--    $options = ['Air Leak', 'Cracked', 'Broken', 'Damaged', 'Faulty', 'Leaking', 'Missing', 'Noisy', 'Puncture', 'Worn', 'Multiple Faults', 'Other'];-->
                <!--@endphp-->
@php
    // Check if defect_options is already an array or needs to be decoded
    $defectOptions = is_array($question->defect_options) 
        ? $question->defect_options 
        : json_decode($question->defect_options, true) ?? [];
    
    $options = ['Air Leak', 'Cracked', 'Broken', 'Damaged', 'Faulty', 'Leaking', 'Missing', 'Noisy', 'Puncture', 'Worn', 'Multiple Faults', 'Other'];
@endphp

                <div class="row">
                    @foreach($options as $index => $option)
                        @if($index % 4 == 0 && $index != 0)
                            </div><div class="row">
                        @endif
                        <div class="col-md-3 mb-2">
                            <label>
                                {{ Form::checkbox('defect_options[]', $option, in_array($option, $defectOptions)) }} {{ $option }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div id="otherOptionContainer" class="{{ in_array('Other', $defectOptions) ? '' : 'd-none' }}">
                    {{ Form::label('other_defect', __('Other Defect')) }}
{{ Form::text('other_defect', in_array('Other', $defectOptions) && !empty(array_diff($defectOptions, $options)) ? array_values(array_diff($defectOptions, $options))[0] : '', ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input[name="defect_options[]"]');
        const otherOptionContainer = document.getElementById('otherOptionContainer');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.value === 'Other') {
                    otherOptionContainer.classList.toggle('d-none', !this.checked);
                }
            });
        });
    });
</script>
