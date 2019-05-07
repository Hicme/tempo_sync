<?php

namespace system\api;

class Request extends Category{

    use \system\Instance;

    /**
     * Temp save API Key
     *
     * @var string
     * @since 1.0.0
     */
    protected $api_key = null;

    /**
     * Temp save Syndicate API Key
     *
     * @var string
     * @since 1.0.0
     */
    protected $syndicate_key = null;

    /**
     * What we want from API
     *
     * @var string
     * @since 1.0.0
     */
    protected $request_type = null;

    /**
     * Temp save of current curl
     *
     * @var object
     * @since 1.0.0
     */
    protected static $curl = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        add_action( 'shutdown', [ __CLASS__ , 'shutdown_curl' ] );
    }

    /**
     * Set request type
     *
     * @param string $type
     * @return void
     * @since 1.0.0
     */
    public function set_request_type( $type )
    {
        $this->request_type = esc_attr( $type );
    }

    /**
     * Return current request type
     *
     * @return string
     * @since 1.1.0
     */
    public function get_request_type()
    {
        return ( !empty( $this->request_type ) ? $this->request_type : false );
    }

    /**
     * Set API and Syndicate keys from db to temp variable
     *
     * @return boolean
     * @since 1.0.0
     */
    private function set_keys()
    {
        $status_api = $status_syn = false;

        if( is_null( $this->api_key ) ){
            if( $option = get_option( 'tempo_api', null ) ){
                $this->api_key = $option;
                $status_api = true;
            }
        }else{
            $status_api = true;
        }

        if( is_null( $this->syndicate_key ) ){
            if( $option = get_option( 'tempo_syndicate', null ) ){
                $this->syndicate_key = $option;
                $status_syn = true;
            }
        }else{
            $status_syn = true;
        }

        return ( $status_api && $status_syn );
    }

    /**
     * Check if curl enabled on serwer
     *
     * @return boolean
     * @since 1.0.0
     */
    private static function is_curl()
    {
        return function_exists( 'curl_version' );
    }

    /**
     * Set new curl
     *
     * @return boolean
     * @since 1.0.0
     */
    private function set_curl()
    {
        if( self::is_curl() ){
            if( is_null( self::$curl ) ){
                self::$curl = curl_init();
            }

            return true;

        }else{
            return false;
        }
    }

    /**
     * Return curl class
     *
     * @return object
     * @since 1.1.0
     */
    private function get_curl()
    {
        if( is_null( self::$curl ) ){
            $this->set_curl();
        }

        return self::$curl;
    }

    /**
     * Close current curl
     *
     * @return void
     * @since 1.0.0
     */
    public static function shutdown_curl()
    {
        if( ! is_null( $this->get_curl() ) ){
            curl_close( $this->get_curl() );
        }
    }

    /**
     * Start curl request
     *
     * @return mixed
     * @since 1.0.0
     */
    private function start_request()
    {
        if( $this->set_curl() && $this->set_keys() ){
            return $this->exec();
        }

        return false;

    }
    
    /**
     * Trigger request and return response
     *
     * @return mixed
     * @since 1.0.0
     */
    public function get_responce()
    {
        return $this->start_request();
    }

    /**
     * Get readble response from api
     *
     * @return array
     * @since 1.0.0
     */
    private function exec()
    {

        $this->request();

        if( curl_error( $this->get_curl() ) ){
            return false;
        }else{
            if( ! empty( ( $responce = curl_exec( $this->get_curl() ) ) ) ){
                return json_decode( $responce, true );
            }else{
                return false;
            }
        }

    }

    /**
     * Send request
     *
     * @return void
     * @since 1.0.0
     * @see https://docs.ebizplatform.com/
     */
    private function request()
    {
        
        curl_setopt_array(
            $this->get_curl(), 
            [
                CURLOPT_URL => 'https://api.ebizplatform.com/' . $this->get_request_type(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "APIKey: " . $this->api_key,
                    "SyndicateKey: " . $this->syndicate_key,
                    "Content-Type: application/json"
                ],
            ]
        );

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