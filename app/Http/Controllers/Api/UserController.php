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
            $validated["password"] = Hash::make($validated["password"]);
            $validated["uuid"] = Str::uuid()->toString();
            $user = User::create($validated);
            $token = $this->jwtService->generateToken($user);
            $user["token"] = $token;

            $data = $this->getJsonResponseData(1, $user->toArray());
            return response()->json($data, 200);

        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }

    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only("email", "password");
            $user = User::where("email", $credentials["email"])->first();

            if ($user && Hash::check($credentials["password"], $user->password)) {
                $token = $this->jwtService->generateToken($user);

                $data = $this->getJsonResponseData(1, [
                    "token" => $token->toString(),
                ]);
                return response()->json($data, 200);
            }

            $data = $this->getJsonResponseData(0, [], "Unauthorized");
            return response()->json($data, 401);

        }catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }

    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                $data = $this->getJsonResponseData(0, [], "Unauthorized");
                return response()->json($data, 401);
            }

            $parsedToken = $this->jwtService->parseToken($token);
            $this->jwtService->deleteToken($parsedToken);

            $data = $this->getJsonResponseData(1);
            return response()->json($data, 200);
        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }
    }

    public function getUser(): JsonResponse
    {
        try{
            $token = request()->bearerToken();
            $user = $this->jwtService->getUserFromToken($token);
            if (!$user) {
                $data = $this->getJsonResponseData(0, [], "Unauthorized");
                return response()->json($data, 401);
            }

            $data = $this->getJsonResponseData(1, $user->toArray());
            return response()->json($data, 200);

        }catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }
    }

    public function deleteUser(): JsonResponse
    {
        try{
            $token = request()->bearerToken();
            $user = $this->jwtService->getUserFromToken($token);
            if (!$user) {
                $data = $this->getJsonResponseData(0, [], "Unauthorized");
                return response()->json($data, 401);
            }

            $parsedToken = $this->jwtService->parseToken($token);
            $this->jwtService->deleteToken($parsedToken);

            $user->delete();
            $data = $this->getJsonResponseData(1);
            return response()->json($data, 200);
        }catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }

    }

    protected function getJsonResponseData(
        int $success, array $data = [],
        string $error = "", array $errors = [], array $extra = []): array
    {
        return [
            "success" => $success,
            "data" => $data,
            "error" => $error,
            "errors" => $errors,
            "extra" => $extra
        ];
    }

}
