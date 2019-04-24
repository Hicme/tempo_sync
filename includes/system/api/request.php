<?php

namespace system\api;

class Request{

    protected static $api_key = null;
    protected static $syndicate_key = null;
    protected static $request_type = null;
    protected static $curl = null;

    public static function set_request_type( $type )
    {
        self::$request_type = esc_attr( $type );
    }

    public static function get_responce()
    {
        return self::start_request();
    }

    private static function set_keys()
    {
        $status_api = $status_syn = false;

        if( is_null( self::$api_key ) ){
            if( $option = get_option( 'tempo_api', null ) ){
                self::$api_key = $option;
                $status_api = true;
            }
        }else{
            $status_api = true;
        }

        if( is_null( self::$syndicate_key ) ){
            if( $option = get_option( 'tempo_syndicate', null ) ){
                self::$syndicate_key = $option;
                $status_syn = true;
            }
        }else{
            $status_syn = true;
        }

        return ( $status_api && $status_syn );
    }

    private static function set_curl()
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

    public static function shutdown_curl()
    {
        if( ! is_null( self::$curl ) ){
            curl_close( self::$curl );
        }
    }

    private static function is_curl()
    {
        return function_exists( 'curl_version' );
    }

    private static function start_request()
    {

        if( self::set_curl() && self::set_keys() ){
            add_action( 'shutdown', [ __CLASS__ , 'shutdown_curl' ] );   

            return self::exec();
        }

        return false;

    }

    private static function exec()
    {

        self::request();

        if( curl_error( self::$curl ) ){
            return false;
        }else{
            if( ! empty( ( $responce = curl_exec( self::$curl ) ) ) ){
                return json_decode( $responce, true );
            }else{
                return false;
            }
        }

    }

    private static function request()
    {
        
        curl_setopt_array(
            self::$curl, 
            [
                CURLOPT_URL => 'https://api.ebizplatform.com/' . self::$request_type,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "APIKey: " . self::$api_key,
                    "SyndicateKey: " . self::$syndicate_key,
                    "Content-Type: application/json"
                ],
            ]
        );

    }

}