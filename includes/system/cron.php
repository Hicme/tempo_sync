<?php

namespace system;

class Cron
{
    
    public function __construct()
    {
        add_action( 'continue_parsing', [ $this, 'trigger_parser' ], 10 );
    }

    public function trigger_parser()
    {
        tempo()->parser->start();
    }

}