<?php
/**
 * Helper to structure JSON and redirect responses
 */

class Response {

    // Return JSON response and terminate request execution
    public static function json($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Redirect to base path
    public static function redirect($path) {
        header("Location: " . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }
}
