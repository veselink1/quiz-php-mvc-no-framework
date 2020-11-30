<?php

class Response {
    public static function redirect($route) {
        self::setLocation($route);
        exit;
    }

    public static function setLocation($route) {
        header('Location: ' . ROUTER_BASE_URL . '/' . $route);
    }
}
