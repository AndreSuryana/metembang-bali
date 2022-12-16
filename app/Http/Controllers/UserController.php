<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Show data of current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fetchUser(Request $request)
    {
        try {
            $user = $request->user();

            return ResponseFormatter::success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'occupation' => $user->occupation,
                'address' => $user->address,
                'photo_url' => $user->photo_path ? env('APP_URL') . 'storage/' . $user->photo_path : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]);
        } catch(Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }

    /**
     * Change password of current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'password_old' => 'required',
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password'
            ]);

            // Get current user
            $id = Auth::user()->id;
            $user = User::find($id);
            
            // Check password input matches
            if (!Hash::check($request->password_old, $user->password)) {
                return ResponseFormatter::error('Password does not match');
            } else {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);

                return ResponseFormatter::success(true, 'Password successfully changed');
            }
        } catch(Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }

    /**
     * Update data of current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUser(Request $request)
    {
        try {
            // Get current user
            $id = Auth::user()->id;
            $user = User::find($id);

            // Validate input
            $request->validate([
                'name' => 'required|string|min:3|max:50',
                'email' => [
                    'required',
                    'string',
                    'email',
                    Rule::unique(User::class)->ignore($id)
                ],
                'phone' => 'required|string|max:15',
                'occupation' => 'nullable|string',
                'address' => 'required|string'
            ],
            [
                'name.required' => 'Name cannot be empty',
                'email.required' => 'Email cannot be empty',
                'email.unique' => 'Email has been registered',
                'password.confirmed' => 'Confirm password does not match'
            ]);

            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'occupation' => $request->occupation,
                'address' => $request->address
            ]);

            // Get updated user
            $updatedUser = User::find($id);

            return ResponseFormatter::success($updatedUser);
        } catch(Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }

    /**
     * Upload profile picture for current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadPhoto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|max:2048'
            ]);
    
            if ($validator->fails()) {
                return ResponseFormatter::error('Image file not found', 400);
            } 
    
            if ($request->file('photo')) {
                // Save photo path to current user
                $id = Auth::user()->id;
                $user = User::find($id);
                $user->update([
                    'photo_path' => $this->storeFile($request->photo, 'user/photo')
                ]);
    
                return ResponseFormatter::success(true);
            } else {
                return ResponseFormatter::error('Image file not found', 404);
            }
        } catch(Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }
}
