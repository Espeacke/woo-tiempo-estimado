<?php

namespace WTE\App\Handlers;

use WTE\Original\Events\Handler\EventHandler;

Class HeadStylesHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    public function execute()
    {
        print "
       <link rel='stylesheet' id='woo-tiempo-estimado' href='https://geekcel.com/wp-content/plugins/woo-tiempo-estimado/style.css' type='text/css' media='all' />
        ";
    }
}