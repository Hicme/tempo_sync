<?php
/* Во мне есть список всех возможных запросов относительно продукта и его данных. Должен возвращать уже полученный результат */

namespace system\api;

class Product extends Request{

    public static function get_products()
    {
        self::$request_type = 'products';

        return self::get_responce();
    }

    public static function get_product( integer $product_id  )
    {
        self::$request_type = 'products/' . esc_attr( $product_id );

        return self::get_responce();
    }

    public static function get_product_items( integer $product_id )
    {
        self::$request_type = 'products/' . esc_attr( $product_id ) . '/items';

        return self::get_responce();
    }

    public static function get_product_documents( integer $product_id )
    {
        self::$request_type = 'products/' . esc_attr( $product_id ) . '/documents';

        return self::get_responce();
    }

    public static function get_product_media( integer $product_id )
    {
        self::$request_type = 'products/' . esc_attr( $product_id ) . '/media';

        return self::get_responce();
    }

    public static function get_product_categories( integer $product_id )
    {
        self::$request_type = 'products/' . esc_attr( $product_id ) . '/categories';

        return self::get_responce();
    }

    public static function get_prduct_articles( integer $product_id )
    {
        self::$request_type = 'products/' . esc_attr( $product_id ) . '/articles';

        return self::get_responce();
    }

    public static function get_product_related( integer $product_id )
    {
        self::$request_type = 'products/' . esc_attr( $product_id ) . '/related';

        return self::get_responce();
    }

}