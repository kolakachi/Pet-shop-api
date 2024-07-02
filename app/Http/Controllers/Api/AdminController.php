<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use App\Services\JwtService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin API endpoint"
 * )
 */
class AdminController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/create",
     *     summary="Create a new admin account",
     *     tags={"Admin"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"first_name", "last_name", "email", "password", "password_confirmation", "address", "phone_number"},
     *
     *                     @OA\Property(property="first_name", type="string", description="The user's first name", example="" ),
     *                     @OA\Property(property="last_name", type="string", description="The user's last name", example="" ),
     *                     @OA\Property(property="email", type="string", format="email", description="The user's email address", example="" ),
     *                     @OA\Property(property="password", type="string", description="The user's password", example="" ),
     *                     @OA\Property(property="password_confirmation", type="string", description="Password confirmation", example="" ),
     *                     @OA\Property(property="address", type="string", description="The user's address", example="" ),
     *                     @OA\Property(property="phone_number", type="string", description="The user's phone number", example="" ),
     *                     @OA\Property(property="is_marketing", type="boolean", description="Marketing consent", nullable=true, example="" ),
     *                     @OA\Property(property="avatar", type="string", description="The user's avatar URL", nullable=true, example="" )
     *                 )
     *             )
     *         }
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Admin created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function create(AdminCreateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);
            $validated['uuid'] = Str::uuid()->toString();
            $validated['is_admin'] = true;

            $admin = User::create($validated);

            $data = $this->getJsonResponseData(1, $admin->toArray());

            return response()->json($data, 200);

        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0, [], $error->getMessage());

            return response()->json($data, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     summary="Login an admin account",
     *     tags={"Admin"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *
     *            @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"email","password"},
     *
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="password", type="string", format="password")
     *                 )
     *            )
     *         }
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if ($user && Hash::check($credentials['password'], $user->password) && $user->is_admin) {
                $token = $this->jwtService->generateToken($user);

                $data = $this->getJsonResponseData(1, [
                    'token' => $token->toString(),
                ]);

                return response()->json($data, 200);
            }
            $data = $this->getJsonResponseData(0, [], 'Unauthorized');

            return response()->json($data, 401);
        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0, [], $error->getMessage());

            return response()->json($data, 500);
        }
    }

    public function logout(): JsonResponse
    {
        $token = request()->bearerToken();
        $parsedToken = $this->jwtService->parseToken($token);
        $this->jwtService->deleteToken($parsedToken);

        $data = $this->getJsonResponseData(1, [
            'message' => 'Logged out successfully',
        ]);

        return response()->json($data, 200);
    }
}
