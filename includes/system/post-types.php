<?php

namespace system;

class Post_Types
{

    public static function init()
    {
        add_action( 'setup_theme', [ __CLASS__, 'register_post_types' ], 5 );
        add_action( 'setup_theme', [ __CLASS__, 'register_taxonomies' ], 5 );

        add_action( 'init', [ __CLASS__, 'add_resources_rewrite' ], 100 );
        add_action( 'wp', [ __CLASS__, 'add_pseudo_post_type' ], 100 );

        add_filter( 'manage_posts_columns', [ __CLASS__, 'add_tempo_post_column' ], 10, 2 );

        add_action('manage_posts_custom_column', [ __CLASS__, 'add_tempo_post_column_content' ], 10, 2);

    }

    public static function register_taxonomies()
    {
        
        if ( ! is_blog_installed() ) {
			return;
		}

		if ( taxonomy_exists( 'tempes_cat' ) ) {
			return;
        }
        
        register_taxonomy( 
            'tempes_cat',
            [ 'tempes' ],
            [
                'hierarchical'          => true,
                'label'                 => __( 'Tempo Categories', 'tempes' ),
                'labels'                => [
                    'name'              => __( 'Tempo categories', 'tempes' ),
                    'singular_name'     => __( 'Tempo Category', 'tempes' ),
                    'menu_name'         => _x( 'Tempo Categories', 'Admin menu name', 'tempes' ),
                    'search_items'      => __( 'Search tempo categories', 'tempes' ),
                    'all_items'         => __( 'All tempo categories', 'tempes' ),
                    'parent_item'       => __( 'Parent tempo category', 'tempes' ),
                    'parent_item_colon' => __( 'Parent tempo category:', 'tempes' ),
                    'edit_item'         => __( 'Edit tempo category', 'tempes' ),
                    'update_item'       => __( 'Update tempo category', 'tempes' ),
                    'add_new_item'      => __( 'Add new tempo category', 'tempes' ),
                    'new_item_name'     => __( 'New tempo category name', 'tempes' ),
                    'not_found'         => __( 'No tempo categories found', 'tempes' ),
                ],
                'show_ui'               => true,
                'query_var'             => true,
                'capabilities'          => [
                    'manage_terms' => 'manage_tempes_terms',
                    'edit_terms'   => 'edit_tempes_terms',
                    'delete_terms' => 'delete_tempes_terms',
                    'assign_terms' => 'assign_tempes_terms',
                ],
                // 'rewrite'               => [
                //     'slug'         => 'products',
                //     'with_front'   => false,
                //     'hierarchical' => true,
                // ],
            ]
         );

    }

    public static function register_post_types()
    {
        if( ! is_blog_installed() || post_type_exists( 'tempes' ) ){
            return;
        }

        do_action( 'p_register_post_types' );

        $supports   = array( 'title', 'thumbnail' );

        $has_archive = true;

        register_post_type(
            'tempes',
            [
                'labels' => [
                    'name'                  => __( 'Products', 'tempo' ),
                    'singular_name'         => __( 'Tempo Product', 'tempo' ),
                    'all_items'             => __( 'All Tempo Products', 'tempo' ),
                    'menu_name'             => _x( 'Tempo Products', 'Admin menu name', 'tempo' ),
                    'add_new'               => __( 'Add New', 'tempo' ),
                    'add_new_item'          => __( 'Add new tempo product', 'tempo' ),
                    'edit'                  => __( 'Edit', 'tempo' ),
                    'edit_item'             => __( 'Edit tempo product', 'tempo' ),
                    'new_item'              => __( 'New tempo product', 'tempo' ),
                    'view_item'             => __( 'View tempo product', 'tempo' ),
                    'view_items'            => __( 'View tempo products', 'tempo' ),
                    'search_items'          => __( 'Search tempo products', 'tempo' ),
                    'not_found'             => __( 'No tempo products found', 'tempo' ),
                    'not_found_in_trash'    => __( 'No tempo products found in trash', 'tempo' ),
                    'parent'                => __( 'Parent tempo product', 'tempo' ),
                    'featured_image'        => __( 'Tempo Product image', 'tempo' ),
                    'set_featured_image'    => __( 'Set tempo product image', 'tempo' ),
                    'remove_featured_image' => __( 'Remove tempo product image', 'tempo' ),
                    'use_featured_image'    => __( 'Use as tempo product image', 'tempo' ),
                    'insert_into_item'      => __( 'Insert into tempo product', 'tempo' ),
                    'uploaded_to_this_item' => __( 'Uploaded to this tempo product', 'tempo' ),
                    'filter_items_list'     => __( 'Filter tempo products', 'tempo' ),
                    'items_list_navigation' => __( 'Tempo Products navigation', 'tempo' ),
                    'items_list'            => __( 'Tempo Products list', 'tempo' ),
                ],
                'description'         => '',
                'public'              => true,
                'show_ui'             => true,
                'capability_type'     => 'tempes',
                'map_meta_cap'        => true,
                'publicly_queryable'  => true,
                'exclude_from_search' => false,
                'hierarchical'        => false,
                'query_var'           => true,
                'rewrite'             => [
                    'slug' => 'products',
                    'with_front ' => false,
                ],
                'supports'            => $supports,
                'has_archive'         => 'products',
                'show_in_nav_menus'   => true,
                'show_in_rest'        => true,
                'show_in_menu'        => 'tempo',
            ]
        );

    }

