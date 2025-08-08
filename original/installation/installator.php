<?php

namespace WTE\Original\Installation;

use WTE\App\Installators\ConcreteInstallator;
use WTE\Original\Environment\Env;

Class Installator
{
    public function __construct()
    {
        register_activation_hook(
            Env::absolutePluginFilePath(), 
            [new ConcreteInstallator, 'install']
        );
    }
}