<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Services\JwtService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User API endpoint"
 * )
 *
 */
class UserController extends Controller
{
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/create",
     *     summary="Create User account",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"first_name", "last_name", "email", "password", "password_confirmation", "address", "phone_number"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Ok",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="first_name", type="string", example=""),
     *                 @OA\Property(property="last_name", type="string", example=""),
     *                 @OA\Property(property="email", type="string", example=""),
     *                 @OA\Property(property="address", type="string", example=""),
     *                 @OA\Property(property="phone_number", type="string", example=""),
     *                 @OA\Property(property="is_marketing", type="integer", example=0),
     *                 @OA\Property(property="avatar", type="string", example=""),
     *                 @OA\Property(property="updated_at", type="date-time"),
     *                 @OA\Property(property="created_at", type="date-time"),
     *                 @OA\Property(property="token", type="string", example="")
     *             ),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
    public function create(UserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated["password"] = Hash::make($validated["password"]);
            $validated["uuid"] = Str::uuid()->toString();
            $user = User::create($validated);
            $token = $this->jwtService->generateToken($user);
            $user["token"] = $token->toString();

            $data = $this->getJsonResponseData(1, $user->toArray());
            return response()->json($data, 200);

        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     summary="Login a user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                      type="object",
     *                      required={"email", "password"},
     *                      @OA\Property(property="email", type="string", format="email", description="The user's email address", example="" ),
     *                      @OA\Property(property="password", type="string", description="The user's password", example="" )
     *                  )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", description="JWT token")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/user/logout",
     *     summary="Logout user",
     *     description="Logout the authenticated user by invalidating their JWT token.",
     *     operationId="logoutUser",
     *     tags={"User"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     summary="Get logged-in user",
     *     description="Returns the data of the authenticated user",
     *     operationId="getUser",
     *     tags={"User"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved user data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *                 @OA\Property(property="is_marketing", type="boolean", example=true),
     *                 @OA\Property(property="avatar", type="string", example="avatar.png")
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/user",
     *     summary="Delete logged-in user",
     *     description="Deletes the authenticated user and their token",
     *     operationId="deleteUser",
     *     tags={"User"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted user",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/user/edit",
     *     summary="Edit logged-in user details",
     *     description="Edit the details of the authenticated user",
     *     operationId="editUser",
     *     tags={"User"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "address", "phone_number"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890"),
     *             @OA\Property(property="is_marketing", type="boolean", nullable=true, example=true),
     *             @OA\Property(property="avatar", type="string", nullable=true, example="avatar.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated user details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                 @OA\Property(property="is_marketing", type="boolean", example=true),
     *                 @OA\Property(property="avatar", type="string", example="avatar.png")
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
    public function edit(UserRequest $request): JsonResponse
    {
        try{
            $token = request()->bearerToken();
            $user = $this->jwtService->getUserFromToken($token);
            if (!$user) {
                $data = $this->getJsonResponseData(0, [], "Unauthorized");
                return response()->json($data, 401);
            }
            $user->update($request->validated());
            $data = $this->getJsonResponseData(1, $user->toArray());
            return response()->json($data, 200);

        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/forgot-password",
     *     summary="Forgot Password",
     *     description="Generates a password reset token for the user",
     *     operationId="forgotPassword",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset token generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reset_token", type="string", example="token_value")
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);
            if ($validator->fails()) {
                $data = $this->getJsonResponseData(
                    0, [],
                    "Failed Validation",
                    $validator->errors()->toArray()
                );
                return response()->json($data, 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $data = $this->getJsonResponseData(0, [], "User not found");
                return response()->json($data, 404);
            }

            $token = $this->jwtService->generateTokenForPasswordReset($user);
            $data = $this->getJsonResponseData(1, [
                "reset_token" => $token->toString(),
            ]);

            return response()->json($data, 200);

        }catch (Exception $error) {
            $data = $this->getJsonResponseData(0,[], $error->getMessage());
            return response()->json($data, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/reset-password",
     *     summary="Reset Password",
     *     description="Resets the password using a reset token",
     *     operationId="resetPasswordToken",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="reset_token_value"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="new_password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="new_password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password has been successfully updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=1),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Password has been successfully updated")
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
    public function resetPasswordToken(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8',
                "password_confirmation" => "required|same:password",
            ]);
            if ($validator->fails()) {
                $data = $this->getJsonResponseData(
                    0, [],
                    "Failed Validation",
                    $validator->errors()->toArray()
                );
                return response()->json($data, 422);
            }

            $token = $request->token;
            $parsedToken = $this->jwtService->parseToken($token);
            $userId = $parsedToken->claims()->get('user_uuid');

            $user = User::where('uuid', $userId)->where('email', $request->email)->first();
            if (!$user) {
                $data = $this->getJsonResponseData( 0, [], "User not found");
                return response()->json($data, 404);
            }
            $user->password = Hash::make($request->password);
            $user->save();

            $data = $this->getJsonResponseData(1, [
                "message" => "Password has been successfully updated",
            ]);

            return response()->json($data, 200);
        } catch (\Exception $error) {
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
