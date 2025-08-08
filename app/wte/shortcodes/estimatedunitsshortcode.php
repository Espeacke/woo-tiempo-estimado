<?php

namespace WTE\App\WTE\Shortcodes;

use WTE\App\Handlers\WooCommerceCompatibilityHandler;
use WTE\App\WTE\Time\TimeRange;
use WTE\Original\Characters\StringManager;
use WTE\Original\Collections\Collection;
use WTE\Original\Collections\Mapper\Types;
use WTE\Original\Environment\Env;
use WTE\Original\Shortcodes\Shortcode;

Class EstimatedUnitsShortCode extends Shortcode
{
    protected static $name = 'eta';

    protected $timeRange;

    public static function getDefinition(array $properties)
    {
        (object) $properties = new Collection($properties);
        (string) $name = static::name();

        return "[{$name} id=\"{$properties->get('id')}\"]";   
    }
    
    protected function map()
    {
        return [
            'id' => Types::INTEGER,
            'order_id' => Types::INTEGER // optional
        ];   
    }

    public function setUp()
    {
        $this->timeRange = new TimeRange(
            WooCommerceCompatibilityHandler::getETAData($this->properties->id, $this->properties->order_id)
        );
    }
    

    public function render()
    {
        return $this->timeRange->getReadable(self::getReadableFormatter());   
    }

    public static function getReadableFormatter()
    {
        return function($min, $max, StringManager $unit){
            (string) $minToMax = sprintf( esc_html__('%1$s a %2$s', Env::textDomain()), $min, $max);
            (string) $businessUnits = $unit->is('hours')? 
                                        esc_html__('horas hábiles', Env::textDomain()) :
                                        esc_html__('días hábiles', Env::textDomain());

            return
            "<span style=\"text-transform: uppercase; font-weight: bold; color: red\">{$minToMax} {$businessUnits}</span>"; 
        };   
    }
    
    
}