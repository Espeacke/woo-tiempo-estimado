<?php

namespace WTE\Original\Collections\Mapper\Types;

use WTE\Original\Collections\Abilities\ArrayRepresentation;
use WTE\Original\Collections\Mapper\Types;

Class CollectionType extends Types
{
    protected function setType()
    {
        return static::COLLECTION;
    }

    public function isCorrectType($value)
    {
        return is_array($value);
    }

    public static function castToExpectedType($value, $beforeResotringToNull = null)
    {
        return (array) $value;
    }

    public function hasDefaultValue()
    {
        return $this->isCorrectType($this->defaultValue);
    }

    public function concretePickValue($newValue)
    {

        if (($newValue === []) || (is_object($newValue)) || (!is_array($newValue))) {
            return $this->getDefaultValue();
        }

        return $newValue;
    }
}