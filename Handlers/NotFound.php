<?php


namespace Rest\Handlers;
use Rest\Handlers\IHandler;

class NotFound implements IHandler
{
    public function execute(){
        echo 'Page notfound';
    }
}