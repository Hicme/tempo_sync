<?php

namespace system\parser;

class Processor extends Category{

    use \system\Instance;

    /**
     * If bedug is opened
     *
     * @var boolean
     * @since 1.1.0
     */
    private $debug = false;

    /**
     * Operation status for logging
     *
     * @var string
     */
    private $operation_status = '';
    
    /**
     * Type of current operation
     * 
     * @var string
     * @since 1.1.0
     */
    private $operation_type = false;

    /**
     * Temp variable for api elements
     *
     * @var array
     * @since 1.0.0
     */
    private $temp_elements = [];

    /**
     * Temp for array iteration
     *
     * @var integer
     * @since 1.1.0
     */
    private $iterated = 0;

    /**
     * Total elements count
     *
     * @var integer
     * @since 1.1.0
     */
    private $total_iterated = 0;

    /**
     * Total elements from API
     *
     * @var integer
     */
    private $total_elements = 0;

    /**
     * Start operation timemark
     *
     * @var integer
     * @since 1.0.0
     */
    private $start_time = null;

    /**
     * Start iteration timemark
     *
     * @var integer
     * @since 1.0.0
     */
    private $iteration_time = null;

    /**
     * Max available time for php
     *
     * @var integer
     */
    private $max_time = null;

    /**
     * Curretnt log
     *
     * @var array
     * @since 1.0.0
     */
    private $logging = [];

    /**
     * Save log name for current operation
     *
     * @var string
     * @since 1.1.0
     */
    private $log_name = '';


    /**
     * Class constructor
     */
    public function __construct()
    {
        @set_time_limit(500);

        $this->set_debug();
    }

    /**
     * Start exit actions
     *
     * @return void
     * @since 1.1.0
     */
    public function exit( $string = '' )
    {

        do_action( 'tempo_sync_exit' );
        if( ! empty( $string ) ){
            $this->write_log( 'Script exit. Cause: ' . $string );
        }else{
            $this->write_log( 'Script exit.' );
        }
        
        $this->save_log();
        exit;
    }

    /**
     * Set debug mode on or off
     *
     * @return boolean
     * @since 1.0.0
     */
    private function set_debug()
    {
        $this->debug = get_option( 'tempo_debug', false );
    }

    /**
     * Set operation status
     *
     * @param string $status
     * @return void
     * @since 1.1.0
     */
    public function set_status( $status )
    {
        $this->operation_status = $status;
    }

    /**
     * Return current status
     *
     * @return string
     * @since 1.1.0
     */
    public function get_status()
    {
        return ( !empty( $this->operation_status ) ? $this->operation_status : false );
    }

    /**
     * Set type of operation
     *
     * @param string $type
     * @since 1.1.0
     */
    public function set_type( $type )
    {
        $this->operation_type = $type;
    }

    /**
     * Get type of operation
     *
     * @return string
     * @since 1.1.0
     */
    public function get_type()
    {
        return ( !empty( $this->operation_type ) ? $this->operation_type : false );
    }

    /**
     * Manual set time
     *
     * @param integer $time
     * @return void
     * @since 1.1.0
     */
    public function set_start_time( $time )
    {
        $this->start_time = $time;
    }

    /**
     * Set sync start time 
     *
     * @return void
     * @since 1.0.0
     */
    public function start_time()
    {
        if( is_null( $this->start_time ) ){
            $this->start_time = time();
        }

        $this->iteration_time = time();
    }

    /**
     * Return started time
     *
     * @return int
     * @since 1.0.0
     */
    public function get_start_time()
    {
        return $this->start_time;
    }

    /**
     * Sets the maximum possible execution time with a margin to complete all operations
     *
     * @return void
     * @since 1.0.0
     */
    public function set_max_time()
    {
        $serverlimit = ini_get( 'max_execution_time' );
        $this->max_time = $this->iteration_time + ceil( $serverlimit / 1.2 );

        if( $this->debug ){
            $this->write_log( 'Max php script time set: ' . $this->max_time );
        }
    }

    /**
     * If operation very long we need set the wp cron task
     *
     * @return void
     * @since 1.0.0
     */
    public function set_cron_task()
    {
        wp_schedule_single_event( time() + 60, 'continue_parsing' );

        if( $this->debug ){
            $this->write_log( 'Added WPCron task.' );
        }
    }

    /**
     * Set api categoories as temp elements paginate if needed
     *
     * @param string $skip
     * @return boolean
     * @since 1.1.0
     */
    public function set_category_as_elements( $skip = false )
    {
        if( $categories = $this->get_api_catalogs( $skip ) ){

            $this->temp_elements = array_merge( $this->temp_elements, $categories['value'] );

            if( array_key_exists( '@odata.nextLink', $categories ) ){
                $url = parse_url( $categories['@odata.nextLink'] );

                if( $this->debug ){
                    $this->write_log( 'Found paginate category. Link set: ' . esc_url( $categories['@odata.nextLink'] ) );
                }

                $this->set_category_as_elements( $url[ 'query' ] );
            }

            return true;
        }else{

            $this->write_log( 'Unable to get categories.' );
            $this->set_status( 'error' );

            return false;
        }
    }

