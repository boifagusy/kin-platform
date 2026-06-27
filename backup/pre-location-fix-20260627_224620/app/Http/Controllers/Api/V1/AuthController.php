<?php

// ===== backend/app/Http/Controllers/Api/V1/AuthController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * WHY:
     * Accept a phone number, create the user if missing,
     * and begin the login flow.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^\+234\d{10}$/'
            ],
        ]);

        $phone = $validated['phone'];

        if (strlen($phone) !== 14) {
            return response()->json([
                'error' => 'Invalid phone number',
            ], 422);
        }

        $user = User::firstOrCreate(
            [
                'phone' => $phone
            ],
            [
                'name' => 'Kin User'
            ]
        );

        return response()->json([
            'message' => 'Code sent',
            'user_id' => $user->id,
        ], 200);
    }
}
