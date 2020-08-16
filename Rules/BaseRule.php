<?php
namespace Rest\Rules;

use Rest\Handlers\IHandler;

class BaseRule implements IRule
{
    protected $templateURI;
    protected $requestURI;
    protected $handler;
    protected $params;
    protected $ruleTemplate;
    protected $TemplateForParams = '/{[a-z A-Z 0-9]*}/';

    public function __construct(string $uri, IHandler $handler){
        if(!empty($uri))
            $this->templateURI = $uri;
        else
            throw new \Exception('URI not define');

        $this->handler = $handler;

        $this->cutParamFromURI();
        $this->ruleTemplate['default'] = '[a-z A-Z 0-9]*';

        return $this;
    }

    public function match() : bool{
        $__uriTemplate = $this->replaceParamsInUri();

        if(preg_match('/^'.$__uriTemplate.'$/', $this->requestURI))
            return true;
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

    public function getParamsValueFromURI() : array{
        $templateReg = $this->replaceParamsInUri();

        $values = [];
        $arParamToVAlue = [];
        $count = 0;

        preg_match_all('/'.$templateReg.'/', $this->requestURI, $values, PREG_PATTERN_ORDER);

        d($templateReg, $this->requestURI, $values);

        foreach ($this->params as $param){
            $arParamToVAlue[$param] = $values[0][$count];
        }

        return [];
    }

    public function setRequestURI(string $uri){
        if(!empty($uri))
            $this->requestURI = $uri;
        else
            throw new Exception("uri is empty");
    }
}