    /**
     * Set api products as temp elements paginate if needed
     *
     * @param string $skip
     * @return boolean
     * @since 1.1.0
     */
    public function set_products_as_elements( $skip = false )
    {
        if( $products = $this->get_api_products( $skip ) ){

            $this->temp_elements = array_merge( $this->temp_elements, $products['value'] );

            if( array_key_exists( '@odata.nextLink', $products ) ){
                $url = parse_url( $products['@odata.nextLink'] );

                if( $this->debug ){
                    $this->write_log( 'Found paginate products. Link set: ' . esc_url( $products['@odata.nextLink'] ) );
                }

                $this->set_products_as_elements( $url[ 'query' ] );
            }

            return true;
        }else{

            $this->write_log( 'Unable to get product.' );
            $this->set_status( 'error' );

            return false;
        }
    }

    /**
     * Return temp elements form api response
     *
     * @return mixed
     * @since 1.1.0
     */
    public function get_elements()
    {
        return ( !empty( $this->temp_elements ) ? $this->temp_elements : false );
    }
        
    /**
     * Return temp element content
     *
     * @param string $data_item
     * @return mixed
     */
    public function get_element_data( $key )
    {
        return ( isset( $this->temp_elements[ $this->iterated ][ $key ] ) ? $this->temp_elements[ $this->iterated ][ $key ] : false );
    }

    /**
     * Delete meta from temp element array
     *
     * @param string $key
     * @return void
     * @since 1.1.0
     */
    public function delete_element_data( $key )
    {
        if( $this->get_element_data( $key ) ){
            unset( $this->temp_elements[ $this->iterated ][ $key ] );
        }
    }

    /**
     * Append or update temp element data
     *
     * @param string $key
     * @param mixed $data
     * @return void
     * @since 1.1.0
     */
    public function update_element_data( $key, $data )
    {
        $this->temp_elements[ $this->iterated ][ $key ] = $data;
    }

    /**
     * Return count of elements from api response
     *
     * @return mixed
     * @since 1.1.0
     */
    public function get_count_elements()
    {
        return ( $this->get_elements() ? count( $this->get_elements() ) - 1 : false );
    }

    /**
     * Check if all api keys is valid and connection isset
     *
     * @return mixed
     * @since 1.1.0
     */
    public function check_connections()
    {
        return ( ! empty( tempo()->methods->check_connect() ) ? true : false );
    }

    /**
     * Return current log file name
     *
     * @return string
     * @since 1.1.0
     */
    public function get_log_name()
    {
        if( !empty( $this->log_name ) ){
            return $this->log_name;
        }else{
            return $this->create_log_name();
        }
    }

    /**
     * Create log name
     *
     * @return string
     * @since 1.1.0
     */
    public function create_log_name()
    {
        return $this->log_name = date( 'His' );
    }

    /**
     * Return log from file
     *
     * @return void
     * @since 1.1.0
     */
    public function get_log()
    {
        $path = P_PATH . 'logs/';

        if( $log_file = fopen( $path . 'tempo_' . $this->get_log_name() . '.log', 'r' ) ){
            while ( ( $buffer = fgets( $log_file, 4096 ) ) !== false) {
                $this->logging[] = json_decode( $buffer, true );
            }
        }

    }

    /**
     * Save log in file and clean temp variable
     *
     * @return void
     * @since 1.1.0
     */
    public function save_log()
    {
        $path = P_PATH . 'logs/';

        $log_file = fopen( $path . 'tempo_' . $this->get_log_name() . '.log', 'a' );

        if( ! empty( $this->logging ) ){
            foreach ( $this->logging as $log ) {
                fwrite( $log_file, json_encode( $log ) . "\n" );
            }
        }

        fclose( $log_file );

        $this->logging = [];

    }

    /**
     * Create and write log
     *
     * @param string $datas
     * @return void
     * @since 1.0.0
     */
    public function write_log( $datas = '' )
    {
        $this->logging[] = [ 'time' => time(), 'message' => $datas ];       
    }

    /**
     * Check if we has enough time to complete all tasks. If no - start create cron task
     *
     * @return boolean
     * @since 1.0.0
     */
    public function is_time_valid()
    {

        $processing_time = false;

        if( is_null( $this->iteration_time ) ){
            $this->start_time();
        }

        if( is_null( $this->max_time ) ){
            $this->set_max_time();
        }

        if( time() < $this->max_time ){
            $processing_time = true;
        }else{
            //if we has no time for script - try to resume while wp cron

            $this->write_log( 'Timeout. Add cron task.' );

            $this->set_status( 'timeout' );
            $this->set_cron_task();
        }

        return $processing_time;

    }

    /**
     * Launch setup api elements as temp variable
     *
     * @return boolean
     * @since 1.1.0
     */
    public function set_elements()
    {
        if( ! $this->get_type() || $this->get_type() == 'categories' ){
            //Nothing loaded. Maybe it is start of operation. Need set up categoties.
            $this->set_type( 'categories' );

            $this->write_log( 'Start trying sync categories.' );

            return $this->set_category_as_elements();

        }elseif( $this->get_type() == 'products' ){

            $this->write_log( 'Start trying sync products.' );

            return $this->set_products_as_elements();

        }
    }

