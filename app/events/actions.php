<?php

return [
    'init' => [
        'WTE\\App\\Handlers\\ShortCodesRegistratorHandler',
        'WTE\\App\\Handlers\\WooCommerceCompatibilityHandler',
        'WTE\\App\\Handlers\\QuickEditHandler',
    ],
    'wp_enqueue_scripts' => [
        'WTE\\App\\Handlers\\LoadScriptsHandler',
    ],
    'wp_head' => [
        'WTE\\App\\Handlers\\HeadStylesHandler',
    ],
    'admin_menu' => [
        'WTE\\App\\Handlers\\OptionsPageHandler',
    ],
    'admin_enqueue_scripts' => [
        'WTE\\App\\Handlers\\AdminScriptsHanlder',
    ],
    'admin_head' => [
        'WTE\\App\\Handlers\\AdminHeadHandler',
    ],
    'admin_init' => [
        'WTE\\App\\Handlers\\OrderDateHandler',
    ],
];