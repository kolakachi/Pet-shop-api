<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *
 *     @OA\Info(
 *         title="Pet Shop API - Swagger Documentation",
 *         version="1.0.0",
 *         description="This API has been created with the goal to pass the coding skills test a job position at Buckhill"
 *     ),
 *
 *     @OA\Components(
 *
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT"
 *         )
 *     )
 * )
 */
abstract class Controller
{
    protected function getJsonResponseData(
        int $success, array $data = [],
        string $error = '', array $errors = [], array $extra = []): array
    {
        return [
            'success' => $success,
            'data' => $data,
            'error' => $error,
            'errors' => $errors,
            'extra' => $extra,
        ];
    }
}
