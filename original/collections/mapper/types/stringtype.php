<?php

namespace WTE\Original\Collections\Mapper\Types;

use WTE\Original\Characters\StringManager;
use WTE\Original\Collections\Mapper\Types;

Class StringType extends Types
{
    public static function stringsOnly()
    {
        return function ($value) {
            return Types::isString($value);
        };
    }
    
    protected function setType()
    {
        return static::STRING;
    }

    public function isCorrectType($value)
    {
        return Types::isString($value);
    }

    public static function castToExpectedType($value, $beforeResotringToNull = null)
    {
        (string) $value = is_string($value)? $value : '';

        return new StringManager($value);
    }

    public function hasDefaultValue()
    {
        if (is_string($this->defaultValue)) {
            return $this->defaultValue !== '';
        }

        return ($this->defaultValue instanceof StringManager) && 
               (!$this->defaultValue->isEmpty());  
    }

    public function concretePickValue($newValue)
    {
        if (($newValue === '') || !$this->isCorrectType($newValue)) {
            return $this->getDefaultValue();
        }

        return $newValue;
    }
}