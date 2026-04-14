{{ Form::model($reminder, ['route' => ['other.reminder.update', $reminder->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        @php
        use App\Models\Fleet;
        use App\Models\FleetPlannerReminder;
        use Carbon\Carbon;

        $reminderDate = Carbon::parse($reminder->next_reminder_date);

        // Get Fleet's start_date
        $fleet = Fleet::find($reminder->fleet_planner_id);
        $fleetStartDate = $fleet ? Carbon::parse($fleet->start_date) : null;

        // Latest completed reminder for min date
        $latestCompletedReminder = FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
            ->where('status', 'Completed')
            ->latest('next_reminder_date')
            ->first();

        $minDate = $latestCompletedReminder
            ? Carbon::parse($latestCompletedReminder->next_reminder_date)->addDay()->format('Y-m-d')
            : null;

        // Next pending reminder for max date
        $nextPendingReminder = FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
            ->where('status', 'Pending')
            ->where('next_reminder_date', '>', $reminderDate)
            ->oldest('next_reminder_date')
            ->first();

        $maxDate = $nextPendingReminder
            ? Carbon::parse($nextPendingReminder->next_reminder_date)->subDay()->format('Y-m-d')
            : null;

        // ✅ Special case: start_date == reminder date → no min limit
        if ($fleetStartDate && $fleetStartDate->equalTo($reminderDate)) {
            $minDate = null;
        }
@endphp




        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('next_reminder_date', __('Next Reminder Date')) }}
                {{ Form::date('next_reminder_date', $reminder->next_reminder_date, [
                    'class' => 'form-control',
                    'required' => 'required',
                    'min' => $minDate,
                    'max' => $maxDate
                ]) }}
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
