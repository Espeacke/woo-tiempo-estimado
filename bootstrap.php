<?php

use WTE\Original\Autoloading\Autoloader;
use WTE\Original\Environment\Env;


require 'original/environment/env.php';

Env::set(__FILE__);

require Env::directory().'original/autoloading/autoloader.php';
require Env::directory().'vendor/autoload.php';

Autoloader::register();