    public static function add_resources_rewrite()
    {

        global $wp_rewrite;

        $archive_slug = "\x72\x65\x73\x6f\x75\x72\x63\x65\x73";

        $name = 'tempes';

        add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$name", 'top' );
        add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$name" . '&paged=$matches[1]', 'top' );

    }

    public static function add_pseudo_post_type()
    {

        global $wp_the_query;
        global $wp_post_types;
        global $wp;

        if( $wp->request == "\x72\x65\x73\x6f\x75\x72\x63\x65\x73" ){
            $res = new \stdClass();

            $res->labels = new \stdClass();

            $res->labels->name = __( "\x52\x65\x73\x6f\x75\x72\x63\x65\x73", 'tempes' );
            $res->has_archive = true;
            $res->name = "\x72\x65\x73\x6f\x75\x72\x63\x65\x73";

            $wp_post_types["\x72\x65\x73\x6f\x75\x72\x63\x65\x73"] = $res;
            
            $wp_the_query->query_vars['post_type'] = "\x72\x65\x73\x6f\x75\x72\x63\x65\x73";
        }

    }

    public static function add_tempo_post_column( $columns, $post_type )
    {

        if( $post_type == 'tempes' ){
            $save_date = $columns['date'];

            unset( $columns['date'] );

            $columns['api_id'] = 'API ID';

            $columns['attributes']  = 'Attributes';

            $columns['items']  = 'Items';

            $columns['date'] = $save_date;
        }

        return $columns;
    }

    public static function add_tempo_post_column_content( $column_name, $post_id )
    {
        if( $column_name == 'api_id' ){
            if( $api_id = get_post_meta( $post_id, '_productId', true ) ){
                echo '<span class="loaded_datas">'. $api_id .'</span>';
            }else{
                echo '<span class="not_loaded_datas"><span class="dashicons dashicons-no"></span></span>';
            }
        }

        if( $column_name == 'attributes' ){
            if( !empty( get_post_meta( $post_id, '_attributes', true ) ) ){
                echo '<span class="loaded_datas"><span class="dashicons dashicons-yes"></span></span>';
            }else{
                echo '<span class="not_loaded_datas"><a href="#" class="dashicons dashicons-no try_recync" data-post_id="' . $post_id . '" data-type="attributes"></a></span>';
            }
        }

        if( $column_name == 'items' ){
            if( !empty( get_post_meta( $post_id, '_items', true ) ) ){
                echo '<span class="loaded_datas"><span class="dashicons dashicons-yes"></span></span>';
            }else{
                echo '<span class="not_loaded_datas"><a href="#" class="dashicons dashicons-no try_recync" data-post_id="' . $post_id . '" data-type="items"></a></span>';
            }
        }
    }

}