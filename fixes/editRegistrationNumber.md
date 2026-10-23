# Edit Registration Number Feature

**Date:** 23/10/2026  
**Feature:** Allow editing of vehicle registration number from the Manage Vehicles edit form (both Vehicle and Trailer).

---

## Files Updated

### 1. Frontend — Edit Forms

| File | Change |
|------|--------|
| `resources/views/contract/edit.blade.php` | Added `registrationNumber` text input field below the Vehicle ID field |
| `resources/views/contract/trailer/edit.blade.php` | Added `registrationNumber` text input field below the Vehicle ID field |

### 2. Backend — Controller

| File | Change |
|------|--------|
| `app/Http/Controllers/ContractController.php` — `update()` | Added `registrationNumber` validation rule, company-scoped duplicate check, and multi-table update logic |
| `app/Http/Controllers/ContractController.php` — `trailerupdate()` | Same as above — identical registration update logic applied to trailer edit flow |

---

## Tables That Store Registration Number (Must Be Updated on Change)

| # | Table | Column | Update Method | Notes |
|---|-------|--------|---------------|-------|
| 1 | `vehicle_details` | `registrationNumber` | `$contract->registrationNumber = $newReg` | Primary table — updated first |
| 2 | `vehicles` | `registrations` | `$contract->vehicle->registrations = $newReg` | Only if vehicle relationship exists |
| 3 | `vehicle_reminder_logs` | `registration_number` | `WHERE vehicle_id = $contract->id` | Snapshot stored when reminders are sent |
| 4 | `vehicle_overdue_reminder_logs` | `registration_number` | `WHERE vehicle_id = $contract->id` | Snapshot stored when overdue reminders are sent |
| 5 | `pcns` | `vehicle_registration_number` | `WHERE vehicle_id = $contract->id OR vehicle_registration_number = $oldReg` | Uses both FK and old reg string as fallback (vehicle_id is nullable in pcns) |
| 6 | `deleted_vehicle_logs` | `vehicle_registrationnumber` | ❌ Do NOT update | Audit trail — must preserve original value at time of deletion |

---

## Duplicate Check Logic

Registration number uniqueness is **company-scoped** (not globally unique).

```php
$duplicate = vehicleDetails::where('companyName', $request->companyName)
    ->where('registrationNumber', $newReg)
    ->where('id', '!=', $contract->id)  // exclude current record
    ->exists();
```

---

## Notes

- New registration is always stored **uppercased and trimmed** (`strtoupper(trim(...))`)
- Multi-table update only runs **if the registration actually changed** (`$regChanged` flag)
- The `deleted_vehicle_logs` table is intentionally skipped — it is a historical audit log
- PCN update uses both `vehicle_id` and old reg string to cover records where `vehicle_id` was not set at creation time