    /**
     * Clear temp variable
     *
     * @return void
     * @since 1.1.0
     */
    public function clean_iteration()
    {
        $this->temp_elements = [];
        $this->iterated = 0;
    }

    /**
     * Iterate array elements if loaded from API
     *
     * @return boolean
     * @since 1.1.0
     */
    public function iterate_elements()
    {

        if( $this->get_count_elements() ){

            if( $this->iterated < $this->get_count_elements() ){
                $this->iterated++;
                $this->total_iterated++;


                if( $this->debug && $this->get_type() == 'products' && $this->iterated > 100 ){
                    $this->write_log( 'Stop operation because of debug mode. ' );
                    $this->set_status( 'complete' );
                    return false;
                }

                return true;

            }elseif( $this->iterated == $this->get_count_elements() && $this->get_count_elements() > 0 && $this->get_type() == 'categories' ){
                //all elements processed. Need load products
                $this->clean_iteration();
                $this->set_type( 'products' );

                return $this->set_elements();

            }else{
                $this->set_status( 'complete' );

                return false;
            }

        }else{
            return false;
        }

    }

    /**
     * Check if elements not empty and was loaded. If not - called to set up it.
     *
     * @return boolean
     * @since 1.1.0
     */
    public function is_elements()
    {
        if( ! $this->get_elements() ){
            return $this->set_elements();
        }else{
            return $this->iterate_elements();
        }
    }

    /**
     * Processing main while cycle
     *
     * @return boolean
     * @since 1.0.0
     */
    public function can_processing()
    {
        $time = $this->is_time_valid();
        $elements = $this->is_elements();

        $this->save_process();

        return ( $time && $elements );
    }

    /**
     * Save things for ajax and cron
     *
     * @return void
     * @since 1.0.0
     */
    public function save_process()
    {
        //Save log
        // set_transient( 'tempo_log', $this->logging, 60 * 60 * 24 );
        $this->save_log();

        //Save status and etc
        $params = [
            'operation_type'   => $this->get_type(),
            'operation_status' => $this->get_status(),
            'debug'            => $this->debug,
            'start_time'       => $this->start_time,
            'iterated'         => $this->iterated,
            'total_elements'   => $this->total_elements,
            'total_iterated'   => $this->total_iterated,
            'count_elements'   => $this->get_count_elements(),
            'log_file'         => $this->log_name,
        ];

        set_transient( 'tempo_process', $params, 60 * 60 * 24 );

    }

    /**
     * Count expected items
     *
     * @return void
     * @since 1.1.0
     */
    public function count_total_elements()
    {
        $this->total_elements = 0;

        $categories = $this->get_api_catalogs();
        $products = $this->get_api_products();

        if( $categories && $products ){
            $this->total_elements = $categories['@odata.count'] + $products['@odata.count'] - 2;
        }

    }

    /**
     * Check old opetaions, clean old process if needed, exit if some one else run operation
     *
     * @return void
     * @since 1.1.0
     */
    public function before_start()
    {
        $this->set_status( 'active' );
        $this->count_total_elements();

        if( $old_operation = get_transient( 'tempo_process' ) ){

            switch ( $old_operation['operation_status'] ){
                case 'timeout':

                    if( $this->debug ){
                        $this->write_log( 'Found timeout old task.' );
                    }
                
                    if( $old_operation['operation_type'] == 'categories' ){
                        $this->set_type( 'categories' );

                    }elseif( $old_operation['operation_type'] == 'products' ){
                        $this->set_type( 'products' );
                    }

                    $this->set_start_time( $old_operation['start_time'] );
                    //rollback
                    $this->iterated = $old_operation['iterated'] - 1;
                    $this->total_iterated = $old_operation['total_iterated'] - 1;
                    $this->total_elements = $old_operation['total_elements'];
                    
                    if( isset( $old_operation['log_file'] ) ){
                        $this->log_name = $old_operation['log_file'];
                        // $this->get_log();
                    }

                    break;
                case 'active':

                    $this->exit( 'Double start' );

                    break;
                case 'complete':
                
                    delete_transient( 'tempo_process' );
                    delete_transient( 'tempo_log' );

                    $this->create_log_name();
              
                    break;
                default:

                    delete_transient( 'tempo_process' );
                    delete_transient( 'tempo_log' );

                    $this->create_log_name();

                    break;
            }
                
        }

        do_action( 'tempo_sync_before' );

    }

    /**
     * Start everything operations
     *
     * @return void
     * @since 1.0.0
     */
    public function start()
    {
        if( $this->check_connections() ){

            $this->before_start();

            while( $this->can_processing() ){

                if( $this->get_type() == 'categories' ){
                    $this->do_category();
                }elseif( $this->get_type() == 'products' ){
                    $this->do_product();
                }

            }

        }else{
            $this->write_log( 'Error while trying setup connection.' );
        }

        $this->exit( 'End start function' );

    }
    
}
