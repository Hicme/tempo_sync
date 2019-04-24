<?php

namespace admin;

class Admin_Startup
{

    public function __construct()
    {
        add_action( 'admin_menu', [ $this, 'admin_menus' ], 9 );
        add_action( 'admin_head', [ $this, 'admin_menus_reorder' ] );
        add_action( 'init', [ $this, 'admin_inits' ] );
        add_action( 'admin_init', [ $this, 'admin_register_settings' ] );
        
        add_filter( 'parent_file', [ $this, 'highlight_taxonomy' ] );

    }

    public function admin_inits()
    {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets()
    {
        wp_enqueue_style( 'pstyles-admin', P_URL_FOLDER . 'assets/css/admin_styles.css', [], P_VERSION, 'screen' );

        wp_enqueue_script( 'pscripts-admin', P_URL_FOLDER . 'assets/js/admin_js.js', [], P_VERSION, true );

        wp_localize_script('pscripts-admin', 'TEMPOAJAX',
		    [
		        'url' => admin_url('admin-ajax.php'),
	            'ajax_nonce' => wp_create_nonce('tempo-admin-ajax-nonce'),
	        ]
	    );
    }

    public function admin_menus()
    {

        add_menu_page( __( 'Tepmo Sync', 'tempo' ), __( 'Tepmo Sync', 'tempo' ), 'manage_tempo', 'tempo', null, 'dashicons-products', '45' );
        add_submenu_page( 'tempo', __( 'Tempo Categories', 'tempo' ), __( 'Tempo Categories', 'tempo' ), 'manage_tempo', 'edit-tags.php?taxonomy=tempes_cat&post_type=tempes',false );
        add_submenu_page( 'tempo', __( 'Tempo Settings', 'tempo' ), __( 'Settings', 'tempo' ), 'manage_tempo', 'tempo_settings', [ $this, 'settings_page' ] );
    
    }

    

    public function admin_menus_reorder()
    {
        global $submenu;

        if( isset( $submenu['tempo'] ) ){
            unset( $submenu['tempo'][0] );

            $post_types = $submenu['tempo'][3];
            unset( $submenu['tempo'][3] );
            array_unshift( $submenu['tempo'], $post_types );
        }
    }


    public function highlight_taxonomy( $parent )
    {
        global $submenu_file, $current_screen, $pagenow;

        if( $current_screen->post_type == 'tempes' ){
            if ( $pagenow == 'edit-tags.php' ) {
                $submenu_file = 'edit-tags.php?taxonomy=tempes_cat&post_type=' . $current_screen->post_type;
            }

            $parent = 'tempo';
        }

        return $parent;
    }

    public function settings_page()
    {
        new \admin\pages\Settings_Page();
    }

    public function admin_register_settings()
    {
        \admin\Register_Settings::register_options();
    }
    
}