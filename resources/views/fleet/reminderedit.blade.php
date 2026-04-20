{{ Form::model($reminder, ['route' => ['reminder.update', $reminder->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        @php
        // Get the 'next_reminder_date' value and convert it to a Carbon instance
        $reminderDate = \Carbon\Carbon::parse($reminder->next_reminder_date);

        // Query to find the latest 'next_reminder_date' with status 'Completed' for the same fleet_planner_id
        $latestCompletedReminder = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
            ->where('status', 'Completed')
            ->latest('next_reminder_date') // Order by the latest 'next_reminder_date'
            ->first();

        // If a 'Completed' reminder exists, set the minDate as that 'next_reminder_date'
        if ($latestCompletedReminder) {
            $minDate = \Carbon\Carbon::parse($latestCompletedReminder->next_reminder_date)->addDay()->format('Y-m-d');
        } else {
            // If no 'Completed' reminder exists, fall back to the current reminder's 'next_reminder_date' and subtract 1 day
            $minDate = null;
        }

          // Query to find the next 'next_reminder_date' with status 'Pending' for the same fleet_planner_id
    $nextPendingReminder = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
    ->where('status', 'Pending')
    ->where('next_reminder_date', '>', $reminderDate) // Get the next reminder after the current reminder
    ->oldest('next_reminder_date') // Order by the earliest 'next_reminder_date'
    ->first();

// If a 'Pending' reminder exists, set the maxDate as that 'next_reminder_date' and subtract 1 day
if ($nextPendingReminder) {
    $maxDate = \Carbon\Carbon::parse($nextPendingReminder->next_reminder_date)->subDay()->format('Y-m-d');
} else {
    // If no 'Pending' reminder exists, fall back to the last date of the month for the current reminder and subtract 1 day
    $maxDate = null;
}
@endphp




        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('next_reminder_date', __('Next Reminder Date')) }}
                {{ Form::date('next_reminder_date', $reminder->next_reminder_date, array('class' => 'form-control', 'required' => 'required', 'min' => $minDate, 'max' => $maxDate)) }}
            </div>
        </div>

        <!-- Comment Box -->
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('comment', __('Comment')) }}
                {{ Form::textarea('comment', $reminder->comment, ['class' => 'form-control', 'rows' => 4]) }}
            </div>
        </div>

        <!-- PMI Interval Dropdown -->
        @if(in_array($reminder->fleet->planner_type, ['PMI Due', 'Brake Test Due']))
        @php
            $currentEvery = $reminder->fleet->every;
            $options = [];
            for ($i = 1; $i <= 10; $i++) {
                $label = $i . ' Week' . ($i > 1 ? 's' : '');
                if ($i == $currentEvery) {
                    $label .= ' (Current)';
                }
                $options[$i] = $label;
            }
        @endphp
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('pmi_intervals', __('PMI Interval')) }}
                {{ Form::select('pmi_intervals', $options, $currentEvery, ['class' => 'form-control']) }}
            </div>
        </div>
        @endif

        <!-- Vehicle Status Dropdown -->
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('archive_reason', __('Vehicle Status'."*"), ['class' => 'form-label']) }}
                {{ Form::select('archive_reason', [
                    '' => 'Select Status',
                    'On time' => 'On time',
                    'Sold' => 'Sold',
                    'Scrapped' => 'Scrapped',
                    'Write off' => 'Write off',
                    'In repair/VOR' => 'In repair/VOR',
                    'Other' => 'Other'
                ], $reminder->archive_reason, ['class' => 'form-control', 'id' => 'archive_reason', 'required' => 'required']) }}
                <input type="text" id="archive_other_text" name="archive_other" class="form-control" placeholder="Please specify" style="display:none; margin-top: 10px;">
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{ Form::close() }}
<script>
        // If "Other" is selected in the archive reason dropdown, show text input
        var archiveReasonSelect = document.getElementById('archive_reason');
        var archiveOtherText = document.getElementById('archive_other_text');
        archiveReasonSelect.addEventListener('change', function () {
            if (this.value === 'Other') {
                archiveOtherText.style.display = 'block';
            } else {
                archiveOtherText.style.display = 'none';
            }
    });
</script>
