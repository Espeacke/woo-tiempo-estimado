<?php

namespace WTE\Original\Collections;

use BadMethodCallException;
use Closure;
use WTE\Original\Characters\StringManager;
use WTE\Original\Collections\Abilities\ArrayRepresentation;
use WTE\Original\Collections\ArrayGetter;
use WTE\Original\Collections\Mapper\Types;
use JsonSerializable;

Class Collection implements ArrayRepresentation, JsonSerializable
{
    private $elements = [];

    public static function range($minimum, $maximum)
    {
        return new Static(range($minimum, $maximum));   
    }
    
    public function __construct(/*Array|Collection*/ $elements)
    {
        (array) $elements = ArrayGetter::getArrayOrThrowExceptionFrom($elements);

        $this->elements = $elements;
    }

    public function asArray()
    {
        return $this->elements;
    }

    public function asJson()
    {
        return new StringManager(json_encode($this));   
    }

    public function clean()
    {
        return new Static(array_filter($this->elements));   
    }

    public function resetKeys()
    {
        $this->elements = array_values($this->elements);
        return $this;   
    }
    

    public function push($element)
    {
        $this->elements[] = $element;

        return $this;
    }

    public function add($key, $value)
    {
        $key = (string) $key;
        
        $this->elements[$key] = $value;   

        return $this;
    }

    public function append(/*Array|Colection*/ $elements)
    {   
        (array) $elements = ArrayGetter::getArrayOrThrowExceptionFrom($elements);

        foreach ($elements as $key => $value) {
            $this->elements[$key] = $value;
        }

        return $this;
    }
    
    public function appendAsArray(/*Array|Colection*/ $elements)
    {
        (array) $elements = ArrayGetter::getArrayOrThrowExceptionFrom($elements);

        foreach ($elements as $key => $value) {
            if (isset($this->elements[$key])) {
                if (is_array($this->elements[$key])) {
                    $this->elements[$key][] = $value;
                } else {
                    $this->elements[$key] = [$this->elements[$key], $value];
                }
            } else {
                $this->elements[$key] = [$value];
            }
        }

        return $this;
    }
    
    public function concat($arrayOrCollection)
    {
        if ($arrayOrCollection instanceof Collection) {
            $newElements = $arrayOrCollection->asArray();
        } else {
            $newElements = $arrayOrCollection;
        }

        return new Static(array_merge($this->elements, $newElements));
    }

    public function merge($arrayOrCollection)
    {
        return $this->concat($arrayOrCollection);   
    }
    
    public function first()
    {
        (boolean) $isFirstIteration = true;

        foreach ($this->elements as $key => $value) {
            if ($isFirstIteration) {
                return $value;
            }
        }
    }

    public function last()
    {
        return isset($this->elements[$this->lastKey()])? $this->elements[$this->lastKey()] : null;
    }

    public function haveMoreThan($number)
    {
        return count($this->elements) > $number;
    }

    public function haveLessThan($number)
    {
        return count($this->elements) < $number;
    }

    public function haveExactly($number)
    {
        return count($this->elements) === $number;
    }

    public function haveAtLeast($number)
    {
        return count($this->elements) >= $number;
    }

    public function haveMaximum($number)
    {
        return count($this->elements) <= $number;
    }

    public function haveAny()
    {
        return count($this->elements) > 0;
    }

    public function haveNone()
    {
        return !$this->haveAny();
    }

    public function count()
    {
        return count($this->elements);
    }

    public function isIndexed()
    {
        foreach ($this->elements as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }

    public function atPosition($index)
    {
        (integer) $currentindex = 1;

        foreach ($this->elements as $element) {
            if ($currentindex === $index) {
                return $element;
            } else {
                $currentindex++;
            }
        }
        
    }

    public function hasKey($keyToSearch)
    {
        $keyToSearch = (string) $keyToSearch;

        return isset($this->elements[$keyToSearch]);   
    }

    public function search($value)
    {
        return array_search($value, $this->elements);   
    }

    public function map(Callable $callable)
    {
        return new Static(array_map($callable, $this->elements));
    }

    public function mapWithKeys(Callable $callable)
    {
        (array) $newArray = [];

        foreach($this->elements as $index => $element) {
            (array) $mappedData = $callable($element, $index);

            $newArray[$mappedData['key']] = $mappedData['value'];
        }

        return new Static($newArray);
    }

    /**
     * MUTABLE ITERATION, RETURNS THE SAME INSTANCE
     */
    public function forEvery(Callable $callable)
    {
        foreach ($this->elements as $key => &$value) {
            $result = $callable($value, $key);

            if ($result === false) {
                break;
            }
        }

        return $this;
    }

    public function reduce(Callable $callback, $initial = null)
    {
        $reduceResult = array_reduce($this->elements, $callback, $initial);

        return Types::isString($reduceResult)? new StringManager((string) $reduceResult) : $reduceResult;
    }

    public function reverse()
    {
        return new Static(array_flip($this->asArray()));   
    }
    
    public function asList($separator = ',')
    {
        return $this->implode("{$separator} ")->trim("{$separator} ");
    }

    public function implode($separator)
    {
        return new StringManager(implode($separator, $this->elements));   
    }

    public function filter(Callable $callable = null)
    {
        (object) $filteredElements = new Static([]);

        $elements = !is_callable($callable)? array_filter($this->elements) : $this->elements;

        foreach ($elements as $key => $value) {
            if ($callable instanceof Closure) {
                (boolean) $canBeIncluded = $callable($value, $key);
            } else {
                (boolean) $canBeIncluded = $callable($value);
            }

            if ($canBeIncluded) {
                $filteredElements->add($key, $value);
            }
        }

        return $filteredElements;
    }

    public function shift()
    {
        return array_shift($this->elements);   
    }
    
    /**
     * array_diff
     */
    public function not(/*ArrayRepresentation*/ $elements)
    {
        (array) $elements = ArrayGetter::getArrayOrThrowExceptionFrom($elements);

        return new Static(array_diff($this->elements, $elements));
    }

    public function sort(Callable $callable)
    {
        (array) $newArray = $this->elements;

        usort($newArray, $callable);

        return new Static($newArray);   
    }

    public function except(/*Array|Collection*/ $keysToExclude)
    {
        (array) $keysToExclude = ArrayGetter::getArrayOrThrowExceptionFrom($keysToExclude);

        return (new Static($this->elements))->filter(function($value, $key) use($keysToExclude) {
            return !in_array($key, $keysToExclude);
        });
    }

    public function only(Array $keysToInclude)
    {
        return new Static(
            array_intersect_key($this->elements, array_flip($keysToInclude))
        );
    }

    /**
     * Compares the inner elements array (self::$elements) to the given array,
     * both arrays must be equal; StringManager values will be typecasted to regular
     * strings so different instances with the same value will evaluate to true
     */
    public function are(Array $itemsToCompare)
    {
        if (count($itemsToCompare) !== $this->count()) {
            return false;
        }

        foreach ($itemsToCompare as $keyToCompare => $valueToCompare) {
            if (!$this->hasKey($keyToCompare)) {
                return false;
            }
            $selfValue = $this->get($keyToCompare);

            if ($valueToCompare instanceof StringManager) {
                $valueToCompare = $valueToCompare->get();
            }

            if ($selfValue instanceof StringManager) {
                $selfValue = $selfValue->get();
            }

            if ($selfValue !== $valueToCompare) {
                return false;
            }
        }

        return true;
    }
    public function areNot(Array $items)
    {
        return !$this->are($items);
    }
    
    public function contain($itemToSearch)
    {
        if ($itemToSearch instanceof Closure) {
            return $this->filter($itemToSearch)->haveAny();
        }

        if (Types::isString($itemToSearch)) {
            return in_array(strtolower($itemToSearch), $this->map(StringManager::convertToLowerCase())->asArray());
        }

        if (is_array($itemToSearch) || $itemToSearch instanceof Collection) {
            $itemToSearch = ArrayGetter::getArrayOrThrowExceptionFrom($itemToSearch);
            $itemToSearch = (new Static($itemToSearch))
                            ->map(Static::convertToString())
                            ->asArray();
        }

        return in_array(
            $itemToSearch, 
            $this->map(Static::convertToString())
                 ->map(function($value){return ($value instanceof Collection)? $value->asArray() : $value;})
                 ->asArray(), 
            $strictTypeSearch = true
        );
    }

    public function containEither(Array $elements)
    {
        foreach($elements as $element) {
            if ($this->contain($element)) {
                return true;
            }
        }

        return false;
    }

    public function containAll(/*Colection|Array*/$elements)
    {

        if ($elements instanceof Collection) {
            $elements = $elements->asArray();
        }

        if (empty($elements) && $this->haveAny()) return false;

        foreach($elements as $element) {
            if (!$this->contain($element)) {
                return false;
            }
        }

        return true;  
    }
    
    public function allMatch($regularExpression)
    {
        if ($this->haveNone()) return false;

        foreach ($this->elements as $element) {
            if (is_string($element)) {
                $element = new StringManager($element);
            } elseif (!($element instanceof StringManager)) {
                return false;
            }
            if (!$element->matches($regularExpression)) {
                return false;
            }
        }

        return true;
    }
    

    public function test(Callable $callable)
    {
        foreach ($this->elements as $element) {
            (boolean) $hasItpassed = ($callable($element) === true);

            if ($hasItpassed) {
                return true;
            }
        }

        return false;
    }

    public function get($key)
    {
        $key = (string) $key;

        if ($this->hasKey($key)) {
            return $this->elements[$key];
        }   
    }

    public function remove($key)
    {
        $key = (string) $key;

        if ($this->hasKey($key)) {
            unset($this->elements[$key]);
        }

        return $this;
    }

    public function removelast()
    {
        $this->remove($this->lastKey());

        return $this;   
    }

    public function lastKey()
    {
        (string) $lastestKey = null;

        foreach ($this->elements as $key => $value) {
            $lastestKey = $key;            
        }   

        return $lastestKey;
    }

    public function getKeys()
    {
        return (new Static(array_keys($this->elements)))->map($this->valueToStringManager());   
    }
    
    public function getValues()
    {
        return new Static(array_values($this->elements));   
    }
    

    public function getEarliest(Array $elementsToSearch)
    {
        return $this->getValueSortedBy(function($index, $validElement, $currentPosition) {
            return ($index < $currentPosition)? $index : false;
        }, $elementsToSearch);

    }

    public function getLatest(Array $elementsToSearch)
    {
        return $this->getValueSortedBy(function($index, $validElement, $currentPosition) {
            return ($index > $currentPosition)? $index : false;
        }, $elementsToSearch, $currentPosition = 0);
    }

    public function getByField($field, $value)
    {
        $key = array_search($value, array_column($this->elements, $field));

        if (($key !== false) && isset($this->elements[$key])) {
            return $this->elements[$key];
        }
    }

    public static function areEqual($collectionOrArray1, $collectionOrArray2)
    {
        if (!($collectionOrArray1 instanceof Collection) && (!is_array($collectionOrArray1))) {
            return false;
        } elseif (!($collectionOrArray2 instanceof Collection) && (!is_array($collectionOrArray2))) {
            return false;
        }

        (array) $array1 = ArrayGetter::getArrayOrThrowExceptionFrom($collectionOrArray1);
        (array) $array2 = ArrayGetter::getArrayOrThrowExceptionFrom($collectionOrArray2);

        return $array1 === $array2;
    }
    

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->elements;   
	}
    
    protected function getValueSortedBy(Callable $sortType, Array $elementsToSearch, $currentPosition = 1000000)
    {
        (array) $validElements = array_intersect($this->elements, $elementsToSearch);

        foreach ($validElements as $index => $validElement) {
            $result = $sortType($index, $validElement, $currentPosition);

            if (is_int($result)) {
                $currentPosition = $result;
            }
        }

        return isset($this->elements[$currentPosition])? $this->elements[$currentPosition] : null;
    }

    protected function valueToStringManager()
    {
        return function($key){
            if (is_string($key)) {
                return new StringManager($key);
            }
            
            return $key;
        };   
    }

    protected static function convertToString()
    {
        return function($value) {
            return StringManager::stringToNative($value);           
        };            
    }
}