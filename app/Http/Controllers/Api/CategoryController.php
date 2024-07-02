<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints for Categories"
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="Get a list of categories",
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
     *         description="A list of categories",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="UnAuthorized"
     *     )
     * )
     */
    public function index()
    {
        $query = Category::query();

        if (request()->has('sort_by')) {
            $sortBy = request()->get('sort_by');
            $desc = request()->get('desc', 'false') === 'true';
            $query->orderBy($sortBy, $desc ? 'desc' : 'asc');
        }

        $limit = request()->get('limit', 10);
        $categories = $query->paginate($limit);

        $data = $this->getJsonResponseData(1, [
            'categories' => $categories,
        ]);

        return response()->json($data, 200);
    }
}
