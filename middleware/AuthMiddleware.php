<?php
/**
 * Authentication Middleware
 */

class AuthMiddleware {

    // Evaluate authentication state and redirect appropriately
    public static function handle($route) {
        $publicRoutes = ['/', '/login'];
        $isLoggedIn = Session::has('user_id');

        if (in_array($route, $publicRoutes)) {
            if ($isLoggedIn) {
                Response::redirect('/dashboard');
            }
            return;
        }

        if (!$isLoggedIn) {
            Response::redirect('/login');
        }

        // Intercept authorization via RoleMiddleware
        RoleMiddleware::check($route);
    }
}
