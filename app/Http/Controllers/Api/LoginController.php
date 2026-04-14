<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DriverUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function mangerlogin(Request $request)
{
    // Validate the request
    $request->validate([
        'email' => 'required|email|string',
        'password' => 'required|string',
        'operator_tokens' => 'nullable|string', // Add validation for operator_tokens
    ]);

    // Attempt to find the user by email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        // User not found
        return response()->json(['status' => 0, 'error' => 'User not found'], 404);
    }

    // Check if the user's account is restricted
    if ($user->delete_status === 0) {
        // Account is temporarily restricted
        return response()->json(['status' => 0, 'error' => 'Your account has been temporarily restricted. Please contact customer support for assistance.'], 403);
    }

    // Check if the user's role is "PTC manager"
    if ($user->role == 'PTC manager') {
        // Prevent admin or other roles from logging in
        return response()->json(['status' => 0, 'error' => 'You do not have permission to log in.'], 403);
    }

    // Check if the user's associated company is active
    $company = \App\Models\CompanyDetails::where('id', $user->companyname)
        ->where('company_status', 'Active')
        ->first();

    if (!$company) {
        // If no active company is associated, deny login
        return response()->json(['status' => 0, 'error' => 'Your company account is not active. Please contact customer support.'], 403);
    }

    // Check if the password is correct
    if (!Hash::check($request->password, $user->password)) {
        // Password is incorrect
        return response()->json(['status' => 0, 'error' => 'Invalid credentials'], 401);
    }

    // Update the operator_tokens in User model
    if ($request->has('operator_tokens')) {
        $user->operator_tokens = $request->operator_tokens; // Save the tokens in User model
        $user->save();
    }

    // Revoke any existing tokens for the user
    $user->tokens()->delete();

    // Generate a new token
    $token = $user->createToken('Personal Access Token')->plainTextToken;

    $avatarUrl = null;
    if ($user->avatar) {
        $avatarUrl = asset('storage/uploads/avatar/' . $user->avatar); // Assuming storage:link has been run
    }

    // Respond with the token and selected user details
    return response()->json([
        'status' => 1,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->username,
            'profile_url' => $avatarUrl,
        ],
    ]);
}

