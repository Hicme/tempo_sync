<?php

namespace system\api;

class Product extends Request{

    public static function get_products()
    {
        self::set_request_type( 'products' );

        return self::get_responce();
    }

    public static function get_product( int $product_id  )
    {
        self::set_request_type( 'products/' . $product_id );

        return self::get_responce();
    }

    public static function get_product_items( int $product_id )
    {
        self::set_request_type( 'products/' . $product_id . '/items' );

        return self::get_responce();
    }

    public static function get_product_documents( int $product_id )
    {
        self::set_request_type( 'products/' . $product_id . '/documents' );

        return self::get_responce();
    }

    public static function get_product_media( int $product_id )
    {
        self::set_request_type( 'products/' . $product_id . '/media' );

        return self::get_responce();
    }

    public static function get_product_categories( int $product_id )
    {
        self::set_request_type( 'products/' . $product_id . '/categories' );

        return self::get_responce();
    }

    public static function get_prduct_articles( int $product_id )
    {
        self::set_request_type( 'products/' . $product_id . '/articles' );

        return self::get_responce();
    }

    public static function get_product_related( int $product_id )
    {
        self::set_request_type( 'products/' . $product_id . '/related' );

        return self::get_responce();
    }

}