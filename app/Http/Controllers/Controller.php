<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
    * @OA\Info(
    *      version="1.0.0",
    *      title="Worldwideadverts API Documentation",
    *      description="Worldwideadverts API Lists Documentation",
    *      @OA\Contact(
    *          email="bangphe90@gmail.com"
    *      ),
    *      @OA\License(
    *          name="Apache 2.0",
    *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
    *      )
    * )
    *
    * @OA\Server(
    *      url=L5_SWAGGER_CONST_HOST,
    *      description="API Server"
    * )
    *
    * @OA\SecurityScheme(
    *      securityScheme="bearerAuth",
    *      in="header",
    *      name="Authorization",
    *      type="http",
    *      scheme="Bearer",
    *      bearerFormat="JWT",
    * ),
*/
    
class Controller extends BaseController
{    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
