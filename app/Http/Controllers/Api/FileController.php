<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use Exception;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="File",
 *     description="File API endpoint"
 * )
 */
class FileController extends Controller
{
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
}
