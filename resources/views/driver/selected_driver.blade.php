{{ Form::open(['route' => ['selected.driver.update', implode(',', $idsArray)], 'method' => 'PUT']) }}
@foreach($idsArray as $id)
    <input type="hidden" name="ids[]" value="{{ $id }}">
@endforeach
<input type="hidden" name="companyName" value="{{ $selectedCompanyId }}">

    <div class="modal-body">

        <div class="row">
          <div class="form-group col-md-6">
    {{ Form::label('driver_status', __('Driver Status')) }}
    {{ Form::select('driver_status', ['Active' => 'Active', 'InActive' => 'InActive', 'Archive' => 'Archive'], null, ['class' => 'form-control', 'placeholder' => __('Select Driver Status')]) }}
</div>

<div class="form-group col-md-6">
    {{ Form::label('automation', __('Automated Licence Check')) }}
    {{ Form::select('automation', ['Yes' => 'Yes', 'No' => 'No'], null, ['class' => 'form-control', 'placeholder' => __('Select Option')]) }}
</div>

        </div>

        <div class="row">
      <div class="form-group col-md-6">
    {{ Form::label('group_id', __('Driver Groups'), ['class' => 'form-label']) }}
    {{ Form::select('group_id', [], null, ['class' => 'form-control', 'placeholder' => __('Select Group')]) }}
</div>

<div class="form-group col-md-6">
    {{ Form::label('depot_id', __('Depot Name'), ['class' => 'form-label']) }}
    {{ Form::select('depot_id', [], null, ['class' => 'form-control', 'placeholder' => __('Select Depot')]) }}
</div>

     </div>
    
<div class="form-group">
    {{ Form::label('depot_access_status', __('Depot Change Allowed')) }}
    {{ Form::select('depot_access_status', ['Yes' => 'Yes', 'No' => 'No'], null, ['class' => 'form-control', 'placeholder' => __('Select Option')]) }}
</div>
</div>



          
    
</div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>

{{ Form::close() }}


<script>
    $(document).ready(function() {
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
                    url: "{{ url('get-groups-by-company') }}/" + companyId,
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
