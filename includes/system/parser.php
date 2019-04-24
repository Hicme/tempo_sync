<?php


namespace system;

class Parser extends Processor {

    private $wp_post = null;
    private $wp_post_term = [];

    public function __construct( $params = [] )
    {
        @set_time_limit(900);

        if( empty( $params ) ){
            $this->get_name();
        }else{
            $this->set_name( $params[0] );
        }

        parent::__construct();

        // $this->start();
    }

    private function create_post()
    {
        $this->wp_post = wp_insert_post( wp_slash( [
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

        $this->write_log( 'Successfully created WP Post. ID =>' . $this->wp_post );

        if( wp_set_object_terms( $this->wp_post, $this->wp_post_term, 'tempes_cat', false ) ){
            $this->write_log( '->WP Post assign to Taxonomy. ID =>' . implode( ', ', $this->wp_post_term ) );
        }
        
    }

    private function create_post_tax()
    {

        if( !empty( $this->wp_post_term ) ){
            $this->wp_post_term = [];
        }

        if( $categories = $this->get_element_data( 'categories' ) ){
            foreach( $categories[ 'value' ] as $category ){
                if( $exists = term_exists( $category[ 'categoryName' ], 'tempes_cat' ) ){
                    $this->wp_post_term[] = $exists[ 'term_id' ];
                }else{
                    $temp_term = wp_insert_term(
                        $category[ 'categoryName' ],
                        'tempes_cat',
                        [
            
                        ]
                    );

                    if( $temp_term ){
                        $this->wp_post_term[] = $temp_term[ 'term_id' ];
                    }
                }
            }
        }
    }

    private function update_post_metas()
    {
        update_post_meta( $this->wp_post, '_productCode', $this->get_element_data( 'productCode' ) );
        update_post_meta( $this->wp_post, '_locale', $this->get_element_data( 'locale' ) );
        update_post_meta( $this->wp_post, '_type', $this->get_element_data( 'type' ) );
        update_post_meta( $this->wp_post, '_family', $this->get_element_data( 'family' ) );
        update_post_meta( $this->wp_post, '_brandName', $this->get_element_data( 'brandName' ) );
        update_post_meta( $this->wp_post, '_brandLogo', $this->get_element_data( 'brandLogo' ) );
        update_post_meta( $this->wp_post, '_pricingLabel', $this->get_element_data( 'pricingLabel' ) );
        update_post_meta( $this->wp_post, '_imageFull', $this->get_element_data( 'imageFull' ) );
        update_post_meta( $this->wp_post, '_imageThumb', $this->get_element_data( 'imageThumb' ) );
        update_post_meta( $this->wp_post, '_imageTiny', $this->get_element_data( 'imageTiny' ) );
        update_post_meta( $this->wp_post, '_createdOn', $this->get_element_data( 'createdOn' ) );
        update_post_meta( $this->wp_post, '_lastModifiedOn', $this->get_element_data( 'lastModifiedOn' ) );

        if( $attr = $this->get_element_data( 'attributes' ) ){
            update_post_meta( $this->wp_post, '_attributes', $attr );
        }

        if( $related = $this->get_element_data( 'related' ) ){
            update_post_meta( $this->wp_post, '_related', $related );
        }

        if( $apps = $this->get_element_data( 'applications' ) ){
            update_post_meta( $this->wp_post, '_applications', $apps );
        }

        if( $articles = $this->get_element_data( 'articles' ) ){
            update_post_meta( $this->wp_post, '_articles', $articles );
        }

        if( $doc = $this->get_element_data( 'documents' ) ){
            update_post_meta( $this->wp_post, '_documents', $doc );
        }

        if( $media = $this->get_element_data( 'media' ) ){
            update_post_meta( $this->wp_post, '_media', $media );
        }

        if( $this->get_element_data( 'categories' ) ){
            update_post_meta( $this->wp_post, '_categories', $this->wp_post_term );
        }

        if( $items = $this->get_element_data( 'items' ) ){
            update_post_meta( $this->wp_post, '_items', $items );
        }

        if( $mod = $this->get_element_data( 'modifiers' ) ){
            update_post_meta( $this->wp_post, '_modifiers', $mod );
        }

        if( $tabs = $this->get_element_data( 'tabs' ) ){
            update_post_meta( $this->wp_post, '_tabs', $tabs );
        }

        if( $controls = $this->get_element_data( 'controlgroups' ) ){
            update_post_meta( $this->wp_post, '_controlgroups', $controls );
        }

        update_post_meta( $this->wp_post, '_sync_date', $this->get_start_time() );

        $this->write_log( '->Successfully added post meta. ID =>' . $this->wp_post );
    }

    private function do_elements()
    {
        $this->setup_element_data();

        $this->create_post_tax();
        $this->create_post();
        $this->update_post_metas();

        $this->save_process();
    }

    public function start()
    {

        //Get products from chache or api;

        if( $this->setup_elements() ){
            while( $this->can_processing() ){

                $this->do_elements();
    
            }
        }

    }

}