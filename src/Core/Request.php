<?php

namespace PAW\src\Core;

class Request{
    public function uri(){
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function method(){
        $method = $_SERVER['REQUEST_METHOD'];
        return ($method === 'HEAD') ? 'GET' : $method;
    }

    public function route(){
        return [
            $this->uri(),
            $this->method()
        ];
    }

    public function get($key){
        return $_POST[$key] ?? $_GET[$key] ?? null;
    }
}