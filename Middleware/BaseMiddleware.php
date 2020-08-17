<?php


namespace Rest\Middleware;
use Rest\Router;

interface IMiddleware{
    public function __construct(Router $router);
    public function execute(Router $router) : bool;
}

abstract class BaseMiddleware implements IMiddleware
{
    public function __construct(Router $router)
    {
        $this->execute($router);
    }

    abstract public function execute(Router $router): bool;
}