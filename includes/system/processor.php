<?php

namespace system;

class Processor
{
    private $debug = false;

    private $operation_name = null;      //unique process name for savig and reciving datas
    private $operation_elements = [];    //all  elements from api
    private $iteration_count = null;
    private $total_count = null;      
    private $page_num = 0;
    private $current_page_link = false;
    private $next_page_link = false;
    private $total_processed = 0;
    private $current_element = -1;       //current iteration element number
    private $current_element_data = [];  //current iteration element datas
    private $start_time = null;          //whole process start time
    private $iteration_time = null;      //iteration start time
    private $max_time = null;            //php max execution time
    private $logging = [];               //log of whole process
    private $current_operation_log = ''; //current operation fast log
    private $log_iteration = 0;



    public function __construct()
    {
        $this->set_debug();
    }



    private function set_debug()
    {
        $this->debug = get_option( 'tempo_debug', false );
    }

    public function get_name()
    {
        if( is_null( $this->operation_name ) ){
            $this->operation_name = generate_string();
        }

        return $this->operation_name;
    }

    public function set_name( $name )
    {
        $this->operation_name = esc_attr( $name );
    }

    public function write_log( $datas = '' )
    {
        if( empty( $this->logging ) ){
            if( $old_log = get_transient( $this->get_name() . '_log' ) ){
                $this->logging = $old_log;
            }
        }

        if( !empty( $datas ) ){
            $this->logging[ $this->log_iteration ] = [ 'time' => time(), 'message' => $datas ];
        }else{
            $this->logging[ $this->log_iteration ] = [ 'time' => time(), 'message' => $this->current_operation_log ];
        }

        $this->log_iteration++;
        
    }

    public function get_cached_elements()
    {
        return get_transient( $this->get_name() . '_elements' );
    }

    public function get_cached_datas()
    {
        return get_transient( $this->get_name() );
    }

    public function cache_elements()
    {
        set_transient( $this->get_name() . '_elements', $this->operation_elements, 60 * 60 * 24 );

    }

    public function get_api_elements( $skip = false )
    {
        if( $elements = \system\api\Product::get_products( $skip ) ){
            return $elements;
        }

        return false;
    }

    public function setup_elements()
    {

        //check info about process
        if( $cached_datas = $this->get_cached_datas() ){
            //if we has prev operation
            $this->start_time        = $cached_datas[ 'start_time' ];
            $this->current_element   = $cached_datas[ 'current_element' ];
            $this->iteration_count   = $cached_datas[ 'iteration_count' ] + 1;
            $this->total_count       = $cached_datas[ 'total_count' ] + 1;
            $this->page_num          = $cached_datas[ 'page_num' ];
            $this->current_page_link = $cached_datas[ 'current_page_link' ];
            $this->next_page_link    = $cached_datas[ 'next_page_link' ];
            $this->total_processed   = $cached_datas[ 'total_processed' ];
            $this->log_iteration     = $cached_datas[ 'log_iteration' ];
            
            //Lets know that all works
            $this->write_log( 'Found previus operation. Was loaded from cache.' );
        }else{

            //check api response

            if( $api_return = $this->get_api_elements() ){
                $this->write_log( 'Elements was found in API request.' );
            }else{
                $this->write_log( 'Something went wrong. API return FALSE.' );

                return false;
            }

        }

        return true;
    }

    public function set_time()
    {
        if( is_null( $this->start_time ) ){
            $this->start_time = time();
        }

        $this->iteration_time = time();
    }

    public function set_max_time()
    {
        $serverlimit = ini_get( 'max_execution_time' );
        $this->max_time = $this->iteration_time + ceil( $serverlimit / 1.2 );
    }

    public function add_cron_task()
    {
        update_option( 'tempo_current_operation', [ 'name' => $this->get_name(), 'status' => 'timeout' ] );
        wp_schedule_single_event( time() + 60, 'continue_parsing', [ $this->get_name() ] );
    }

