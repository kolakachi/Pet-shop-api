<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="File",
 *     description="File API endpoint"
 * )
 */
class FileController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/file/upload",
     *     summary="Upload a file",
     *     tags={"File"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="object", @OA\Property(property="uuid", type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
    public function upload(UploadFileRequest $request)
    {
        try {
            $file = $request->file('file');
            $path = $file->store('pet-shop');
            $uuid = Str::uuid()->toString();

            $file = File::create([
                'uuid' => $uuid,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
            ]);

            $data = $this->getJsonResponseData(1, $file->toArray());

            return response()->json($data, 200);
        } catch (Exception $error) {
            $data = $this->getJsonResponseData(0, [], $error->getMessage());

            return response()->json($data, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/file/{uuid}",
     *     summary="Download a file",
     *     tags={"File"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="File downloaded successfully",
     *
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string"))
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="integer", example=0),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="error", type="string", example=""),
     *             @OA\Property(property="errors", type="object", example={}),
     *             @OA\Property(property="extra", type="object", example={})
     *         )
     *     )
     * )
     */
    public function download($uuid)
    {
        $file = File::where('uuid', $uuid)->firstOrFail();

        return Storage::download($file->path, $file->name);
    }
}
