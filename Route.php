<?php
namespace Rest;
use Rest\Handlers\IHandler;
use Rest\Rules\BaseRule;
use Rest\Rules\IRule;
use Rest\Handlers\NotFound;
use Rest\Request;


class Route
{
    protected $request;
    protected $ruleList = [];
    protected static $instance;

    private function __call($name, $arguments)
    {
    }

    public static function getInstance(){
        if(empty(self::$instance)){
            self::$instance = new self();
            return self::$instance;
        }
        return self::$instance;
    }

    private function __construct(){
        $this->request = new Request();
        return $this;
    }

    public function likeRule() : IRule{
        $method = $this->request->method();
        $uri = $this->request->uri();

        foreach ($this->ruleList[$method] as $rule) {
            if ($rule->match($uri))
                return $rule;
        }

        return (new BaseRule($uri, new NotFound()));
    }

    public function get(string $uri, IHandler $handler){
        $this->ruleList['GET'][] = new BaseRule($uri, $handler);
    }
    public function post(string $uri, IHandler $handler){
        $this->ruleList['POST'][] = new BaseRule($uri, $handler);
    }
    public function put(string $uri, IHandler $handler){
        $this->ruleList['PUT'][] = new BaseRule($uri, $handler);
    }
    public function delete(string $uri, IHandler $handler){
        $this->ruleList['DELETE'][] = new BaseRule($uri, $handler);
    }
}