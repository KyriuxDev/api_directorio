<?php
class Response {

    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success($data, $statusCode = 200) {
        self::json(array_merge(['ok' => true], $data), $statusCode);
    }

    public static function error($message, $statusCode = 400) {
        self::json(['ok' => false, 'error' => $message], $statusCode);
    }
}