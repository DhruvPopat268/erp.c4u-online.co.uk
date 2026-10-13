# Fix: Driver Licence Expiry Reminder — Active Filter Validation
**Date:** 13-10-2026  
**File:** `app/Http/Controllers/DriverController.php`

---

## Problem

All 3 reminder APIs were sending expiry reminder emails to **InActive** and **Archived** drivers. The company Active check was also either missing or implemented in a fragile way in some APIs.

---

## APIs Fixed

### 1. `GET /send-driver-reminders`
**Controller:** `DriverController@sendReminders`  
**Description:** Fetches drivers whose `driver_licence_expiry`, `cpc_validto`, or `tacho_card_valid_to` falls in 30, 60, or 90 days from today. Sends individual reminder emails to drivers and a summary email to their company.  
**Fix:** Replaced fragile `with()` + manual `continue` company check with a proper `whereHas('companyDetails')` filter. Added `where('driver_status', 'Active')` at the query level to exclude InActive and Archived drivers.

---

### 2. `GET /save-driver-reminders`
**Controller:** `DriverController@saveReminders`  
**Description:** Same expiry date logic as above but instead of sending emails immediately, saves matching drivers into the `driver_reminder_logs` table with status `Pending` for later processing.  
**Fix:** Added `where('driver_status', 'Active')` to the existing query which already had `whereHas` for active company. Wrapped the expiry conditions in an explicit `where(function())` group to ensure correct query chaining.

---

### 3. `GET /send-driver-pending-reminders`
**Controller:** `DriverController@sendPendingDriverReminders`  
**Description:** Reads all `Pending` or `Failed` records from `driver_reminder_logs` and sends the emails. Marks records as `Sent` on success or `Failed` on error.  
**Fix:** Added `driver_status !== 'Active'` check and a full company status check (`company_status !== 'Active'`) after loading the driver. Previously there was zero status validation in this function.

---

## Filter Rules (After Fix)

| Condition | Behaviour |
|---|---|
| Driver status = `Active`, Company status = `Active` | ✅ Reminder sent |
| Driver status = `InActive`, Company status = `Active` | ❌ Skipped |
| Driver status = `Archive`, Company status = `Active` | ❌ Skipped |
| Driver status = `Active`, Company status = `InActive` | ❌ Skipped |
| Driver not found or no email | ❌ Skipped |