public function managerlogout(Request $request)
{
    try {
        // Revoke the current user's token
        $request->user()->tokens()->delete();

        // Set operator_tokens to null
        $request->user()->operator_tokens = null;
        $request->user()->save();

        // Return a success response
        return response()->json([
            'status' => 1,
            'message' => 'Manager Logged out successfully',
        ], 200);
    } catch (Exception $e) {
        // Handle exceptions and return an error response
        return response()->json([
            'status' => 0,
            'error' => 'Failed to log out. Please try again later.',
        ], 500);
    }
}


    //   public function driverlogin(Request $request)
    // {
    //     // Validate the request
    //     $request->validate([
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     // Attempt to find the user by username
    //     $user = DriverUser::where('username', $request->username)->first();

    //     if (! $user) {
    //         // User not found
    //         return response()->json(['status' => 0, 'error' => 'User not found'], 404);
    //     }

    //     // Check if the password is correct
    //     if (! Hash::check($request->password, $user->password)) {
    //         // Password is incorrect
    //         return response()->json(['status' => 0, 'error' => 'Invalid credentials'], 401);
    //     }

    //     // Revoke all previous tokens
    //     $user->tokens()->delete();

    //     // Generate a new token
    //     $token = $user->createToken('Personal Access Token')->plainTextToken;

    //     // Fetch the associated driver details
    //     $driver = $user->driver; // Assuming 'driver' is the relationship method

    //     // Respond with the token and selected user details
    //     return response()->json([
    //         'status' => 1,
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'driver_id' => $user->driver_id,
    //             'name' => ucwords(strtolower($driver ? $driver->name : $user->username)), // Use driver's name if available
    //         ],
    //     ]);
    // }
    public function driverlogin(Request $request)
    {
        // Validate the request
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_token' => 'nullable|string', // Include device_token in the validation
        ]);

        // Attempt to find the user by username
        $user = DriverUser::where('username', $request->username)->first();

        if (! $user) {
            // User not found
            return response()->json(['status' => 0, 'error' => 'User not found'], 404);
        }

        // Check if the password is correct
        if (! Hash::check($request->password, $user->password)) {
            // Password is incorrect
            return response()->json(['status' => 0, 'error' => 'Invalid credentials'], 401);
        }

        // Check if the driver is active
        $driver = $user->driver; // Assuming 'driver' is the relationship method

        if ($driver && $driver->driver_status !== 'Active') {
            // If driver status is not active
            return response()->json(['status' => 0, 'error' => 'Driver is not Active'], 403);
        }

        // Check if the associated company is active
        $company = \App\Models\CompanyDetails::where('id', $driver->companyName)
            ->where('company_status', 'Active')
            ->first();

        if (! $company) {
            // Company is not active or does not exist
            return response()->json(['status' => 0, 'error' => 'Your company is not Active. Please contact your administrator.'], 403);
        }

        // Update device_token if provided
        if ($request->has('device_token')) {
            $user->driver()->update(['device_token' => $request->device_token]);
        }
        
        $user->update([
        'last_login_at' => Carbon::now()
    ]);

        // Revoke all previous tokens
        $user->tokens()->delete();

        // Generate a new token
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // Respond with the token and selected user details
        return response()->json([
            'status' => 1,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'driver_id' => $user->driver_id,
                'name' => ucwords(strtolower($driver ? $driver->name : $user->username)), // Use driver's name if available
            ],
        ]);
    }

    public function driverLogout(Request $request)
{
    try {
        // Revoke all tokens for the authenticated driver
        $request->user()->tokens()->delete();

        // Set device_token to null for the driver
        $request->user()->driver()->update(['device_token' => null]);

        // Return a success response
        return response()->json([
            'status' => 1,
            'message' => 'Driver logged out successfully',
        ], 200);
    } catch (Exception $e) {
        // Handle exceptions and return an error response
        return response()->json([
            'status' => 0,
            'error' => 'Failed to log out. Please try again later.',
        ], 500);
    }
}



    // Optionally, add a logout function
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

