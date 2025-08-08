<?php

namespace WTE\Original\Shortcodes;

use WTE\Original\Environment\Env;
use WTE\Original\Collections\JSONMapper;

Abstract Class Shortcode
{
    abstract protected function map();

    abstract public function render();

    protected $properties;
    protected $content;

    public static function name()
    {
        return strtolower(Env::id().'_'.static::$name);   
    }

    final public static function handle()
    {
        (object) $shortCodeClass = get_called_class();

        return function($attributes, $content) use ($shortCodeClass) {
            (object) $shortcode = new $shortCodeClass(
                array_filter((array) $attributes), 
                $content
            );

            return $shortcode->render();
        };   
    }

    public function __construct(Array $attributes, $content = '')
    {
        $this->content = $content;

        $this->properties = (new JSONMapper($this->map()))->map(json_encode($attributes));

        if (method_exists($this, 'setUp')) $this->setUp();
    }

    public function getProperties()
    {
        return $this->properties;
    }

}
