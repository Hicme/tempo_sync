<?php

namespace admin\pages;

class Settings_Page
{

    protected $tab = false;
    protected $tab_links = [];


    public function __construct()
    {        
        if( empty( $_GET['tab'] ) ){
            $this->tab = 'general';
        }else{
            $this->tab = esc_attr( $_GET['tab'] );
        }

        $this->set_tab_links();
        $this->render_content();
    }

    public function set_tab_links()
    {
        $this->tab_links[ 'general' ] = [ 'title' => __( 'General', 'tempo' ), 'callback' => [ $this, 'general_content' ] ];
        $this->tab_links[ 'sync' ] = [ 'title' => __( 'Sync', 'tempo' ), 'callback' => [ $this, 'sync_content' ] ];

        apply_filters( 'set_tab_links', $this->tab_links );
    }

    public function get_tab_link()
    {
        ob_start();
        
        foreach( $this->tab_links as $link => $tab ){

            if( $this->tab == $link ){
                echo '<a href="admin.php?page=tempo_settings&tab='. $link .'" class="active">'. $tab['title'] .'</a>';
            }else{
                echo '<a href="admin.php?page=tempo_settings&tab='. $link .'" class="">'. $tab['title'] .'</a>';
            }

        }    

        echo ob_get_clean();
    }

    public function get_tab_content()
    {
        if( is_array( $this->tab_links[$this->tab]['callback'] ) && isset( $this->tab_links[$this->tab]['callback'][0] ) && is_object( $this->tab_links[$this->tab]['callback'][0] ) ){
            call_user_func( [ $this->tab_links[$this->tab]['callback'][0], $this->tab_links[$this->tab]['callback'][1] ] );
        }else{
            call_user_func( $this->tab_links[$this->tab]['callback'] );
        }
    }

    public function general_content()
    {
        include P_PATH . 'includes/admin/templates/content-general.php';
    }

    public function sync_content()
    {
        include P_PATH . 'includes/admin/templates/content-sync.php';
    }

    public function render_content()
    {
        include P_PATH . 'includes/admin/templates/template-settings.php';
    }


}