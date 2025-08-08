<?php

namespace WTE\App\WTE\WordPressTime;

use WTE\App\Handlers\WooCommerceCompatibilityHandler;
use WTE\App\WTE\Time\TimeRange;
use WTE\Original\Characters\StringManager;
use WTE\Original\Environment\Env;

Class EstimatedUnitsReader
{
    protected $timeRange;

    public static function getFromProductId($productId)
    {
        return new Static(
            new TimeRange(
                get_post_meta(
                    $productId, 
                    WooCommerceCompatibilityHandler::ETA_FIELD_NAME, 
                    true
                )
            )
        );
    }
    
    public function __construct(TimeRange $timeRange)
    {
        $this->timeRange = $timeRange; 
    }
    
    public function getReadable($stylized = true)
    {
        return $this->timeRange->getReadable(function($min, $max, StringManager $unit) use ($stylized){
            (string) $minToMax = sprintf( esc_html__('%1$s a %2$s', Env::textDomain()), $min, $max);
            (string) $businessUnits = $unit->is('hours')? 
                                        esc_html__('horas hábiles', Env::textDomain()) :
                                        esc_html__('días hábiles', Env::textDomain());
            (string) $optionalStyles = $stylized? 'text-transform: uppercase; font-weight: bold; color: red' : '';

            return
            "<span style=\"{$optionalStyles}\">{$minToMax} {$businessUnits}</span>"; 
        });   
    }
    
    public function format(callable $callable)
    {
        return $this->timeRange->getReadable($callable);
    }
    
}

