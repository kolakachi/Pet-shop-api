<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\UserEditRequest;
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
     *     security={{"bearerAuth":{}}},
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
     *
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

    /**
     * @OA\Get(
     *     path="/api/v1/admin/logout",
     *     summary="Logout an admin account",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/admin/user-listing",
     *     summary="List all non-admin users with pagination and filters",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort in descending order",
     *         required=false,
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *    ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function userListing(): JsonResponse
    {
        $perPage = request()->input('limit', 10);
        $sortBy = request()->input('sort_by', 'created_at');
        $desc = request()->input('desc', 'true') === 'true' ? 'desc' : 'asc';

        $users = User::where('is_admin', false)
            ->orderBy($sortBy, $desc)
            ->paginate($perPage);

        $data = $this->getJsonResponseData(1, [
            'users' => $users,
        ]);

        return response()->json($data, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/user-edit/{uuid}",
     *     summary="Edit a non-admin user's account",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the user",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="is_marketing", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function editUser(UserEditRequest $request, $uuid): JsonResponse
    {
        try {
            $user = User::where('uuid', $uuid)->where('is_admin', false)->first();

            if (! $user) {
                $data = $this->getJsonResponseData(0, [], 'User not found');

                return response()->json($data, 404);
            }
            $user->update($request->validated());
            $data = $this->getJsonResponseData(1, $user->toArray());

            return response()->json($data, 200);

        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0, [], $error->getMessage());

            return response()->json($data, 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/user-delete/{uuid}",
     *     summary="Delete a non-admin user's account",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the user",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function deleteUser($uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->where('is_admin', false)->first();

        if (! $user) {
            $data = $this->getJsonResponseData(0, [], 'User not found');

            return response()->json($data, 404);
        }

        $user->delete();
        $data = $this->getJsonResponseData(1, [
            'message' => 'User deleted successfully',
        ]);

        return response()->json($data, 200);
    }
}
