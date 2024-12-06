<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login user and return a token
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            if (!$token = auth('api')->attempt($credentials)) {
                return $this->errorResponse(
                    'Invalid credentials',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            return $this->respondWithToken($token);

        } catch (\Exception $e) {
            \Log::error('Authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                'Authentication failed',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Register a new user and return a token
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();
            
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? User::ROLE_SUPPLIER
            ]);

            $token = auth('api')->login($user);

            return $this->respondWithToken($token);

        } catch (\Exception $e) {

            return $this->errorResponse(
                'Registration failed',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Logout user (Invalidate the token)
     */
    public function logout()
    {
        try {
            auth('api')->logout();

            return $this->successResponse('Successfully logged out');

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Logout failed',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Refresh a token
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(auth('api')->refresh());

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Token refresh failed' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get the authenticated User
     */
    public function me()
    {
        try {
            return $this->successResponse(
                'User profile retrieved successfully',
                auth()->user()
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve user profile',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token)
    {
        return $this->successResponse('Authentication successful', [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refreshable_in' => config('jwt.refresh_ttl') * 60,
            'user' => auth()->user()
        ]);
    }
}
