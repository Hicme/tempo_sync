<?php
/*
Я должен запускать парсер по крон заданию
*/

namespace system;

class Cron
{
    
    public function __construct()
    {
        add_action( 'continue_parsing', [ $this, 'trigger_parser' ], 10, 1 );
    }

    public function trigger_parser( $params )
    {
        tempo()->parser->start();
    }

}