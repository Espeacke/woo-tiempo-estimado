<?php

namespace WTE\Original\Presentation;

use WTE\Original\Collections\Collection;

Class AttributesManager
{
    public function build(Array $attribute)
    {
        (array) $attribute = (new Collection($attribute))->mapWithKeys(function($value, $name){
            return [
                'key' => $name,
                'value' => esc_attr($value)
            ];
        })->asArray();

        return " {$attribute['name']}=\"{$attribute['value']}\"";
    }
}