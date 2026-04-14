{{ Form::open(['route' => ['selected.vehicle.update', implode(',', $idsArray)], 'method' => 'PUT']) }}
@foreach($idsArray as $id)
    <input type="hidden" name="ids[]" value="{{ $id }}">
@endforeach
<input type="hidden" name="companyName" value="{{ $selectedCompanyId }}">

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('vehicle_status', __('Vehicle Status'), ['class' => 'form-label']) }}
            {{ Form::select('vehicle_status', [
                'Owned' => 'Owned',
                'Rented' => 'Rented',
                'Leased' => 'Leased',
                'Contract Hire' => 'Contract Hire',
                'Depot Transfer' => 'Depot Transfer',
                'Archive' => 'Archive'
            ], null, ['class' => 'form-control', 'id' => 'vehicle_status', 'placeholder' => __('Select Status')]) }}
        </div>

        <div class="form-group col-md-12" id="archive_options" style="display: none;">
            {{ Form::label('archive_reason', __('Archive Reason'), ['class' => 'form-label']) }}
            {{ Form::select('archive_reason', [
                '' => 'Select Reason',
                'Sold' => 'Sold',
                'Scrapped' => 'Scrapped',
                'Write off' => 'Write off',
                'In repair/VOR' => 'In repair/VOR',
                'Other' => 'Other'
            ], null, ['class' => 'form-control', 'id' => 'archive_reason']) }}
        </div>

        <div class="form-group col-md-12" id="archive_other_text" style="display: none;">
            {{ Form::label('archive_other_text', __('Please specify'), ['class' => 'form-label']) }}
            {{ Form::text('archive_other_text', null, ['class' => 'form-control', 'placeholder' => 'Please specify']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('group_id', __('Vehicle Groups'), ['class' => 'form-label']) }}
            {{ Form::select('group_id', ['' => __('Select a company first')], null, ['class' => 'form-control', 'placeholder' => __('Select Group')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('depot_id', __('Depots name'), ['class' => 'form-label']) }}
            {{ Form::select('depot_id', ['' => __('Select a company first')], null, ['class' => 'form-control', 'placeholder' => __('Select Depots')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Form::close() }}



<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Archive status change logic
    $('#vehicle_status').change(function() {
        if ($(this).val() === 'Archive') {
            $('#archive_options').show();
        } else {
            $('#archive_options').hide();
            $('#archive_reason').val('');
            $('#archive_other_text').hide().val('');
        }
    });

    $('#archive_reason').change(function() {
        if ($(this).val() === 'Other') {
            $('#archive_other_text').show().focus();
        } else {
            $('#archive_other_text').hide().val('');
        }
    });


    var initialCompanyId = $('input[name="companyName"]').val();

if (initialCompanyId) {
    fetchGroups(initialCompanyId, null); // no old value
    fetchDepots(initialCompanyId, null); // no old value
}

    // If in future you add a visible select for companyName, use this:
    $(document).on('change', 'select[name="companyName"]', function() {
        var companyId = $(this).val();
            fetchGroups(companyId);
            fetchDepots(companyId);
        });

        function fetchGroups(companyId, selectedGroupId = null) {
            if (companyId) {
                $.ajax({
                    url: "{{ url('get-vehicle-groups-by-company') }}/" + companyId,
                    type: 'GET',
                    success: function(data) {
                        var group_idSelect = $('select[name="group_id"]');
                    group_idSelect.empty().append('<option value="">{{ __("Select Group") }}</option>');
                        $.each(data, function(key, value) {
                            var isSelected = (key == selectedGroupId) ? 'selected' : '';
                            group_idSelect.append('<option value="'+ key +'" '+ isSelected +'>'+ value +'</option>');
                        });
                    }
                });
            } else {
                $('select[name="group_id"]').html('<option value="">{{ __("Select a company first") }}</option>');
            }
        }

    function fetchDepots(companyId, selectedDepotId = null) {
            if (companyId) {
                $.ajax({
                    url: "{{ url('get-depots-by-company') }}/" + companyId,
                    type: 'GET',
                    success: function(data) {
                        var depot_idSelect = $('select[name="depot_id"]');
                    depot_idSelect.empty().append('<option value="">{{ __("Select Depot") }}</option>');
                        $.each(data, function(key, value) {
                        var isSelected = (key == selectedDepotId) ? 'selected' : '';
                            depot_idSelect.append('<option value="'+ key +'" '+ isSelected +'>'+ value +'</option>');
                        });
                    }
                });
            } else {
                $('select[name="depot_id"]').html('<option value="">{{ __("Select a company first") }}</option>');
            }
        }

});
</script>
