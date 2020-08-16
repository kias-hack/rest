<?php
namespace Rest\Rules;

use Rest\Handlers\IHandler;

class BaseRule implements IRule
{
    protected $templateURI;
    protected $handler;
    protected $params;
    protected $ruleTemplate;
    protected $TemplateForParams = '/{[a-z A-Z 0-9]*}/';
    protected $httpMethod = 'GET';

    public function __construct(string $uri, IHandler $handler){
        if(!empty($uri))
            $this->templateURI = $uri;
        else
            throw new \Exception('URI not define');

        $this->handler = $handler;

        $this->cutParamFromURI();
        $this->ruleTemplate['default'] = '/[a-z A-Z 0-9]*/';

        return $this;
    }

    public function match(string $uri) : bool{
        $__uriTemplate = $this->replaceParamsInUri();

        if(preg_match($__uriTemplate, $uri))
            return true;
        return false;
    }

    protected function replaceParamsInUri() : string{
        $newUri = $this->templateURI;
        foreach ($this->params as $param){
            $paramCp = '{'.$param.'}';
            if(in_array($param, array_keys($this->ruleTemplate)))
                $regex = $this->ruleTemplate[$paramCp];
            else
                $regex = $this->ruleTemplate['default'];

            $newUri = str_replace($paramCp, $regex);
        }
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
    }

    public function delegate(){
        $this->handler->execute();
    }

    protected function cutParamFromURI(){
        preg_match_all($this->TemplateForParams, $this->templateURI, $params, PREG_PATTERN_ORDER);

        foreach ($params as $key => $param)
            d($params);
            $this->params[] = trim($param, '{}');
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }
}