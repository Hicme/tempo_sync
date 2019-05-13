<?php

namespace system\parser;

class Product{

    /**
     * Return array of products from api
     *
     * @param string $skip
     * @return array
     * @since 1.1.0
     */
    public function get_api_products( $skip = false )
    {
        if( $elements = tempo()->methods->get_products( $skip ) ){
            return $elements;
        }

        return false;
    }

    /**
     * Get meta from api and save or update product
     *
     * @return void
     * @since 1.1.0
     */
    public function do_product()
    {
        
        if( $product_id = get_product_by_tempo_id( $this->get_element_data( 'productId' ) ) ){
            $this->update_product( $product_id );
        }else{
            $this->insert_product();
        }
        
    }

    /**
     * Pritify meta from api
     *
     * @param array $datas
     * @return mixed
     * @since 1.1.0
     */
    public function get_value_from_meta( $datas )
    {
        if( isset( $datas[ 'value' ] ) ){
            return $datas[ 'value' ];
        }else{
            return '';
        }
    }

    /**
     * Create new post in WP
     *
     * @return void
     * @since 1.1.0
     */
    public function insert_product()
    {
        $wp_post = wp_insert_post( wp_slash( [
            'comment_status'   => 'closed',
            'ping_status'      => 'open',
            'post_author'      => '0',
            'post_content'     => $this->get_element_data( 'longDescription' ),
            'post_date'        => $this->get_element_data( 'lastModifiedOn' ),
            'post_date_gmt'    => $this->get_element_data( 'createdOn' ),
            'post_excerpt'     => $this->get_element_data( 'shortDescription' ),
            'post_name'        => $this->get_element_data( 'slug' ),
            'post_status'      => 'publish',
            'post_title'       => $this->get_element_data( 'productName' ),
            'post_type'        => 'tempes',
        ] ) );

        if( ! is_wp_error( $wp_post ) ){

            $this->write_log( 'Successfully created WP Post. ID =>' . $wp_post );
        
            update_post_meta( $wp_post, '_productId', $this->get_element_data( 'productId' ) );
            update_post_meta( $wp_post, '_productCode', $this->get_element_data( 'productCode' ) );
            update_post_meta( $wp_post, '_locale', $this->get_element_data( 'locale' ) );
            update_post_meta( $wp_post, '_type', $this->get_element_data( 'type' ) );
            update_post_meta( $wp_post, '_family', $this->get_element_data( 'family' ) );
            update_post_meta( $wp_post, '_brandName', $this->get_element_data( 'brandName' ) );
            update_post_meta( $wp_post, '_brandLogo', $this->get_element_data( 'brandLogo' ) );
            update_post_meta( $wp_post, '_pricingLabel', $this->get_element_data( 'pricingLabel' ) );
            update_post_meta( $wp_post, '_imageFull', $this->get_element_data( 'imageFull' ) );
            update_post_meta( $wp_post, '_imageThumb', $this->get_element_data( 'imageThumb' ) );
            update_post_meta( $wp_post, '_imageTiny', $this->get_element_data( 'imageTiny' ) );
            update_post_meta( $wp_post, '_createdOn', $this->get_element_data( 'createdOn' ) );
            update_post_meta( $wp_post, '_lastModifiedOn', $this->get_element_data( 'lastModifiedOn' ) );
    
    
    
            if( $this->get_element_data( 'attributes@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'attributes', true );
            }
    
            if( $this->get_element_data( 'related@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'related', true );
            }
    
            if( $this->get_element_data( 'applications@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'applications', true );
            }
    
            if( $this->get_element_data( 'articles@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'articles', true );
            }
    
            if( $this->get_element_data( 'documents@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'documents', true );
            }
    
            if( $this->get_element_data( 'media@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'media', true );
            }
            
            if( $this->get_element_data( 'categories@odata.navigationLink' ) ){
                if( $categories = tempo()->methods->get_product_categories( $this->get_element_data( 'productId' ) ) ){
                    // $this->update_element_data( 'categories', $this->get_meta_values( $categories ) );

                    $term_cat = [];

                    foreach( $this->get_value_from_meta( $categories ) as $cat ){
                        if( $wp_cat = get_category_by_tempo_id( $cat[ 'categoryId' ] ) ){
                            $term_cat[] = $wp_cat;
                            wp_set_object_terms( $wp_post, intval( $wp_cat ), 'tempes_cat', true );
                        }
                    }
                    
                    add_post_meta( $wp_post, '_categories', $term_cat );

                }
            }
    
            if( $this->get_element_data( 'items@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'items', true );
            }
    
            if( $this->get_element_data( 'modifiers@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'modifiers', true );
            }
    
            if( $this->get_element_data( 'tabs@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'tabs', true );
            }
    
            if( $this->get_element_data( 'controlgroups@odata.navigationLink' ) ){
                $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'controlgroups', true );
            }
    
            add_post_meta( $wp_post, '_sync_date', $this->get_start_time() );

        }else{
            $this->write_log( 'ERROR: While insert post. CODE: ' . $wp_post->get_error_message() );
        }

    }

    /**
     * Update existing post in WP
     *
     * @param integer $wp_post
     * @return void
     * @since 1.1.0
     */
    public function update_product( $wp_post )
    {
        wp_update_post( wp_slash( [
            'ID'               => $wp_post,
            'comment_status'   => 'closed',
            'ping_status'      => 'open',
            'post_author'      => '0',
            'post_content'     => $this->get_element_data( 'longDescription' ),
            'post_date'        => $this->get_element_data( 'lastModifiedOn' ),
            'post_date_gmt'    => $this->get_element_data( 'createdOn' ),
            'post_excerpt'     => $this->get_element_data( 'shortDescription' ),
            'post_name'        => $this->get_element_data( 'slug' ),
            'post_status'      => 'publish',
            'post_title'       => $this->get_element_data( 'productName' ),
            'post_type'        => 'tempes',
        ] ) );

        $this->write_log( 'Successfully updated WP Post. ID =>' . $wp_post );
        
        update_post_meta( $wp_post, '_productId', $this->get_element_data( 'productId' ) );
        update_post_meta( $wp_post, '_productCode', $this->get_element_data( 'productCode' ) );
        update_post_meta( $wp_post, '_locale', $this->get_element_data( 'locale' ) );
        update_post_meta( $wp_post, '_type', $this->get_element_data( 'type' ) );
        update_post_meta( $wp_post, '_family', $this->get_element_data( 'family' ) );
        update_post_meta( $wp_post, '_brandName', $this->get_element_data( 'brandName' ) );
        update_post_meta( $wp_post, '_brandLogo', $this->get_element_data( 'brandLogo' ) );
        update_post_meta( $wp_post, '_pricingLabel', $this->get_element_data( 'pricingLabel' ) );
        update_post_meta( $wp_post, '_imageFull', $this->get_element_data( 'imageFull' ) );
        update_post_meta( $wp_post, '_imageThumb', $this->get_element_data( 'imageThumb' ) );
        update_post_meta( $wp_post, '_imageTiny', $this->get_element_data( 'imageTiny' ) );
        update_post_meta( $wp_post, '_createdOn', $this->get_element_data( 'createdOn' ) );
        update_post_meta( $wp_post, '_lastModifiedOn', $this->get_element_data( 'lastModifiedOn' ) );



        if( $this->get_element_data( 'attributes@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'attributes' );
        }

        if( $this->get_element_data( 'related@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'related' );
        }

        if( $this->get_element_data( 'applications@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'applications' );
        }

        if( $this->get_element_data( 'articles@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'articles' );
        }

        if( $this->get_element_data( 'documents@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'documents' );
        }

        if( $this->get_element_data( 'media@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'media' );
        }
        
        if( $this->get_element_data( 'categories@odata.navigationLink' ) ){
            if( $categories = tempo()->methods->get_product_categories( $this->get_element_data( 'productId' ) ) ){
                // $this->update_element_data( 'categories', $this->get_meta_values( $categories ) );
                $term_cat = [];

                foreach( $this->get_value_from_meta( $categories ) as $cat ){
                    if( $wp_cat = get_category_by_tempo_id( $cat[ 'categoryId' ] ) ){
                        $term_cat[] = $wp_cat;
                        wp_set_object_terms( $wp_post, intval( $wp_cat ), 'tempes_cat', true );
                    }
                }
                
                update_post_meta( $wp_post, '_categories', $term_cat );

            }
        }

        if( $this->get_element_data( 'items@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'items' );
        }

        if( $this->get_element_data( 'modifiers@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'modifiers' );
        }

        if( $this->get_element_data( 'tabs@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'tabs' );
        }

        if( $this->get_element_data( 'controlgroups@odata.navigationLink' ) ){
            $this->set_product_meta( $wp_post, $this->get_element_data( 'productId' ), 'controlgroups' );
        }

        update_post_meta( $wp_post, '_sync_date', $this->get_start_time() );
    }

    /**
     * Try get and update meta from API datas
     *
     * @param integer $post_id
     * @param integer $tempo_id
     * @param string $type
     * @param boolean $method
     * @return integer
     * @since 1.2.0
     */
    public function set_product_meta( $post_id, $tempo_id, $type, $method = true )
    {

        if( empty( $type ) ){
            return false;
        }

        $meta_key = '_' . $type;

        switch ( $type ) {
            case 'attributes':
                $meta_value = tempo()->methods->get_product_attributes( $tempo_id );
                break;

            case 'related':
                $meta_value = tempo()->methods->get_product_related( $tempo_id );
                break;

            case 'applications':
                $meta_value = tempo()->methods->get_product_applications( $tempo_id );
                break;

            case 'articles':
                $meta_value = tempo()->methods->get_product_articles( $tempo_id );
                break;

            case 'documents':
                $meta_value = tempo()->methods->get_product_documents( $tempo_id );
                break;

            case 'media':
                $meta_value = tempo()->methods->get_product_media( $tempo_id );
                break;

            case 'items':
                $meta_value = tempo()->methods->get_product_items( $tempo_id );
                break;

            case 'modifiers':
                $meta_value = tempo()->methods->get_product_modifers( $tempo_id );
                break;

            case 'tabs':
                $meta_value = tempo()->methods->get_product_tabs( $tempo_id );
                break;

            case 'controlgroups':
                $meta_value = tempo()->methods->get_product_controlgroups( $tempo_id );
                break;
            
        }

        if( empty( $meta_value ) ){
            return false;
        }

        if( $method ){
            update_post_meta( $post_id, $meta_key, $this->get_value_from_meta( $meta_value ) );
        }else{
            add_post_meta( $post_id, $meta_key, $this->get_value_from_meta( $meta_value ), true );
        }

        return ( ! empty( $meta_value ) );
    }

}