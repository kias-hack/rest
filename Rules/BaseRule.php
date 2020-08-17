<?php
namespace Rest\Rules;

use Rest\Handlers\IHandler;
use Exception;

class BaseRule implements IRule
{
    protected $templateURI;
    protected $requestURI;
    protected $handler;
    protected $params;
    protected $ruleTemplate;
    protected $TemplateForParams = '/{[a-z A-Z 0-9 - _]*}/';
    protected $variables;

    public function __construct(string $uri, IHandler $handler){
        if(!empty($uri))
            $this->templateURI = ($uri[strlen($uri)-1] === '/') ? $uri.'index.php' : $uri;
        else
            throw new \Exception('URI not define');

        $this->handler = $handler;

        $this->cutParamFromURI();
        $this->ruleTemplate['default'] = '[a-z A-Z 0-9]*';

        return $this;
    }

    public function match(string $uri) : bool{
        $this->setRequestURI($uri);
        $__uriTemplate = $this->replaceParamsInUri();
        d($__uriTemplate);
        if(preg_match('/^'.$__uriTemplate.'$/', $this->requestURI)) {
            $this->variables = $this->getParamsValueFromURI();
            return true;
        }
        return false;
    }

    protected function replaceParamsInUri() : string {
        $newUri = str_replace('/', '\/', $this->templateURI);

        foreach ($this->params as $param){
            if(in_array($param, array_keys($this->ruleTemplate)))
                $regex = $this->ruleTemplate[$param];
            else
                $regex = $this->ruleTemplate['default'];

            $newUri = str_replace('{'.$param.'}', $regex, $newUri);
        }

        return $newUri;
    }

    public function where(string $param, string $regex) : IRule{
        if(in_array($param, $this->params)){
            if(!empty($regex))
                $this->ruleTemplate[$param] = $regex;
            else
                throw new Exception('regex empty');
        }
        else
            throw new Exception('param '.$param.' not found');

        return $this;
    }

    public function delegate(){
        $this->handler->execute();
    }

    protected function cutParamFromURI(){
        preg_match_all($this->TemplateForParams, $this->templateURI, $params, PREG_PATTERN_ORDER);

        foreach ($params as $key => $param)
            foreach ($param as $key => $param)
                $this->params[] = trim($param, '{}');
    }

    protected function getParamsValueFromURI() : array{
        $template = $this->requestURI;

        $arParamToVAlue = [];
        $notEmptyVariables = [];

        $bites = explode('/', $this->templateURI);

        foreach ($bites as $bite){
            if(!preg_match($this->TemplateForParams, $bite))
                $template = str_replace($bite, '', $template);
        }

        $variables = explode('/', preg_replace('/\/+/', '/', $template));

        foreach ($variables as $variable){
            if(!empty($variable))
                $notEmptyVariables[] = $variable;
        }

        foreach ($notEmptyVariables as $key => $variable){
            if(!empty($variable))
                $arParamToVAlue[$this->params[$key]] = $variable;
        }

        foreach ($this->params as $param){

        }

        return $arParamToVAlue;
    }

    protected function setRequestURI(string $uri){
        if(!empty($uri))
            $this->requestURI = $uri;
        else
            throw new Exception("uri is empty");
    }
}
