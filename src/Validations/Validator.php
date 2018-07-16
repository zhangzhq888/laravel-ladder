<?php

namespace Laravelladder\Core\Validations;

use Illuminate\Validation\Validator as IlluminateValidator;
use Laravelladder\Core\Exceptions\Validations\ValidationException;

class Validator extends IlluminateValidator
{
    protected $customFailures = [];

    public function addCustomFailure($attribute, $rule){
        $this->customFailures[] = array($attribute, $rule);
    }

    protected function applyCustomFailure(){
        foreach($this->customFailures as $failure){
            list($rule, $parameters) = $this->parseStringRule($failure[1]);
            $this->addFailure($failure[0], $rule, $parameters);
        }
    }

    public function customPasses(){
        $this->passes();
        $this->applyCustomFailure();
        return count($this->messages->all()) === 0;
    }

    public function customFails(){
        return !$this->customPasses();
    }

    public function makeException(){
        return new ValidationException($this);
    }
}