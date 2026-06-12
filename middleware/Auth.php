<?php
class Auth {

    public static function validate($expectedToken) {
        $token = self::getTokenFromHeaders();

        if (!$token || !hash_equals($expectedToken, $token)) {
            Response::error('Token de API inválido o ausente', 401);
            exit;
        }
    }

    private static function getTokenFromHeaders() {
        // getallheaders() funciona en Apache
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value) {
                if (strtolower($key) === 'x-api-token') {
                    return trim($value);
                }
            }
        }

        // Fallback por si getallheaders() no está disponible
        if (isset($_SERVER['HTTP_X_API_TOKEN'])) {
            return trim($_SERVER['HTTP_X_API_TOKEN']);
        }

        return null;
    }
}