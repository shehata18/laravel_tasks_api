<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Task API",
 *     version="1.0.0",
 *     description="API documentation for Task Management",
 * )
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 * @OA\Tag(
 *      name="Tasks",
 *      description="API Endpoints for managing tasks"
 *  )
 */

class SwaggerController extends Controller
{
    // This controller is used for Swagger annotations only
}
