<?php

namespace WTE\Original\Events\Handler;

use WTE\Original\Utilities\ClassName;
use WTE\Original\Cache\MemoryCache;

abstract class EventHandler
{
    use ClassName;

    protected $event;
    protected $numberOfArguments = 1;
    protected $priority = 10;
    protected $cache;

    public static final function register($event)
    {
        $handler = new static($event);

        add_action(
            $event, 
            [$handler, 'execute'],
            $handler->priority,
            $handler->numberOfArguments
        );
    }

    public function __construct()
    {
        $this->cache = new MemoryCache;
    }
}