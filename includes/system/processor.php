<?php

namespace system;

class Processor
{
    use \system\Instance;

    private $name = null;
    private $params = [];
    private $max_time = 0;
    private $start_time = 0;


    private $total_items = 0;
    private $current_iteration = 0;

    public function __construct()
    {
        $this->set_time();
    }

    public function get_name()
    {
        if( is_null( $this->name ) ){
            $this->name = wp_create_nonce( 'tempo_processor' );
        }

        return $this->name;
    }

    public function set_name( $name )
    {
        $this->name = esc_attr( $name );
    }

    public function set_state( array $params )
    {
        $this->params = $params;
    }

    public function get_state()
    {
        return $this->params;
    }

    private function set_time()
    {
        $serverlimit = ini_get( 'max_execution_time' );
        $this->max_time = ceil( $serverlimit / 2 );

        return $this->start_time = time();
    }

    private function time_out()
    {
        if( empty( $this->start_time ) ){
            return false;
        }

        if( ( time() - $this->max_time ) <= $this->max_time ){
            return true;
        }

        return false;
    }

    private function delete_state()
    {
        delete_transient( $this->get_name() );
    }

    private function save_state()
    {
        set_transient( $this->get_name(), $this->params, 60 * 60 * 24 );
    }

    private function update_state()
    {
        if( $params = get_transient( $this->get_name() ) ){
            $this->set_state( $params );
        }else{
            $params = [
                'start_time' => $this->set_time(),
                'product_id' => 0,
                'total_products' => 0
            ];

            $this->set_state( $params );
            $this->save_state();

        }
    }

    private function add_task()
    {
        wp_schedule_single_event( time() + 30 * 60, 'continue_parsing', [ 'name' => $this->get_name() ] );
    }


}