    public function clear_temp()
    {

        if( !$this->debug ){
            delete_transient( $this->get_name() . '_elements' );
            delete_transient( $this->get_name() );
            delete_transient( $this->get_name() . '_log' );

            for ( $i=0; $i < $this->iteration_count; $i++ ) { 
                delete_transient( $this->get_name() . '_' . $i );
            }
        }

        //need set this meta for stop js actions
        set_transient( $this->get_name() . '_done' , [ 'time' => date( 'H:i:s', ( time() - $this->start_time ) ) ] );
        delete_option( 'tempo_current_operation' );

    }

    public function get_start_time()
    {
        return $this->start_time;
    }

    public function is_time_valid()
    {

        $processing_time = false;

        if( is_null( $this->iteration_time ) ){
            $this->set_time();
        }

        if( is_null( $this->max_time ) ){
            $this->set_max_time();
        }

        if( time() <= $this->max_time ){
            $processing_time = true;
        }else{
            //if we has no time for script - try to resume while wp cron
            $this->add_cron_task();
        }

        return $processing_time;

    }

    private function try_setup_elements()
    {
        $skip = false;

        //Looks like it new start or cron new cron actions.
        if( empty( $this->operation_elements ) ){
            if( $this->current_page_link ){
                $url = parse_url( $this->current_page_link );
                $skip = $url[ 'query' ];
            }
        }else{
            if( $this->next_page_link ){
                $url = parse_url( $this->next_page_link );
                $skip = $url[ 'query' ];
            }
        }


        if( $responce = $this->get_api_elements( $skip ) ){
            $this->total_count = $responce['@odata.count'];

            if( $this->next_page_link ){
                $this->current_page_link = $this->next_page_link;
            }

            if( array_key_exists( '@odata.nextLink', $responce ) ){
                $this->next_page_link = $responce['@odata.nextLink'];
                $this->page_num++;

            }else{
                $this->next_page_link = false;
            }

            $this->operation_elements = $responce['value'];

            $this->iteration_count = count( $this->operation_elements );

            return true;
        }

        return false;

    }

    public function is_elements_valid()
    {
        $processing_elements = false;

        if( empty( $this->operation_elements ) ){
            $this->try_setup_elements();
        }

        if( !empty( $this->operation_elements ) ){

            if( $this->current_element + 1 < $this->iteration_count ){
                $this->current_element++;
                $this->total_processed++;
                $processing_elements = true;

            }elseif( $this->current_element + 1 == $this->iteration_count && $this->iteration_count > 0 ){

                if( !is_null( $this->next_page_link ) ){
                    //current page was iterated and now need go to next

                    $this->current_element = 0;

                    $this->write_log( 'Try to load next API page.' );

                    if( $this->try_setup_elements() ){
                        $processing_elements = true;
                    }

                }else{
                    $this->write_log( 'All done.' );

                    //all elements processed and we need clear temp datas
                    $this->clear_temp();
                }
                
            }
            
        }

        return $processing_elements;
    }

    public function can_processing()
    {
        return ( $this->is_time_valid() && $this->is_elements_valid() );
    }

    public function save_process()
    {
        //Save log
        set_transient( $this->get_name() . '_log', $this->logging, 60 * 60 * 24 );

        //Save status and etc
        $params = [
            'start_time'         => $this->start_time,
            'current_element'    => $this->current_element,
            'iteration_count'    => $this->iteration_count - 1,
            'total_count'        => $this->total_count - 1,
            'total_processed'    => $this->total_processed,
            'page_num'           => $this->page_num,
            'current_page_link'  => $this->current_page_link,
            'next_page_link'     => $this->next_page_link,
            'log_iteration'      => $this->log_iteration,
        ];

        set_transient( $this->get_name(), $params, 60 * 60 * 24 );

        if( !empty( $this->current_element_data ) ){
            // set_transient( $this->get_name() . '_' . $this->current_element, $this->current_element_data, 60 * 60 * 24 );
        }

    }

    public function setup_element_data()
    {
        if( !empty( $this->current_element_data ) ){
            $this->current_element_data = [];
        }

        $temp_element = $this->operation_elements[ $this->current_element ];

        $this->write_log( 'Start to setup new element API iD = '. $temp_element[ 'productId' ] .'.' );

        if( array_key_exists( 'attributes@odata.navigationLink' , $temp_element ) ){
            if( $attr = \system\api\Product::get_product_attributes( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'attributes@odata.navigationLink' ] );
                $temp_element[ 'attributes' ] = $attr;

                // $this->write_log( '->Successfully loaded element Attributes.' );
            }
        }

