<?php

namespace WTE\App\Handlers;

use WTE\Original\Events\Handler\EventHandler;

Class AdminHeadHandler extends EventHandler
{
    protected $numberOfArguments = 1;
    protected $priority = 10;

    public function execute()
    {
        print '<style>
        #the-list #wte-eta {
            margin: 14px 0;
        }
        #the-list #wte-eta p {
                padding-left: 0 !important;
            }
        </style>';
    }
}