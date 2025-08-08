<?php

namespace WTE\Original\Collections;

use WTE\Original\Collections\Collection;
use WTE\Original\Collections\MappedObject;
use WTE\Original\Characters\StringManager;
use WTE\Original\Collections\Mapper\Mappable;
use WTE\Original\Collections\Mapper\Types;
use WTE\Original\Collections\Mapper\Types\BooleanType;
use WTE\Original\Collections\Mapper\Types\CollectionType;
use WTE\Original\Collections\Mapper\Types\StringType as S;
use StdClass;

class JSONMapper
{
    protected $mapDefinition = [];
    public $mappedObjectOnlyWithFieldsFound;
    protected $mappedObject;

    public static function getArrayFromJson($JSONString)
    {
        (string) $json = static::getValidJsonString($JSONString);
        return json_decode($json, $asArray = true);
    }

    public function __construct(array $map)
    {
        $this->mapDefinition = $map;
        $this->mappedObjectOnlyWithFieldsFound = new MappedObject;
        $this->mappedObject = new StdClass;
    }

    public function smartMap($value)
    {
        if ((!$this->hasMap()) && $this->isNonJson($value)) {
            return $value;
        } elseif ($this->isObjectOrAssociativeArray($value)) {
            (object) $jsonObject = $this->getObjectFromJson(json_encode($value));
        } else {
            (object) $jsonObject = $this->getObjectFromJson($value);
        }

        return $this->mapObject($jsonObject);   
    }

    public function map($jsonable)
    {
        return $this->mapObject($this->getObjectFromJson($jsonable));
    }

    public function mapObject($parameters)
    {
        $parameters = $this->removeEntriesNotInMap($parameters);

        foreach ($this->mapDefinition as $key => $value) {

            $parameterValue = $this->getAllowedOrDefaultValue($value, $parameters->{$key});

            if (is_array($value)) {
                $parameters->{$key} = $this->recursiveMap($value, $parameterValue);
            } elseif (is_string($value) && is_a($value, Collection::class, $className = true) || $this->valueIs($value, Types::COLLECTION)) {
                (array) $newArray = is_string($parameterValue) ? array_map(function($item) {
                    return trim($item);
                }, explode(',', $parameterValue)) : (array) $parameterValue;

                $parameters->{$key} = (new Collection($newArray))->clean();
            } elseif (is_a($value, Mappable::class, $className = true)) {
                $parameters->{$key} = new $value($parameterValue);
            } elseif ($this->valueIs($value, Types::ANY)) {
                $parameters->{$key} = $parameterValue;
            } elseif ($this->valueIs($value, Types::STRING) || Types::isString($value)) {
                (string) $stringValue = (Types::isString($parameterValue) || is_numeric($parameterValue)) ? (string) $parameterValue : "";

                $parameters->{$key} = new StringManager($stringValue);
            } elseif ($this->valueIs($value, Types::INTEGER) || (is_integer($value) && $value !== Types::BOOLEAN)) {
                (integer) $earlyValue = (is_integer($parameterValue) || is_numeric($parameterValue)) ? (integer) $parameterValue : 0;

                if (($value instanceof Types) && $value->hasMinimum()) {
                    (integer) $minimumValue = $value->getOrDefaultToMinimum($earlyValue);
                    $parameters->{$key} = ($earlyValue >= $value->getMinimum()) ? $minimumValue : max($minimumValue, (integer) $value->getDefaultValue());
                } else {
                    $parameters->{$key} = $earlyValue;
                } 
            } elseif ($this->valueIs($value, Types::BOOLEAN)) {
                $parameters->{$key} = BooleanType::castToExpectedType($parameterValue, $beforeResortingTonull = false);
            }
        }

        $this->mappedObject = $parameters;

        $this->setObjectWithOnlyFieldsFound($parameters);

        return $parameters; 
    }

    protected function setObjectWithOnlyFieldsFound(\StdClass $parameters)
    {
        foreach ($this->mappedObject->mapFieldsFoundInSource->asArray() as $fieldName) {
            $this->mappedObjectOnlyWithFieldsFound->{$fieldName->get()} = $parameters->{$fieldName->get()};
        }   

        $this->mappedObject->setMappedFieldsFound($this->mappedObjectOnlyWithFieldsFound);
    }

    protected function recursiveMap(array $map, $parameterValue)
    {
        (object) $jsonMapper = new static($map);

        return $jsonMapper->smartMap($parameterValue);
    }

    protected function removeEntriesNotInMap($parametersObject)
    {
        (object) $newObject = new MappedObject;

        foreach($this->mapDefinition as $property => $value) {
            if (isset($parametersObject->{$property})) {
                $givenValue = $parametersObject->{$property};
                $newObject->mapFieldsFoundInSource->push(new StringManager($property));
            } else {
                $givenValue = '';
                $newObject->mapFieldsNotFoundInSource->push(new StringManager($property));
            }

            $newObject->{$property} = $givenValue;
        }

        (object) $sourceObject = new Collection((array) $parametersObject);

        $newObject->allFieldsFoundInSource->append(
                                            $sourceObject->getKeys()
                                                         ->filter(S::stringsOnly())
                                                         ->map(function($property){
                                                             return new StringManager($property);
                                                         })
                                                        ->asArray()
                                          );

        return $newObject;
    }

    protected function getAllowedOrDefaultValue($mapValue, $parameterValue)
    {
        $parameterValue = $this->getOrFallbackToDefaultValue($mapValue, $parameterValue);

        if (($mapValue instanceof Types) && !$mapValue->anyValueIsAllowed()) {
            if (
                ($mapValue instanceof CollectionType) ? 
                    (!$mapValue->getAllowedValues()->containAll($parameterValue)) : 
                    (!$mapValue->getAllowedValues()->contain($parameterValue))
            ) {
                return $mapValue->getFallbackAllowedValue();
            }
        }

        return $parameterValue;
    }

    protected function getOrFallbackToDefaultValue($mapValue, $parameterValue)
    {
        if (($mapValue instanceof Types)) {
            return $mapValue->pickValue($parameterValue);
        }   

        return $parameterValue;
    }

    protected function valueIs($value, $type)
    {
        if ($value instanceof Types) {
            return $value->is($type);
        }

        return $value === $type;   
    }

    protected function hasMap()
    {
        return $this->mapDefinition !== [];
    }

    protected function isNonJson($value)
    {
        if (is_string($value)) {
            if (empty($value) || trim($value)[0] !== '{') {
                return true;  
            }
            return false;
        } elseif (is_numeric($value) || is_bool($value)) {
            return true;
        } elseif (is_array($value) && (new Collection($value))->isIndexed()) {
            return true;
        }

        return false;
    }

    protected function isObjectOrAssociativeArray($value)
    {
        return is_object($value) || is_array($value);
    } 

    protected function getObjectFromJson($JSONString)
    {
        (string) $json = static::getValidJsonString($JSONString);

        return (object) json_decode($json);
    }

    public static function getValidJsonString($JSONString)
    {
        return static::isInvalidJson($JSONString) ? "{}" : $JSONString;
    }

    public static function isInvalidJson($json)
    {
        if (!is_string($json)) return true;
            
        call_user_func_array('json_decode', func_get_args());

        return (json_last_error() !== JSON_ERROR_NONE);
    }
}
