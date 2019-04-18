<?php

namespace system\api;

class Category extends Request{
    
    public static function get_categories()
    {
        self::set_request_type( 'categories' );

        return self::get_responce();
    }

    public static function get_category( int $category )
    {
        self::set_request_type( 'categories/' . $category );

        return self::get_responce();
    
    }

    public static function get_sub_categories( int $category )
    {
        self::set_request_type( 'categories/' . $category . '/subs' );

        return self::get_responce();
    }

    public static function get_category_products( int $category )
    {
        self::set_request_type( 'categories/' . $category . '/products' );

        return self::get_responce();
    }

}
