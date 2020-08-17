<?php


namespace Rest;


class Request
{
    private $server = [];
    private $request = [];

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->request = $_REQUEST;
    }

    public function method(){
        return $this->server['REQUEST_METHOD'];
    }
    public function uri(){
        return $this->server['DOCUMENT_URI'];
    }
    public function get(string $paramName) {
        if(!empty($_GET[$paramName]))
            return $_GET[$paramName];
        return false;
    }
    public function post(string $paramName){
        if(!empty($_POST[$paramName]))
            return $_POST[$paramName];
        else {//достаем параметры из тела запроса
            $body = json_decode(file_get_contents('php://input'), true);
            if(!empty($body[$paramName]))
                return $body[$paramName];
        }
        return false;
    }
}