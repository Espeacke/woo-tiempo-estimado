<?php

namespace WTE\App\Handlers;

use WTE\Original\Environment\Env;
use WTE\Original\Events\Handler\EventHandler;

Class AdminScriptsHanlder extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    protected $scriptName = 'wte_admin_script';

    public function execute($hook)
    {
        wp_enqueue_script(
            $this->scriptName, 
            Env::directoryURI() . '/app/scripts/admin.js',
            ['jquery'],
            '3.0'
        );      

        wp_enqueue_style(
            'wte_admin_script', 
            Env::directoryURI() . '/app/styles/admin.css',
            null,
            '3.0'
        );       
    }
}