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
                if (Session::get('force_password_change') === true) {
                    Response::redirect('/change-password');
                }
                Response::redirect('/dashboard');
            }
            return;
        }

        if (!$isLoggedIn) {
            Response::redirect('/login');
        }

        // Force password change check
        if (Session::get('force_password_change') === true) {
            if ($route !== '/change-password' && $route !== '/change-password/save' && $route !== '/logout') {
                Response::redirect('/change-password');
            }
        }

        // Intercept authorization via RoleMiddleware
        RoleMiddleware::check($route);
    }
}
