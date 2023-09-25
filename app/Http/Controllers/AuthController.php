<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $authRegisterRequest): JsonResponse
    {
        $validatedData = $authRegisterRequest->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        $userData = User::create($validatedData);

        $userData->token = $userData->createToken('API_TOKEN')->plainTextToken;

        $userData->message = "User Created Successfully";

        $userData->status = true;

        return (new AuthResource($userData))->response()->setStatusCode(201);
    }

    public function login(AuthLoginRequest $authLoginRequest): AuthResource
    {
        $credentials = $authLoginRequest->validated();

        if (Auth::attempt($credentials)) {
            $user = User::where('email', $credentials['email'])->first();
            $user->token = $user->createToken('API_TOKEN')->plainTextToken;
            $user->status = true;
            $user->message = 'Logged In Successfully';

            return new AuthResource($user);
        }

        return throw new HttpResponseException(response([
            "errors" => [
                "message" => [
                    "username or password wrong"
                ]
            ]
        ], 401));
    }
}
