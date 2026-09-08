# PMI Interval Not Set Issue

## Summary
Vehicles that were created **before frontend PMI validation was added** ended up with `date_of_inspection` filled but `PMI_intervals = NULL` and `PMI_due = NULL` in the database. This caused the Edit Vehicle popup to lock the PMI interval field with a misleading "cannot be edited" warning, even though no fleet/reminder records existed yet.

---

## Root Cause

### 1. How vehicles got into this broken state
The `create.blade.php` popup has frontend JS validation that blocks form submission if `date_of_inspection` is filled but `PMI_intervals` is not selected. **However**, this validation was added after some vehicles were already created — so older vehicles slipped through with `date_of_inspection` set but `PMI_intervals = NULL` and `PMI_due = NULL`.

**Example vehicle:**
```
Table: vehicle_details
id: 1542 | registrationNumber: PN69CAA | date_of_inspection: 2026-01-02 | PMI_intervals: NULL | PMI_due: NULL
```

---

### 2. Why the Edit popup showed the warning and locked the field

**File:** `resources/views/contract/edit.blade.php`

The `$isEditable` condition for both `date_of_inspection` and `PMI_intervals` fields was:

```php
// BUGGY condition
$isEditable = is_null($contract->date_of_inspection)
    ? true
    : ($editFlags['PMI Due'] ?? false);
```

**How `$editFlags['PMI Due']` is built** (in `ContractController@edit`):
- It checks if a `Fleet` record exists for this vehicle with `planner_type = 'PMI Due'`
- Then checks if the latest **Completed** reminder date matches `PMI_due`
- Since `PMI_due = NULL` → `if ($contractValue)` is false → `$canEdit = false`
- So `$editFlags['PMI Due'] = false`

**Result for PN69CAA:**
- `date_of_inspection` is NOT null → goes to `$editFlags['PMI Due']`
- `$editFlags['PMI Due']` = false (because PMI_due is null, no fleet record exists)
- `$isEditable = false` → field locked + warning shown:
  > *"This value cannot be edited directly. To make changes, please update it in the Forward Planner (PMI Due)."*

This was wrong — the field should have been editable since `PMI_due` was null (no fleet/reminder records existed yet).

---

## Fix

**File:** `resources/views/contract/edit.blade.php`

Changed the `$isEditable` condition in **two places** (once for `date_of_inspection`, once for `PMI_intervals`):

```php
// BEFORE (buggy):
$isEditable = is_null($contract->date_of_inspection)
    ? true
    : ($editFlags['PMI Due'] ?? false);

// AFTER (fixed):
$isEditable = (is_null($contract->date_of_inspection) || is_null($contract->PMI_due))
    ? true
    : ($editFlags['PMI Due'] ?? false);
```

**Logic:** If either `date_of_inspection` OR `PMI_due` is null → always editable. Only lock when both exist AND the fleet planner completed-reminder condition is met.

---

## How the full fix works end-to-end

Once the condition is fixed, the user can:

1. **Open Edit Vehicle popup** → `PMI_intervals` dropdown is now unlocked (shows 1–10 weeks)
2. **Select PMI interval** (e.g. 6 weeks) → JS auto-calculates `PMI_due = date_of_inspection + (interval × 7 days)`
3. **Click Update** → `ContractController@update` runs this logic:

```php
if (
    !$isArchived &&
    $request->filled('PMI_due') &&
    $request->filled('date_of_inspection') &&
    $shouldCreateReminder   // true because PMI_due changed from null → new value
) {
    // Creates Fleet record: planner_type='PMI Due', every=PMI_intervals, interval='Week'
    $fleetPMI = Fleet::create([...]);
    $this->generateReminders($fleetPMI);  // inserts FleetPlannerReminder rows weekly from start to end_date

    // Creates Fleet record: planner_type='Brake Test Due', same config
    $fleetBrake = Fleet::create([...]);
    $this->generateReminders($fleetBrake);
}
```

`generateReminders()` inserts one `fleet_planner_reminders` row per interval cycle from `start_date` to `end_date` (1 year), each with `status = 'Pending'`.

---

## Files Changed

| File | Change |
|------|--------|
| `resources/views/contract/edit.blade.php` | Fixed `$isEditable` condition for `date_of_inspection` and `PMI_intervals` fields — added `|| is_null($contract->PMI_due)` to the condition |

---

## Related Files (for reference)

| File | Purpose |
|------|---------|
| `resources/views/contract/create.blade.php` | Frontend JS validation — blocks submit if date_of_inspection filled but PMI_intervals empty |
| `app/Http/Controllers/ContractController.php` | `store()` line ~864: creates fleet+reminders on vehicle creation; `update()` line ~3163: creates fleet+reminders on vehicle update; `edit()` line ~2730: builds `$editFlags` array |
| `app/Models/Fleet.php` | Fleet planner record model |
| `app/Models/FleetPlannerReminder.php` | Individual reminder rows model |
| `database/migrations/2024_11_19_053822_create_fleets_table.php` | fleets table schema |
| `database/migrations/2024_11_19_084820_create_fleet_planner_reminders_table.php` | fleet_planner_reminders table schema |

---

## How to identify affected vehicles

Run this query to find all vehicles with the same broken state (date_of_inspection set but PMI_intervals/PMI_due missing):

```sql
SELECT id, companyName, registrationNumber, date_of_inspection, PMI_intervals, PMI_due
FROM vehicle_details
WHERE date_of_inspection IS NOT NULL
  AND (PMI_intervals IS NULL OR PMI_due IS NULL);
```

For each result — open Edit Vehicle, select PMI interval, click Update. Fleet records and reminders will be auto-created.