        if( array_key_exists( 'related@odata.navigationLink' , $temp_element ) ){
            if( $related = \system\api\Product::get_product_related( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'related@odata.navigationLink' ] );
                $temp_element[ 'related' ] = $related;

                // $this->write_log( '->Successfully loaded element Related.' );
            }
        }
        
        if( array_key_exists( 'applications@odata.navigationLink' , $temp_element ) ){
            if( $applications = \system\api\Product::get_product_applications( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'applications@odata.navigationLink' ] );
                $temp_element[ 'applications' ] = $applications;

                // $this->write_log( '->Successfully loaded element Applications.' );
            }
        }

        if( array_key_exists( 'articles@odata.navigationLink' , $temp_element ) ){
            if( $articles = \system\api\Product::get_prduct_articles( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'articles@odata.navigationLink' ] );
                $temp_element[ 'articles' ] = $articles;

                // $this->write_log( '->Successfully loaded element Articles.' );
            }
        }

        if( array_key_exists( 'documents@odata.navigationLink' , $temp_element ) ){
            if( $documents = \system\api\Product::get_product_documents( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'documents@odata.navigationLink' ] );
                $temp_element[ 'documents' ] = $documents;

                // $this->write_log( '->Successfully loaded element Documents.' );
            }
        }

        if( array_key_exists( 'media@odata.navigationLink' , $temp_element ) ){
            if( $media = \system\api\Product::get_product_media( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'media@odata.navigationLink' ] );
                $temp_element[ 'media' ] = $media;

                // $this->write_log( '->Successfully loaded element Media.' );
            }
        }
        
        if( array_key_exists( 'categories@odata.navigationLink' , $temp_element ) ){
            if( $categories = \system\api\Product::get_product_categories( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'categories@odata.navigationLink' ] );
                $temp_element[ 'categories' ] = $categories;

                // $this->write_log( '->Successfully loaded element Categories.' );
            }
        }

        if( array_key_exists( 'items@odata.navigationLink' , $temp_element ) ){
            if( $items = \system\api\Product::get_product_items( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'items@odata.navigationLink' ] );
                $temp_element[ 'items' ] = $items;

                // $this->write_log( '->Successfully loaded element Items.' );
            }
        }

        if( array_key_exists( 'modifiers@odata.navigationLink' , $temp_element ) ){
            if( $modifiers = \system\api\Product::get_product_modifers( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'modifiers@odata.navigationLink' ] );
                $temp_element[ 'modifiers' ] = $modifiers;

                // $this->write_log( '->Successfully loaded element Modifiers.' );
            }
        }

        if( array_key_exists( 'tabs@odata.navigationLink' , $temp_element ) ){
            if( $tabs = \system\api\Product::get_product_tabs( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'tabs@odata.navigationLink' ] );
                $temp_element[ 'tabs' ] = $tabs;

                // $this->write_log( '->Successfully loaded element Tabs.' );
            }
        }

        if( array_key_exists( 'controlgroups@odata.navigationLink' , $temp_element ) ){
            if( $controlgroups = \system\api\Product::get_product_controlgroups( $temp_element[ 'productId' ] ) ){
                unset( $temp_element[ 'controlgroups@odata.navigationLink' ] );
                $temp_element[ 'controlgroups' ] = $controlgroups;

                // $this->write_log( '->Successfully loaded element Controlgroups.' );
            }
        }

        $this->current_element_data = $temp_element;
    }

    public function get_element_data( $data_item )
    {
        if( array_key_exists( $data_item, $this->current_element_data ) ){
            return $this->current_element_data[ $data_item ];
        }else{
            return false;
        }
        
    }

    public function set_current_operation()
    {
        update_option( 'tempo_current_operation', [ 'name' => $this->get_name(), 'status' => 'online' ] );
    }
    
}