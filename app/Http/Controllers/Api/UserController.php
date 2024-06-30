<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Services\JwtService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function create(UserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);
            $validated['uuid'] = Str::uuid()->toString();
            $user = User::create($validated);
            $token = $this->jwtService->generateToken($user);
            $user['token'] = $token;

            return response()->json([
                "success" => 1,
                "data" => $user,
                "error" => null,
                "errors" => [],
                "extra" => []
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                "success" => 0,
                "data" => [],
                "error" => $error->getMessage(),
                "errors" => [],
                "extra" => []
            ], 500);
        }

    }

}
