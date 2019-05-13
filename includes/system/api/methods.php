<?php

namespace system\api;

class Methods extends Request{

    use \system\Instance;
    use \system\api\Category;
    use \system\api\Product;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get api access and informations about api keys
     *
     * @return array
     * @since 1.0.0
     */
    public function check_connect()
    {
        $this->set_request_type( 'me' );

        return $this->get_responce();
    }

    /**
     * Send custom api request
     *
     * @param string $type
     * @return array
     * @since 1.0.0
     */
    public function custom_request( $type )
    {
        $this->set_request_type( $type );

        return $this->get_responce();
    }

}