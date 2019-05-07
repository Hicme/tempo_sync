<?php

namespace system\api;

class Category extends Product{
    
    /**
     * Get all categories form api. Paginated
     *
     * @param boolean $skip
     * @return array
     * @since 1.0.0
     */
    public function get_categories( $skip = false )
    {
        if( $skip ){
            $this->set_request_type( 'categories' . '?' .  $skip );
        }else{
            $this->set_request_type( 'categories' );
        }

        return $this->get_responce();
    }

    /**
     * Get single category form api
     *
     * @param integer $category
     * @return array
     * @since 1.0.0
     */
    public function get_category( int $category )
    {
        $this->set_request_type( 'categories/' . $category );

        return $this->get_responce();
    
    }

    /**
     * Get sub categories to category from api
     *
     * @param integer $category
     * @return array
     * @since 1.0.0
     */
    public function get_sub_categories( int $category )
    {
        $this->set_request_type( 'categories/' . $category . '/subs' );

        return $this->get_responce();
    }

    /**
     * Get category products form api
     *
     * @param integer $category
     * @return array
     * @since 1.0.0
     */
    public function get_category_products( int $category )
    {
        $this->set_request_type( 'categories/' . $category . '/products' );

        return $this->get_responce();
    }

}
