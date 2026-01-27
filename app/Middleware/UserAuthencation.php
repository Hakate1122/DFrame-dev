<?php
namespace App\Middleware;

use DFrame\Application\Middleware;

/**
 * **User Authentication Middleware**
 * 
 * This middleware handles user authentication for incoming requests.
 */
class UserAuthencation extends Middleware
{
    public static function sign(): void
    {
        Middleware::register('needed', function () {
            // Implement your authentication logic here
            if (!isset($_SESSION['dframe_user'])) {
                http_response_code(401);
                return 'Unauthorized Access';
            }
            return null;
        });
    }
}
