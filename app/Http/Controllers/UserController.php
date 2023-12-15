<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if (User::where('username', $data['username'])->count() == 1) {
                return $this->sendError([
                    'username' => [
                        'Username already registered',
                    ],
                ], 400);
            }

            $user = new User($data);
            $user->password = Hash::make($data['password']);
            $user->save();
            $token = $user->createToken(Str::uuid()->toString())->plainTextToken;

            return $this->sendResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'type' => 'Bearer',
            ], 201);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = User::where('username', $data['username'])->first();
            if (!$user || !Auth::attempt($data) || !Hash::check($data['password'], $user->password)) {
                return $this->sendError([
                    'message' => [
                        'Username or password is incorrect',
                    ],
                ], 401);
            }

            $token = $user->createToken(Str::uuid()->toString())->plainTextToken;

            return $this->sendResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'type' => 'Bearer',
            ]);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function get(): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = new UserResource($user);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = $request->user();

            if (isset($data['name'])) {
                $user->name = $data['name'];
            }

            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
            $result = new UserResource($user);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();

            return $this->sendResponse();
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }
}
