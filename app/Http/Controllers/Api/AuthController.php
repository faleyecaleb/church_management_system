<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. Try to authenticate as admin/staff (User) first
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        }

        // 2. Fallback: Try to authenticate as a church Member
        $member = Member::where('email', $request->email)->first();
        if ($member && Hash::check($request->password, $member->password)) {
            $token = $member->createToken('API Token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $member,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        if ($user instanceof \App\Models\Member) {
            $user->load(['departments.department', 'church']);
        }
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Change password for the authenticated user/member.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided current password does not match our records.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    }

    /**
     * Save the Expo push token for the authenticated member.
     */
    public function savePushToken(Request $request)
    {
        $request->validate([
            'expo_push_token' => 'required|string',
        ]);

        $user = $request->user();

        // Check if the user is a Member, since only members use the mobile app and have push notifications.
        if ($user instanceof \App\Models\Member) {
            $user->update([
                'expo_push_token' => $request->expo_push_token
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expo push token saved successfully.',
                'data' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Push tokens can only be saved for member accounts.'
        ], 400);
    }
}