<?php

class Route {
    function __construct($path, $executable, $defVariables = []){
        $this->path = $path;
        $this->executable = $executable;
        $this->defVariables = $defVariables;
        $this->variables = [];
        
        $this->preparePath();
    }
    
    function preparePath(){
        $variables = null;
        
        preg_match_all('/\#\w+\#/', $this->path, $variables, PREG_UNMATCHED_AS_NULL);
        
        $variables = array_map(function($i){
            return str_replace("#", "", $i);
        }, $variables);
        
        foreach ($variables[0] as $variable) {
            $this->variables[$variable] = null;
        }
    }
    
    function getPath(){
        return $this->path;
    }
    
    function getExecutable(){
        return $this->executable;
    }
    
    function getDefVariables(){
        return $this->defVariables;
    }
    
    function getVariables(){
        return $this->variables;
    }
    
    function setVariables(array $variables){
        foreach($variables as $name => $value){
            if(!in_array($name, array_keys($this->variables)))
                throw new \Exception("нету такого ключа - " . $name);
                
            $this->variables[$name] = $value;
        }
    }
}

class RouteMatcher{
    function match($curPath, Route $route) : bool{
        $matches = [];
        $routeReg = $route->getPath();
        
        $routeReg = str_replace("/", "\/", $routeReg);
        $routeReg = str_replace("?", "\?", $routeReg);
        
        foreach($route->getVariables() as $key => $variable){
            $routeReg = str_replace("#$key#", "\\w+", $routeReg);    
        }
        
        preg_match("/" . $routeReg . "/", $curPath, $matches);
        
        if(count($matches) != 1)
            return false;
            
        if(strlen($matches[0]) != strlen($curPath))
            return false;
        
        return true;
    }
}

class VariableUrlExtracor{
    function extract(string $path, Route $route){
        $partsCur = explode("?", $path);
        $partsTmpl = explode("?", $route->getPath());
        
        $uriCur = $partsCur[0];
        $paramsCur = $partsCur[1];
        
        $uriTmpl = $partsTmpl[0];
        $paramsTmpl = $partsTmpl[1];
        
        $this->uriCurCrumbs = explode("/", $uriCur);
        $this->paramsCurCrumbs = explode("&", $paramsCur);
        
        $this->uriTmplCrumbs = explode("/", $uriTmpl);
        $this->paramsTmplCrumbs = explode("&", $paramsTmpl);
        
        $this->clearCrumbs();
        
        $variables = [];
        
        $this->extractVariables($this->uriTmplCrumbs, $this->uriCurCrumbs);
        $this->extractVariables($this->paramsTmplCrumbs, $this->paramsCurCrumbs);
        
        return $this->variables;
    }
    
    private function clearCrumbs(){
        $this->uriCurCrumbs = array_filter($this->uriCurCrumbs);
        $this->paramsCurCrumbs = array_filter($this->paramsCurCrumbs);
        $this->uriTmplCrumbs = array_filter($this->uriTmplCrumbs);
        $this->paramsTmplCrumbs = array_filter($this->paramsTmplCrumbs);
    }
    
    private function extractVariables($haystackTmpl, $haystackCur){
        foreach($haystackTmpl as $key => $crumb){
            $match = [];
            preg_match("/\#\w+\#/", $crumb, $match);
            
            if(count($match) != 1)
                continue;
                
            $this->extractVariable($crumb, $match, $haystackCur[$key]);
        }
    }
    
    private function extractVariable($crumbTmpl, $match, $crumbCur){            
        $cname = str_replace("#", "", $match[0]);
                
        $other = preg_replace("/\#\w+\#/", "", $crumbTmpl);
        
        $value = str_replace($other, "", $crumbCur);
        
        $this->variables[$cname] = $value;
    }
}

class Router {
    function __construct(RouteMatcher $matcher, VariableUrlExtracor $extractor){
        $this->get = [];
        $this->post = [];
        
        $this->matcher = $matcher;
        $this->extractor = $extractor;
    }
    
    function get($path, $executable, $defVariables = []){
        $route = new MyRoute($path, $executable, $defVariables);
        
        $this->get[] = $route;
        
        return $route;
    }
    
    function post($path, $executable, $defVariables = []){
        $route = new MyRoute($path, $executable, $defVariables);
        
        $this->post[] = $route;
        
        return $route;
    }
    
    function handle($request){
        switch ($request["REQUEST_METHOD"]) {
            case "POST":
                $container = $this->post;
                break;
                
            case "GET":
                $container = $this->get;
                break;
        }
        
        foreach($container as $route){
            if(!$this->matcher->match($request["REQUEST_URI"], $route))
                continue;
                
            $variables = $this->extractor->extract($request["REQUEST_URI"], $route);
                
            $route->setVariables($variables);
                
            return $route;
                
            break;
        }
    }
}

interface MiddlewareContract{
    function handle($data);
}

interface MiddlewareAwareContract
{
    function beforeMiddleware(MiddlewareContract $middleware);
    function afterMiddleware(MiddlewareContract $middleware);
    function getBeforeMiddlewares();
    function getAfterMiddlewares();
}

trait MiddlewareAwareTrait {
    private $after = [];
    private $before = [];
    
    function beforeMiddleware(MiddlewareContract $middleware){
        $this->before[] = $middleware;   
    }
    
    function afterMiddleware(MiddlewareContract $middleware){
        $this->after[] = $middleware;
    }
    
    function getBeforeMiddlewares(){
        return $this->before;
    }
    
    function getAfterMiddlewares(){
        return $this->after;
    }
}

class MyRoute extends Route implements MiddlewareAwareContract{
    use MiddlewareAwareTrait;
}

class AnyMiddleware implements MiddlewareContract {
    function handle($data){
        print_r("HELLO WORLD!!!\n");
    }
}

function catalog_detail($id_section, $id_element, $action, $ss = false){
    print_r($id_section . " " . $id_element . " " . $action . " " . $ss . " \n");
}

$router = new Router(new RouteMatcher, new VariableUrlExtracor);

$router->get("/catalog/#id_section#/#id_element#?action=#action#", "catalog_detail", ["ss" => "sss"])->beforeMiddleware(new AnyMiddleware);

$_REQUEST["REQUEST_URI"] = "/catalog/123/321?action=yes";
$_REQUEST["REQUEST_METHOD"] = "GET";

$route = $router->handle($_REQUEST);

foreach ($route->getBeforeMiddlewares() as $middleware)
    $middleware->handle($_REQUEST);

if(!function_exists($route->getExecutable()))
    throw new \Exception("функции " . (string)$route->getExecutable() . " не существует");

call_user_func_array($route->getExecutable(), $route->getVariables() + $route->getDefVariables());

foreach ($route->getAfterMiddlewares() as $middleware)
    $middleware->handle($_REQUEST);
