{{ Form::model($reminder, ['route' => ['reminder.update', $reminder->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        @php
        // Get the 'next_reminder_date' value and convert it to a Carbon instance
        $reminderDate = \Carbon\Carbon::parse($reminder->next_reminder_date);

        // Query to find the latest 'next_reminder_date' with status 'Completed'
        // across ALL fleet planners for the same vehicle + planner type
        // (not just the current fleet_planner_id, since new Fleet records are created on each PMI update)
        $vehicleId        = $reminder->fleet->vehicle_id;
        $fleetPlannerType = $reminder->fleet->planner_type;

        \Log::info('[reminderedit] Reminder ID: ' . $reminder->id);
        \Log::info('[reminderedit] fleet_planner_id: ' . $reminder->fleet_planner_id);
        \Log::info('[reminderedit] vehicle_id: ' . $vehicleId);
        \Log::info('[reminderedit] planner_type: ' . $fleetPlannerType);
        \Log::info('[reminderedit] current reminder date: ' . $reminder->next_reminder_date);

        $latestCompletedReminder = \App\Models\FleetPlannerReminder::whereHas('fleet', function ($q) use ($vehicleId, $fleetPlannerType) {
                $q->where('vehicle_id', $vehicleId)
                  ->where('planner_type', $fleetPlannerType);
            })
            ->where('status', 'Completed')
            ->latest('next_reminder_date')
            ->first();

        \Log::info('[reminderedit] latestCompletedReminder: ' . ($latestCompletedReminder ? $latestCompletedReminder->next_reminder_date . ' (ID: ' . $latestCompletedReminder->id . ')' : 'NULL'));

        // If a 'Completed' reminder exists, set the minDate as that 'next_reminder_date' + 1 day
        if ($latestCompletedReminder) {
            $minDate = \Carbon\Carbon::parse($latestCompletedReminder->next_reminder_date)->addDay()->format('Y-m-d');
        } else {
            // No completed reminder exists — no min restriction
            $minDate = null;
        }

        \Log::info('[reminderedit] minDate: ' . ($minDate ?? 'NULL'));

          // Query to find the next 'next_reminder_date' with status 'Pending' for the same fleet_planner_id
    $nextPendingReminder = \App\Models\FleetPlannerReminder::where('fleet_planner_id', $reminder->fleet_planner_id)
    ->where('status', 'Pending')
    ->where('next_reminder_date', '>', $reminderDate) // Get the next reminder after the current reminder
    ->oldest('next_reminder_date') // Order by the earliest 'next_reminder_date'
    ->first();

    \Log::info('[reminderedit] nextPendingReminder: ' . ($nextPendingReminder ? $nextPendingReminder->next_reminder_date . ' (ID: ' . $nextPendingReminder->id . ')' : 'NULL'));

// If a 'Pending' reminder exists, set the maxDate as that 'next_reminder_date' and subtract 1 day
if ($nextPendingReminder) {
    $maxDate = \Carbon\Carbon::parse($nextPendingReminder->next_reminder_date)->subDay()->format('Y-m-d');
} else {
    // If no 'Pending' reminder exists, fall back to the last date of the month for the current reminder and subtract 1 day
    $maxDate = null;
}

    \Log::info('[reminderedit] maxDate: ' . ($maxDate ?? 'NULL'));
    \Log::info('[reminderedit] ---- END ----');
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

        // Enforce min/max on the date picker — Chrome does not visually block out-of-range dates
        (function () {
            var dateInput = document.getElementById('next_reminder_date');
            if (!dateInput) return;

            var minDate = dateInput.getAttribute('min') || null;
            var maxDate = dateInput.getAttribute('max') || null;

            // Show visible hint under the picker
            var hint = document.createElement('small');
            hint.style.color = '#dc3545';
            hint.style.display = 'none';
            dateInput.parentNode.appendChild(hint);

            dateInput.addEventListener('change', function () {
                var val = this.value;
                if (!val) return;

                if (minDate && val < minDate) {
                    hint.textContent = 'Date cannot be before ' + formatDisplay(minDate) + ' (last completed date + 1 day)';
                    hint.style.display = 'block';
                    this.value = minDate;
                } else if (maxDate && val > maxDate) {
                    hint.textContent = 'Date cannot be after ' + formatDisplay(maxDate) + ' (day before next scheduled reminder)';
                    hint.style.display = 'block';
                    this.value = maxDate;
                } else {
                    hint.style.display = 'none';
                }
            });

            function formatDisplay(ymd) {
                var parts = ymd.split('-');
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
        })();
</script>
