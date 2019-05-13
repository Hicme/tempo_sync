<?php

namespace system\parser;

class Category extends Product{

    /**
     * Save or update categories from API datas
     *
     * @return void
     * @since 1.1.0
     */
    public function do_category()
    {
        if( $category_id = get_category_by_tempo_id( $this->get_element_data( 'categoryId' ) ) ){
            $this->update_category( $category_id );
        }else{
            $this->insert_category();
        }
    }

    /**
     * Return list of categories
     *
     * @param string $skip
     * @return mixed
     * @since 1.1.0
     */
    public function get_api_catalogs( $skip = false )
    {
        if( $elements = tempo()->methods->get_categories( $skip ) ){
            return $elements;
        }

        return false;
    }

    /**
     * Create new category in WP
     *
     * @return void
     * @since 1.1.0
     */
    public function insert_category()
    {
        $term = wp_insert_term(
            $this->get_element_data( 'categoryName' ),
            'tempes_cat',
            [
                'description' => $this->get_element_data( 'description' ),
                'parent'      => ( ( $cat_parent = $this->get_element_data( 'parentCategoryId' ) ) == 100 ? 0 : get_category_by_tempo_id( $cat_parent ) ),
            ]
        );

        if( ! is_wp_error( $term ) ){
            $term_id = $term['term_id'];

            $this->write_log( 'Successfully created WP Category. ID =>' . $term_id );

            add_term_meta( $term_id, '_categoryId', $this->get_element_data( 'categoryId' ) );
            add_term_meta( $term_id, '_parentCategoryId', $this->get_element_data( 'parentCategoryId' ) );
            add_term_meta( $term_id, '_image', $this->get_element_data( 'image' ) );
        }else{
            $this->write_log( 'ERROR: While insert category. CODE: ' . $term->get_error_message() );
        }

    }

    /**
     * Update existing category in WP
     *
     * @param integer $term_id
     * @return void
     * @since 1.1.0
     */
    public function update_category( $term_id )
    {
        $term = wp_update_term(
            $term_id,
            'tempes_cat',
            [
                'description' => $this->get_element_data( 'description' ),
                'parent'      => ( ( $cat_parent = $this->get_element_data( 'parentCategoryId' ) ) == 100 ? 0 : get_category_by_tempo_id( $cat_parent ) ),
            ]
        );

        if( ! is_wp_error( $term ) ){
            $term_id = $term['term_id'];

            $this->write_log( 'Successfully update WP Category. ID =>' . $term_id );

            update_term_meta( $term_id, '_categoryId', $this->get_element_data( 'categoryId' ) );
            update_term_meta( $term_id, '_parentCategoryId', $this->get_element_data( 'parentCategoryId' ) );
            update_term_meta( $term_id, '_image', $this->get_element_data( 'image' ) );

        }else{
            $this->write_log( 'ERROR: While update category. CODE: ' . $term->get_error_message() );
        }

    }

}