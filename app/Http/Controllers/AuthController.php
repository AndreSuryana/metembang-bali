<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register new user account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {   
        try {
            // Validate request data with Validator
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:50',
                    'email' => 'required|string|email|unique:users',
                    'password' => 'required|min:6'
                ],
                [
                    'name.required' => 'Name cannot be empty',
                    'email.required' => 'Email cannot be empty',
                    'email.unique' => 'Email has been registered'
                ]
            );

            // Check validation status
            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors()->first(),
                    409
                );
            } else {
                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password)
                ]);

                $user = User::where('email', $request->email)->first();
                
                $token = $user->createToken('authToken')->plainTextToken;

                return ResponseFormatter::success([
                    'access_token' => $token,
                    'user' => $user
                ]);
            }
        } catch (Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }

    /**
     * Login to the registered account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            // Validate request data with Validator
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            // Credentials check
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Email atau password salah');
            }

            // User validation
            $user = User::where('email', $request->email)->firstOrFail();

            // Generate token
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'user' => $user
            ]);
        } catch (Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }

    /**
     * Logout from current account session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ResponseFormatter::success(true, 'Logout berhasil');
        } catch(Exception $exception) {
            return ResponseFormatter::error($exception->getMessage());
        }
    }
}
