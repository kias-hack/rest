<?php
namespace Rest;

class Route
{
    public function __construct(string $base_dir = ''){
        return $this;
    }

    public function get(string $uri, IHandler $handler){

    }
}