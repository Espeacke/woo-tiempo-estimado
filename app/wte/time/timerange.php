<?php

namespace WTE\App\WTE\Time;

use WTE\Original\Characters\StringManager;
use WTE\Original\Collections\Collection;
use WTE\Original\Collections\Mapper\Mappable;
use WTE\Original\Collections\Mapper\Types;

class TimeRange extends Mappable
{
    protected $range;
    protected $time;

    protected function getMap()
    {
        return [
            'unit' => Types::STRING()->allowed(['hours', 'days'])->withDefault('hours'),
            'range' => [
                'min' => Types::INTEGER,
                'max' => Types::INTEGER
            ]
        ];   
    }
    
    public function __construct($timeData)
    {
        $this->time = $this->map($timeData);
    }

    public function unitIs($unit)
    {
        return $this->time->unit->is($unit);
    }

    public function getUnit()
    {
        return $this->time->unit;   
    }

    public function getRange()
    {
        return $this->time->range->asCollection();   
    }

    public function hasValidRange()
    {
        return $this->time->range->min > 0 || $this->time->range->max > 0;
    }

    public function getReadable(callable $customFormatter = null)
    {
        if (is_callable($customFormatter)) {
            return $customFormatter($this->time->range->min, $this->time->range->max, $this->getUnit());
        }

        return "{$this->time->range->min} to {$this->time->range->max} business {$this->getUnit()}";
    }
                    
    protected function getValuesToUnmap()
    {
        return $this->time->asCollection();   
    }
}