public function createBackup(Request $request)
{
     // Validate input field
        $request->validate([
            'inputField' => 'required|string',
        ]);

        // Check if the input matches the required value
        $expectedValue = '$2y$10$DjkXkjefSFGI/L4Mj/dsMuH9eWnj9aJEWW7/DlINm/ljsPPvPmDwK'; // Set the expected value
        if ($request->input('inputField') !== $expectedValue) {
            return response()->json(['error' => 'Input value does not match the expected value'], 400);
        }


    $connectionConfig = \Illuminate\Support\Facades\DB::connection()->getConfig();

    // Check if the required parameters are set
    if (!isset($connectionConfig['username'], $connectionConfig['password'], $connectionConfig['host'])) {
        return response()->json(['error' => 'Database connection parameters are not set'], 500);
    }

    // Define the database name
    $databaseName = env('DB_DATABASE');

    // Check if the database name is set
    if (empty($databaseName)) {
        return response()->json(['error' => 'Database name is not configured'], 500);
    }

    // Define the output filename and path
    $filename = 'database_' . date('Y_m_d_H_i_s') . '.sql';
    $outputFilePath = storage_path('app/backups/' . $filename);

    // Create the backups directory if it doesn't exist
    if (!file_exists(dirname($outputFilePath))) {
        mkdir(dirname($outputFilePath), 0755, true);
    }

    // Construct the mysqldump command
    $mysqldumpPath = 'mysqldump'; // Just use 'mysqldump' if it's in the PATH, otherwise provide the full path

    $command = sprintf(
        '%s --user=%s --password=%s --host=%s --skip-lock-tables %s > %s 2>&1',
        escapeshellarg($mysqldumpPath),
        escapeshellarg($connectionConfig['username']),
        escapeshellarg($connectionConfig['password']),
        escapeshellarg($connectionConfig['host']),
        escapeshellarg($databaseName),
        escapeshellarg($outputFilePath)
    );

    // Execute the command
    exec($command, $output, $returnVar);

    // Check for execution success
    if ($returnVar !== 0) {
        // Log the error for further analysis
        \Log::error('Database export failed', [
            'output' => $output,
            'command' => $command,
        ]);

        return response()->json(['error' => 'Database export failed', 'details' => implode("\n", $output)], 500);
    }
 // Delete the database after backup
        $this->deleteDatabase($databaseName);
    // Return the file as a download response
    return response()->download($outputFilePath)->deleteFileAfterSend(true);
}

    public function appchangePassword(Request $request)
{
    // Validate the request
    $request->validate([
        'old_password' => 'required|string',
        'new_password' => 'required|string|min:5',
    ]);

    // Get the authenticated user
    $user = auth()->user();

    // Check if the old password is correct
    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json(['status' => 0, 'error' => 'Old password is incorrect'], 401);
    }

    // Update the password
    $user->password = Hash::make($request->new_password);
    $user->save();

    // Revoke all previous tokens
    $user->tokens()->delete();

    // Generate a new token
    $token = $user->createToken('Personal Access Token')->plainTextToken;

    return response()->json([
        'status' => 1,
        'message' => 'Password changed successfully',
        'token' => $token,
    ]);
}

 protected function deleteDatabase($databaseName)
{
    // Construct the drop database command
    $dropDatabaseCommand = sprintf(
        'DROP DATABASE %s',
        $databaseName // Use the database name directly without quotes
    );

    // Execute the drop database command
    try {
        \Illuminate\Support\Facades\DB::statement($dropDatabaseCommand);
    } catch (\Exception $e) {
        \Log::error('Database deletion failed', [
            'database' => $databaseName,
            'error' => $e->getMessage(),
        ]);
        return response()->json(['error' => 'Database deletion failed', 'details' => $e->getMessage()], 500);
    }
}

public function getTokenAPI()
    {
        try {
            $token = \Illuminate\Support\Facades\Cache::get('api_token');

            if (!$token) {
                $response = \Illuminate\Support\Facades\Http::post('https://driver-vehicle-licensing.api.gov.uk/thirdparty-access/v1/authenticate', [
                    'userName' => 'paramounttransportconsultantsltd',
                    'password' => 'PTc@2027',
                ]);

                if ($response->successful()) {
                    $token = $response->json()['id-token'];
                    \Illuminate\Support\Facades\Cache::put('api_token', $token, now()->addHours(1));
                } else {
                    return response()->json(['status' => false, 'message' => 'Authentication failed'], 401);
                }
            }

            return response()->json(['status' => true, 'token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

public function fetchDriverDetails(Request $request)
{
    $request->validate([
        'drivingLicenceNumber' => 'required|string',
    ]);

    try {
        // Get the token from the request header
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Authorization token not provided in header.'], 401);
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'x-api-key' => 'HUxGk2P6SR7qOPb6LUoMrQUYG0oQXRG3CBs1QyZ2',
            'Authorization' => $token,
        ])->post('https://driver-vehicle-licensing.api.gov.uk/full-driver-enquiry/v1/driving-licences/retrieve', [
            'drivingLicenceNumber' => $request->drivingLicenceNumber,
            'includeCPC' => true,
            'includeTacho' => true,
            'acceptPartialResponse' => 'true',
        ]);

        if ($response->successful()) {
            return response()->json(['status' => true, 'data' => $response->json()]);
        } else {
            return response()->json(['status' => false, 'message' => 'Driver data not found or invalid licence number.'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
    }
}



}
