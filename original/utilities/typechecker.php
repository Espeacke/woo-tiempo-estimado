<?php

namespace WTE\Original\Utilities;

use WTE\Original\Utilities\ConcreteTypeChecker;

Trait TypeChecker
{
    protected function expect($value)
    {
        return new ConcreteTypeChecker($value);
    }

    /* 
        The key here is we are type hinting the array, if we used the above method we wouldn't be able to enforce the value to be an array
    */
    protected function expectEach(Array $values) {
        return new ConcreteTypeChecker($values);
    }
}