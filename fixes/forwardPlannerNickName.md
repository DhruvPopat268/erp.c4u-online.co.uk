# Forward Planner - Show Nick Name in Calendar Cell

**Date:** 27/10/2026

## Issue
In the Forward Planner calendar, each event cell was showing the vehicle registration number in brackets below the planner type (e.g. `(AB12 CDE)`).

## Change
Show `vehicle_nick_name` (the "Vehicle ID" field) in brackets instead of the registration number.
If a vehicle has no nick name set, an empty bracket `()` is shown.

## Files Modified

### `app/Http/Controllers/FleetController.php`
- Added `$vehicleNickName` variable that reads `vehicle_nick_name` from `vehicleDetails`, empty string if null.
- Added `'vehicle_nick_name' => $vehicleNickName` to the event data array returned for the calendar.

### `resources/views/fleet/index.blade.php`
- In `eventContent`, read `vehicle_nick_name` from `extendedProps`.
- Render `(vehicleNickName)` in the bracket — shows empty bracket `()` if no nick name set.

### `app/Exports/CalendarEventsExport.php`
- Heading `'Vehicle Registration Number'` changed to `'Vehicle ID'`.
- Map value changed from `$fleet->vehicle->registrationNumber` to `$fleet->vehicle->vehicle_nick_name ?? ''`.

### `resources/views/fleet/template.blade.php` (PDF export)
- Calendar table column header changed from `Reg No.` to `Vehicle ID`.
- `$regNo` value changed from `registrationNumber` to `vehicle_nick_name ?? ''`.
- Reminder summary table header changed from `Vehicle Reg` to `Vehicle ID`.
- Reminder summary table value changed from `registrationNumber` to `vehicle_nick_name ?? ''`.

## What Was NOT Changed
- Modal still shows the real registration number under "Vehicle Registration Number".
- Save/update event logic is unaffected.
- PDF export (template.blade.php) is unaffected.
- Filters, redirects, and history links are unaffected.
