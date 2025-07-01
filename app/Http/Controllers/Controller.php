<?php

namespace App\Http\Controllers;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Auth-Lend",
 *         @OA\License(name="MIT"),
 *         @OA\Contact(
 *             email="jadiael1@gmail.com"
 *         )
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *             scheme="Bearer",
 *         ),
 *         @OA\Attachable
 *     )
 * )
 */
abstract class Controller
{
    //
}
