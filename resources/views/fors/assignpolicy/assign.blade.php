<div class="modal-header">
    <h5 class="modal-title">{{ __('Assign Policy') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    {!! Form::open(['route' => ['forsGold.assignPolicy', $goldPolicy->id], 'method' => 'post']) !!}
        <div class="form-group">
            <label for="policy_name">{{ __('Policy Name') }} : {{ $goldPolicy->gold_policy_name }}</label>
        </div>
        <!-- Form Actions -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Assign Policy') }}</button>
        </div>

        <!-- Select All Checkbox -->
        <div class="form-group">
            <label for="driver_ids">{{ __('Select Drivers') }}</label>
            <br>
            <input class="form-check-input" type="checkbox" id="selectAllDrivers">
            <label class="form-check-label" for="selectAllDrivers">{{ __('Select All Drivers') }}</label>
        </div>

        <!-- Driver Selection Row -->
        <div class="row">
            @foreach($drivers as $driver)
                <div class="col-md-3 mb-2"> <!-- Adjust the column width as needed -->
                    <div class="form-check">
                        <input class="form-check-input driver-checkbox" type="checkbox" value="{{ $driver->id }}" id="driverCheck{{ $driver->id }}" name="driver_ids[]"
                            {{ in_array($driver->id, $assignedDriverIds) ? 'checked' : '' }}>
                        <label class="form-check-label" for="driverCheck{{ $driver->id }}">
                            {{ $driver->name }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>


    {!! Form::close() !!}
</div>

<style>
    .form-check {
        display: flex;
        align-items: center;
    }
    .form-check-input {
        margin-right: 0.5rem;
    }
</style>

<script>
    document.getElementById('selectAllDrivers').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.driver-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });
</script>
