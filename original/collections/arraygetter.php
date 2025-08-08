<?php

namespace WTE\Original\Collections;

use BadMethodCallException;
use WTE\Original\Collections\Abilities\ArrayRepresentation;

Class ArrayGetter
{
    public static function getArrayOrThrowExceptionFrom($value)
    {
        if (is_array($value) ) {
            return $value;
        } elseif ($value instanceof ArrayRepresentation){
            return $value->asArray();
        }

        throw new BadMethodCallException("Error: method expects parameter to be array or ArrayRepresentation, ".gettype($value).' given.');
        
    }

    public static function isArrayRepresentation($value)
    {
        return is_array($value) || $value instanceof ArrayRepresentation; 
    }
}