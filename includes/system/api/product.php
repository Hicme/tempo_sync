<?php

namespace system\api;

trait Product
{

    /**
     * Get all products form api. Paginated
     *
     * @param string $skip
     * @return array
     * @since 1.0.0
     */
    public function get_products( $skip = false )
    {
        
        if( $skip ){
            $this->set_request_type( 'products' . '?' .  $skip );
        }else{
            $this->set_request_type( 'products' );
        }

        return $this->get_responce();
    }

    /**
     * Get single product form api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product( int $product_id  )
    {
        $this->set_request_type( 'products/' . $product_id );

        return $this->get_responce();
    }

    /**
     * Get product attributes from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_attributes( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/attributes' );

        return $this->get_responce();
    }

    /**
     * Get product application meta from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_applications( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/applications' );

        return $this->get_responce();
    }

    /**
     * Get product items meta form api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_items( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/items' );

        return $this->get_responce();
    }

    /**
     * Get product documents from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_documents( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/documents' );

        return $this->get_responce();
    }

    /**
     * Get product media from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_media( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/media' );

        return $this->get_responce();
    }

    /**
     * Get product categories from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_categories( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/categories' );

        return $this->get_responce();
    }

    /**
     * Get porduct articles from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_articles( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/articles' );

        return $this->get_responce();
    }

    /**
     * Get related products for product from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_related( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/related' );

        return $this->get_responce();
    }

    /**
     * Get product modifers from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_modifers( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/modifiers' );

        return $this->get_responce();
    }

    /**
     * Get product tabds meta from api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_tabs( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/tabs' );

        return $this->get_responce();
    }

    /**
     * Get product controlgroups form api
     *
     * @param integer $product_id
     * @return array
     * @since 1.0.0
     */
    public function get_product_controlgroups( int $product_id )
    {
        $this->set_request_type( 'products/' . $product_id . '/controlgroups' );

        return $this->get_responce();
    }
}
