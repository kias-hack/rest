<?php
namespace Rest\Rules;


use Rest\Handlers\IHandler;

interface IRule
{
    public function __construct(string $uri, IHandler $handler);
    public function match() : bool;
    public function where(string $param, string $regex) : IRule;
    public function delegate();
    public function setRequestURI(string $uri);
    public function getParamsValueFromURI() : array;
}