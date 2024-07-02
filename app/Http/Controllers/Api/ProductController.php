<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for Products"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="Get a list of products",
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort in descending order",
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A list of products"
     *     )
     *
     *
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query();

        if ($request->has('sort_by')) {
            $sortBy = $request->query('sort_by');
            $desc = $request->query('desc', false) == 'true' ? 'desc' : 'asc';
            $products->orderBy($sortBy, $desc);
        }

        $products = $products->paginate(
            $request->query('limit', 15),
            ['*'],
            'page',
            $request->query('page', 1)
        );

        $data = $this->getJsonResponseData(1, [
            'products' => $products,
        ]);

        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/product/create",
     *     tags={"Products"},
     *     summary="Create a new product",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"category_uuid", "title", "price", "description"},
     *
     *                 @OA\Property(property="category_uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="metadata", type="string", format="json")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_uuid' => 'required|string|exists:categories,uuid',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'metadata' => 'required|json',
        ]);

        if ($validator->fails()) {
            $data = $this->getJsonResponseData(0, $validator->errors()->toArray(), 'Validation error');

            return response()->json($data, 422);
        }
        $validated = $validator->validated();
        $validated['uuid'] = Str::uuid()->toString();
        $product = Product::create($validated);

        $data = $this->getJsonResponseData(1, $product->toArray());

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Get a product by UUID",
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
     *         description="Product details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function get($uuid): JsonResponse
    {
        $product = Product::where('uuid', $uuid)->first();

        if (! $product) {
            $data = $this->getJsonResponseData(0, [], 'Product not found');

            return response()->json($data, 404);
        }

        $data = $this->getJsonResponseData(1, $product->toArray());

        return response()->json($data, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Update a product by UUID",
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="category_uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="metadata", type="string", format="json")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Product not found"
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *     )
     *   )
     */
    public function update(Request $request, $uuid): JsonResponse
    {
        $product = Product::where('uuid', $uuid)->first();
        if (! $product) {
            $data = $this->getJsonResponseData(0, [], 'Product not found');

            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'category_uuid' => 'sometimes|required|string|exists:categories,uuid',
            'title' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'metadata' => 'sometimes|required|json',
        ]);

        if ($validator->fails()) {
            $data = $this->getJsonResponseData(0, $validator->errors()->toArray(), 'Validation error');

            return response()->json($data, 422);
        }

        $product->update($validator->validated());

        $data = $this->getJsonResponseData(1, $product->toArray());

        return response()->json($data, 200);
    }
}
