<?php

namespace system\parser;

class Product{

    /**
     * Return array of products from API
     *
     * @param string $skip
     * @return array
     * @since 1.1.0
     */
    public function get_api_products( $skip = false )
    {
        if( $elements = tempo()->api->get_products( $skip ) ){
            return $elements;
        }

        return false;
    }

    /**
     * Get meta form API and save or update product
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
     * Pritify meta form API
     *
     * @param array $datas
     * @return mixed
     * @since 1.1.0
     */
    public function get_meta_values( $datas )
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
                if( $attr = tempo()->api->get_product_attributes( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_attributes', $this->get_meta_values( $attr ) );
                }
            }
    
            if( $this->get_element_data( 'related@odata.navigationLink' ) ){
                if( $related = tempo()->api->get_product_related( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_related', $this->get_meta_values( $related ) );
                }
            }
    
            if( $this->get_element_data( 'applications@odata.navigationLink' ) ){
                if( $applications = tempo()->api->get_product_applications( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_applications', $this->get_meta_values( $applications ) );
                }
            }
    
            if( $this->get_element_data( 'articles@odata.navigationLink' ) ){
                if( $articles = tempo()->api->get_prduct_articles( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_articles', $this->get_meta_values( $articles ) );
                }
            }
    
            if( $this->get_element_data( 'documents@odata.navigationLink' ) ){
                if( $documents = tempo()->api->get_product_documents( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_documents', $this->get_meta_values( $documents ) );
                }
            }
    
            if( $this->get_element_data( 'media@odata.navigationLink' ) ){
                if( $media = tempo()->api->get_product_media( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_media', $this->get_meta_values( $media ) );
                }
            }
            
            if( $this->get_element_data( 'categories@odata.navigationLink' ) ){
                if( $categories = tempo()->api->get_product_categories( $this->get_element_data( 'productId' ) ) ){
                    // $this->update_element_data( 'categories', $this->get_meta_values( $categories ) );

                    $term_cat = [];

                    foreach( $this->get_meta_values( $categories ) as $cat ){
                        if( $wp_cat = get_category_by_tempo_id( $cat ) ){
                            $term_cat[] = $wp_cat;
                            wp_set_object_terms( $wp_post, $wp_cat, 'tempes_cat' );
                        }
                    }
                    
                    update_post_meta( $wp_post, '_categories', $term_cat );

                }
            }
    
            if( $this->get_element_data( 'items@odata.navigationLink' ) ){
                if( $items = tempo()->api->get_product_items( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_items', $this->get_meta_values( $items ) );
                }
            }
    
            if( $this->get_element_data( 'modifiers@odata.navigationLink' ) ){
                if( $modifiers = tempo()->api->get_product_modifers( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_modifiers', $this->get_meta_values( $modifiers ) );
                }
            }
    
            if( $this->get_element_data( 'tabs@odata.navigationLink' ) ){
                if( $tabs = tempo()->api->get_product_tabs( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_tabs', $this->get_meta_values( $tabs ) );
                }
            }
    
            if( $this->get_element_data( 'controlgroups@odata.navigationLink' ) ){
                if( $controlgroups = tempo()->api->get_product_controlgroups( $this->get_element_data( 'productId' ) ) ){
                    update_post_meta( $wp_post, '_controlgroups', $this->get_meta_values( $controlgroups ) );
                }
            }
    
            update_post_meta( $wp_post, '_sync_date', $this->get_start_time() );

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
            if( $attr = tempo()->api->get_product_attributes( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_attributes', $this->get_meta_values( $attr ) );
            }
        }

        if( $this->get_element_data( 'related@odata.navigationLink' ) ){
            if( $related = tempo()->api->get_product_related( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_related', $this->get_meta_values( $related ) );
            }
        }

        if( $this->get_element_data( 'applications@odata.navigationLink' ) ){
            if( $applications = tempo()->api->get_product_applications( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_applications', $this->get_meta_values( $applications ) );
            }
        }

        if( $this->get_element_data( 'articles@odata.navigationLink' ) ){
            if( $articles = tempo()->api->get_prduct_articles( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_articles', $this->get_meta_values( $articles ) );
            }
        }

        if( $this->get_element_data( 'documents@odata.navigationLink' ) ){
            if( $documents = tempo()->api->get_product_documents( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_documents', $this->get_meta_values( $documents ) );
            }
        }

        if( $this->get_element_data( 'media@odata.navigationLink' ) ){
            if( $media = tempo()->api->get_product_media( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_media', $this->get_meta_values( $media ) );
            }
        }
        
        if( $this->get_element_data( 'categories@odata.navigationLink' ) ){
            if( $categories = tempo()->api->get_product_categories( $this->get_element_data( 'productId' ) ) ){
                // $this->update_element_data( 'categories', $this->get_meta_values( $categories ) );
                $term_cat = [];

                foreach( $this->get_meta_values( $categories ) as $cat ){
                    if( $wp_cat = get_category_by_tempo_id( $cat ) ){
                        $term_cat[] = $wp_cat;
                        wp_set_object_terms( $wp_post, $wp_cat, 'tempes_cat' );
                    }
                }
                
                update_post_meta( $wp_post, '_categories', $term_cat );

            }
        }

        if( $this->get_element_data( 'items@odata.navigationLink' ) ){
            if( $items = tempo()->api->get_product_items( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_items', $this->get_meta_values( $items ) );
            }
        }

        if( $this->get_element_data( 'modifiers@odata.navigationLink' ) ){
            if( $modifiers = tempo()->api->get_product_modifers( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_modifiers', $this->get_meta_values( $modifiers ) );
            }
        }

        if( $this->get_element_data( 'tabs@odata.navigationLink' ) ){
            if( $tabs = tempo()->api->get_product_tabs( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_tabs', $this->get_meta_values( $tabs ) );
            }
        }

        if( $this->get_element_data( 'controlgroups@odata.navigationLink' ) ){
            if( $controlgroups = tempo()->api->get_product_controlgroups( $this->get_element_data( 'productId' ) ) ){
                update_post_meta( $wp_post, '_controlgroups', $this->get_meta_values( $controlgroups ) );
            }
        }

        update_post_meta( $wp_post, '_sync_date', $this->get_start_time() );
    }

}