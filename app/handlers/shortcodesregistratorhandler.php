<?php

namespace WTE\App\Handlers;

use WTE\App\WTE\Shortcodes\EstimatedUnitsShortCode;
use WTE\Original\Events\Handler\EventHandler;

Class ShortCodesRegistratorHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    public function execute()
    {
        add_shortcode(EstimatedUnitsShortCode::name(), EstimatedUnitsShortCode::handle());
    }
}