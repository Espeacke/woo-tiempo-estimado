<?php

namespace WTE\Original\Environment;

Class Env
{
    private static $instance;
    private static $directory;
    private static $absoluteRootFilePath;
    private static $settings;

    public static function set($absoluteFilePath)
    {
        // can't use plugin_dir_path as this object is also used in a non-wordpress command line environment
        if (function_exists('plugin_dir_path')) {
            static::$directory = plugin_dir_path($absoluteFilePath);
        } else {
            static::$directory = dirname($absoluteFilePath).'/';
        }

        static::$absoluteRootFilePath = $absoluteFilePath;
        static::$settings = json_decode(json_encode(require static::appDirectory('settings').'default.php'));
    }

    public static function isWordPress()
    {
        return function_exists('add_action');
    }
    
    public static function settings()
    {
        return static::$settings;
    }

    public static function id()
    {
        return strtoupper(static::$settings->app->id);
    }

    public static function shortId()
    {
        return strtolower(static::$settings->app->shortId);
    }

    public static function idLowerCase()
    {
        return strtolower(static::id());   
    }

    public static function getWithPrefix($text)
    {
        return static::idLowerCase() . "_{$text}";   
    }
    

    public static function absolutePluginFilePath()
    {
        return static::$directory . strtolower(static::$settings->app->pluginFileName). '.php';
    }

    public static function directory()
    {
        return static::$directory;
    }

    public static function getAppDirectory($registeredDirectory)
    {
        return static::appDirectory(static::$settings->directories->app->{$registeredDirectory});
    }

    public static function appDirectory($subDirectory = '')
    {
        $subDirectory = $subDirectory? "$subDirectory/" : '';
        return static::directory() . "app/{$subDirectory}";
    }

    public static function originalDirectory($subDirectory = '')
    {
        $subDirectory = $subDirectory? "$subDirectory/" : '';
        return static::directory() . "original/{$subDirectory}";
    }

    public static function testsDirectory($subDirectory = '')
    {
        $subDirectory = $subDirectory? "$subDirectory/" : '';
        return static::directory() . "tests/{$subDirectory}";
    }

    public static function directoryURI()
    {
        if (function_exists('plugin_dir_url')) {
            return plugin_dir_url(static::$absoluteRootFilePath);
        }
    }

    public static function uploadsDirectory()
    {
        return wp_upload_dir()['basedir'];
    }

    public static function textDomain()
    {
        return strtolower(static::id());
    }

    public static function database()
    {
        // for development only, not used live
        return (object) [
            'name' => 'social'
        ];
    }

    public static function isRemoteTesting()
    {
        return defined('HIGHWAYPRO_BRIDGE_INSTALLED');
    }
}