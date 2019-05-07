<?php

namespace system;

final class StartUp
{

    use \system\Instance;
    
    public $product = null;

    public $categories = null;

    public $articles = null;



    public function __get( $key )
    {
		if ( in_array( $key, array( 'parser', 'api' ), true ) ) {
			return $this->$key();
		}
	}
    

    public function __construct()
    {

        $this->includes();

        do_action( 'p_loaded' );
        
    }


    public function is_request( $type )
    {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
    }

    
    private function includes()
    {
        
        \system\Post_Types::init();

        if( $this->is_request( 'cron' ) ){
            new \system\Cron();
        }

        if( $this->is_request( 'ajax' ) ){
            new \system\Ajax();
        }

        if( $this->is_request( 'admin' ) ){
            new \admin\Admin_Startup();
        }

    }



    public function parser()
    {
        return \system\parser\Processor::instance();
    }

    public function api()
    {
        return \system\api\Request::instance();
    }

}