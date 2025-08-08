<?php

namespace WTE\Original\Collections\Mapper;

use WTE\Original\Collections\Collection;
use WTE\Original\Collections\JSONMapper;

Abstract Class Mappable
{
    abstract protected function getMap(); #: Array
    abstract protected function getValuesToUnmap(); #Collection
    protected function map($value)
    {
        (object) $jsonMapper = new JSONMapper($this->getMap());

        return $jsonMapper->smartMap($value);
    }

    public function unMap()
    {
        (object) $values = $this->getValuesToUnmap();
        (object) $readyValues = new Collection([]);

        foreach ($values->asArray() as $key => $value) {
            if ($value instanceof Mappable) {
                $unMappedValue = $value->unMap();
                $readyValues->add($key, (JSONMapper::isInvalidJson($unMappedValue)? $unMappedValue : json_decode($unMappedValue)));
            } else {
                $readyValues->add($key, $value);
            }
        }

        return $readyValues->asJson()->get();
    }
    
}