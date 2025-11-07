<?php
namespace App\Middleware;

use DFrame\Application\Middleware;

/**
 * #### UserAuthencation Middleware
 * 
 * This middleware handles user authentication for incoming requests.
 */
class UserAuthencation extends Middleware
{
    public static function registerSelf(): void
    {
        Middleware::register('admin', function ($context) {
            // Implement your authentication logic here
            if (!isset($_SESSION['admin'])) {
                http_response_code(401);
                flash("error","Khu vực này yêu cầu đăng nhập!");
                return redirect()->route('admin.login');
            }
            return null;
        });

        Middleware::register('reader', function ($context) {
            if (isset($_SESSION['reader'])) {
                http_response_code(403);
                echo "Access denied for users!";
                return false;
            }
            return null;
        });

        Middleware::register('author', function ($context) {
            if (!isset($_SESSION['author'])) {
                http_response_code(403);
                flash("error","Khu vực này yêu cầu đăng nhập!");
                return redirect()->route('author.login');
            }
            return null;
        });

        Middleware::register('driveclone_user', function ($context) {
            if (!isset($_SESSION['driveclone_user'])) {
                http_response_code(403);
                flash("error","Khu vực này yêu cầu đăng nhập!");
                return redirect()->route('driveclone.login');
            }
            return null;
        });
    }
}
