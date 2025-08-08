<?php

namespace WTE\Original\Utilities;

Class ConcreteTypeChecker
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toBe($type)
    {
        if (is_array($this->value)) {
            return $this->groupCheck($type);
        }

        return $this->checkSingleValue($type);
    }

    public function toBeBoolean()
     {
         if (is_bool($this->value)) {
            return $this->value;
         }

         throw new UnexpectedTypeException("Value must be boolean, " . gettype($this->value) . ' given.');
         
     } 

    protected function checkSingleValue($type)
    {
        if (!is_a($this->value, $type, $useClassNames = true)) {
            (string) $givenValue = is_object($this->value)? get_class($this->value) : gettype($this->value) . " $this->value";
            throw new UnexpectedTypeException("{$givenValue} must be instance of {$type}");
        }

        return $this->value;
    }

    protected function groupCheck($type)
    {
        foreach ($this->value as $item) {
            (new Self($item))->checkSingleValue($type);
        }

        return $this->value;
    }

}