<?php

namespace WTE\Original\Autoloading;

use WTE\Original\Environment\Env;

Class Autoloader
{
    protected $fullyQualifiedClassName;
    protected $masterNamespace;

    public static function register()
    {
        spl_autoload_register(function($name){
            (object) $autoLoader = new Self($name);

            $autoLoader->loadClass();
        });
    }

    public function __construct($fullyQualifiedClassName)
    {
        (array) $namespaces = explode('\\', $fullyQualifiedClassName);
        (integer) $classNameindex = count($namespaces) - 1;
        (array) $lowerCaseNamespaces = array_map('lcfirst', $namespaces);
        (array) $capitalizedClass = ucfirst(
            $lowerCaseNamespaces[$classNameindex]
        );

        $lowerCaseNamespaces[$classNameindex] = $capitalizedClass;

        $this->fullyQualifiedClassName = implode('\\', $lowerCaseNamespaces);
        $this->masterNamespace = Env::id();
    }

    protected function loadClass()
    {
        if ($this->isOurClass()) {
            require_once $this->classFileName();
        }
    }

    protected function isOurClass()
    {
        (boolean) $firstNamespace = 0;

        return strpos(
                   strtolower($this->fullyQualifiedClassName), 
                   strtolower("{$this->masterNamespace}\\")
               ) === $firstNamespace;
    }

    protected function classFileName()
    {
        (string) $classNameWithNoMasterNamespace = substr(
            $this->fullyQualifiedClassName, 
            (strlen($this->masterNamespace) + 1)
        );

        return $this->findPath($classNameWithNoMasterNamespace);

    }

    protected function findPath($classNameWithNoMasterNamespace)
    {
        
        return strtolower(Env::directory().str_replace('\\', '/', $classNameWithNoMasterNamespace).'.php');
    }

    protected function transform($functionName)
    {
        return function ($pieces, $index, $piece) use ($functionName) {
            $pieces[$index] = $functionName($piece);

            return implode('/', $pieces);
        };
    }

}