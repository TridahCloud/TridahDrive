<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base Controller
 * 
 * All controllers in this application extend this base controller.
 * Add common functionality here that should be available to all controllers.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
