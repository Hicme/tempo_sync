<?php
/*
я просто посылаю запрос на апи и проверяю пришел ли ответ и возвращаю запрос или false
*/

namespace system\api;

abstract class Request{

    protected static $api_key = null;
    protected static $syndicate_key = null;
    protected static $request_type = null;
    protected static $curl = null;

    private static function set_keys()
    {
        if( is_null( self::$api_key ) ){
            self::$api_key = get_option( 'tempo_api', null );
        }

        if( is_null( self::$syndicate_key ) ){
            self::$syndicate_key = get_option( 'tempo_syndicate', null );
        }
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

    private static function is_curl()
    {
        return function_exists( 'curl_version' );
    }

    public static function get_responce()
    {

        self::request();

        if( curl_error( self::$curl ) ){
            return false;
        }else{
            return curl_exec( self::$curl );
        }

    }

    private static function request()
    {
        self::set_keys();

        if( self::set_curl() && self::$api_key && self::$syndicate_key ){

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

}