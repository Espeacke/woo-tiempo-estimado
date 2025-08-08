<?php return array (
  'app' => 
  array (
    'id' => 'wte',
    'shortId' => 'wte',
    'pluginFileName' => 'woo-tiempo-estimado',
  ),
  'schema' => 
  array (
    'applicationDatabase' => 'WTE\\App\\Data\\Schema\\ApplicationDatabase',
  ),
  'directories' => 
  array (
    'app' => 
    array (
      'schema' => 'data/schema',
      'scripts' => 'scripts',
      'dashboard' => 'scripts/dashboard',
    ),
  ),
  'environment' => 'production',
);