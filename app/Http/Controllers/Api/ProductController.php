<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
