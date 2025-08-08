<?php

use WTE\Original\Installation\Installator;
use WTE\Original\Events\Registrator\EventsRegistrator;

/*
Plugin Name: Woo Tiempo Estimado de Entrega, Versión 2
Plugin URI:   
Description:  Añade la posibildad de establecer un tiempo estimado de finalización de pedido  para WooCommerce.
Version:      2.2
Author:       Rafael Serna
Author URI:   
Text Domain:  woo-tiempo-estimado
Domain Path:  /woo-tiempo-estimado
Minimum supported version: 4.0
*/
require_once 'bootstrap.php';

(object) $installator = new Installator;

(object) $eventsRegistrator = new EventsRegistrator;

$eventsRegistrator->registerEvents();