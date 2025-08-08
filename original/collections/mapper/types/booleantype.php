<?php

namespace WTE\Original\Collections\Mapper\Types;

use WTE\Original\Collections\Mapper\Types;

Class BooleanType extends Types
{
    protected function setType()
    {
        return static::BOOLEAN;
    }

    public function isCorrectType($value)
    {
        return is_bool($value);
    }

    public static function castToExpectedType($value, $beforeResotringToNull = null)
    {
        if (static::isBooleanString($value)) {
            return $value === 'true'? true : false;
        } elseif (is_bool($value)) {
            return $value;
        } elseif ($beforeResotringToNull !== null) {
            return $beforeResotringToNull;
        } else {
            return null;
        }
    }

    public function hasDefaultValue()
    {
        return is_bool($this->defaultValue);
    }

    public function concretePickValue($newValue)
    {
        if (!$this->isBoolean($newValue)) {
            return $this->getDefaultValue();
        }

        return $newValue;
    }

    protected function isBoolean($newValue)
    {
        return is_bool($newValue) || static::isBooleanString($newValue);
    } 

    protected static function isBooleanString($value)
    {
        return is_string($value) && in_array($value, ['true', 'false']);
    }
}