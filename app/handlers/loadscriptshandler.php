<?php

namespace WTE\App\Handlers;

use WTE\Original\Environment\Env;
use WTE\Original\Events\Handler\EventHandler;

Class LoadScriptsHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    public function execute()
    {
        wp_enqueue_script( 'wte_calendar', Env::directoryURI().'app/scripts/vendor/pignose.calendar.full.js', array('jquery'));
        wp_enqueue_script( 
            'wte_script', 
            Env::directoryURI().'app/scripts/wte-script.js', 
            array('wte_calendar'),
            '1.1.0'
        );
        

        wp_register_style( 'wte_calendar_css', Env::directoryURI().'app/scripts/vendor/pignose.calendar.min.css');
        wp_enqueue_style ( 'wte_calendar_css' );
    }
}