<?php

namespace WTE\Original\Events\Registrator;

use WTE\Original\Collections\Collection;
use WTE\Original\Environment\Env;

Class EventsRegistrator
{
    protected $originalEvents = [];
    protected $customEvents = [];

    public function __construct()
    {
        (string) $eventsFile = 'events/actions.php';

        $this->customEvents = new Collection(
            require_once Env::directory() ."/app/{$eventsFile}"
        );

        $this->originalEvents = new Collection(
            require_once Env::directory() ."/original/{$eventsFile}"
        );
    }

    public function registerEvents()
    {
        foreach ($this->getAllEvents()->asArray() as $eventName => $eventHandlers) {
            $this->registerHandlersFor([
                'name' => (string) $eventName,
                'handlers' => (array) $eventHandlers
            ]);            
        }
    }

    protected function registerHandlersFor(array $event)
    {
        foreach ($event['handlers'] as $handlerClass) {
            call_user_func([$handlerClass, 'register'], $event['name']);
        }
    }

    protected function getAllEvents()
    {

        (object) $events = ((new Collection($this->originalEvents)));   

        foreach ($this->customEvents->asArray() as $event => $handlers) {
            foreach ($handlers as $handler) {
                $events->appendAsArray([$event => $handler]);
            }
            
        }

        return $events;
    }
    
}