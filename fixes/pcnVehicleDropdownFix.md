# PCN - Vehicle Registration Number Dropdown Not Loading

**Date:** 04/11/2026

## Issue
On the Create PCN page, the Vehicle Registration Number dropdown was not showing any vehicles for certain Manager role users, even though vehicles were correctly assigned to their depot.

## Root Cause
In `PCNController@create`, the query to load the company for non-company/non-PTC-manager users had an extra condition:

```php
$companies = CompanyDetails::where('created_by', '=', $user->creatorId())
    ->where('id', '=', $user->companyname)
    ->where('company_status', 'Active')
    ->get();
```

The `created_by` condition checks that the company was created by the same person who created the logged-in user (`$user->creatorId()` returns `$user->created_by` for non-company/admin users).

This fails when:
- The **company** was created by User A (e.g. PTC Transport, id=5)
- But the **logged-in user** was created by User B (e.g. a PTC manager, id=104)

In this case `created_by` values don't match → query returns empty → the hidden `#company_id` dropdown has no value → the `loadVehicles()` JavaScript function exits early → vehicle dropdown stays empty.

This affects any Manager user whose account was created by a PTC manager rather than directly by the company owner.

## Fix
Commented out the `created_by` condition in `PCNController@create`. The query now only checks that the company ID matches the user's assigned company and that the company is Active — which is sufficient.

## Files Modified

### `app/Http/Controllers/PCNController.php`
- In the `create()` method, commented out `->where('created_by', '=', $user->creatorId())` in the `$companies` query for non-company/non-PTC-manager users.

## What Was NOT Changed
- Vehicle filtering logic (`getVehiclesByDepotGroup`) is unaffected.
- The fix only applies to the `create()` method of PCNController.
- Company/PTC manager role users are unaffected (they use a different code path).
- All other validations (depot filter, archive filter, company active check) remain intact.
