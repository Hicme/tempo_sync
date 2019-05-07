<?php

namespace system;

class Ajax{

    public function __construct()
    {
        add_action( 'wp_ajax_start_parsing', [ $this, 'start_parsing' ] );
        add_action( 'wp_ajax_check_parsing', [ $this, 'check_parsing' ] );
        add_action( 'wp_ajax_delete_import', [ $this, 'delete_import' ] );
    }

    public function delete_import()
    {
        if( !wp_verify_nonce( $_POST['nonce'], 'tempo-admin-ajax-nonce' ) ){
            wp_send_json_error( [], 404 );
        }

        @ignore_user_abort(true);
        @set_time_limit(900);

        global $wpdb;
        
        $posts = $wpdb->get_results( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "tempes"', OBJECT );

        if( $posts ){
            foreach( $posts as $post ){
                wp_delete_post( $post->ID, true );
            }
        }

        $categories = $wpdb->get_results( 'SELECT t.term_id FROM ' . $wpdb->terms . ' AS t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ("tempes_cat")', OBJECT );

        if( $categories ){
            foreach( $categories as $category ){
                wp_delete_term( $category->term_id, 'tempes_cat' );
            }
        }

        wp_send_json( [ 'status' => 'OK', 'html' => 'All items was deleted!' ], 200 );
    }

    public function start_parsing()
    {
        if( !wp_verify_nonce( $_POST['nonce'], 'tempo-admin-ajax-nonce' ) ){
            wp_send_json_error( [], 404 );
        }

        @ignore_user_abort(true);
        @set_time_limit(900);

        $restart = false;

        if( $process = get_transient( 'tempo_process' ) ){

            $log = get_transient( 'tempo_log' );

            $operation_status = [
                'status' => 'OK', 
                'datas' => [ 
                    'operation_status' => $process['operation_status'],
                    'debug'            => $process['debug'],
                    'start_time'       => $process['start_time'],
                    'total_iterated'   => $process['total_iterated'],
                    'total_elements'   => $process['total_elements'],
                ],
                'log' =>  $log,
            ];

            if( $process['operation_status'] == 'timeout' || $process['operation_status'] == 'complete' ){
                $restart = true;
            }

        }else{

            $operation_status = [
                'status' => 'OK', 
                'datas' => [
                    'operation_status' => '',
                    'debug'            => '',
                    'start_time'       => '',
                    'total_iterated'   => '',
                    'total_elements'   => '',
                ],
                'log' => [],
            ];

            $restart = true;

        }

        $this->close_browser_connection( wp_json_encode( $operation_status ) . '        ' );
        
        if( $restart ){
            tempo()->parser->start();
        }

        die;
    }

    public function check_parsing()
    {
        if( !wp_verify_nonce( $_POST['nonce'], 'tempo-admin-ajax-nonce' ) ){
            wp_send_json_error( [], 400 );
        }

        if( $process = get_transient( 'tempo_process' ) ){

            $lastLogs = ( isset( $_POST['lastLogs'] ) ? esc_attr( $_POST['lastLogs'] ) : 0 );
            $send_log = [];
            if( $logs = get_transient( 'tempo_log' ) ){
                foreach( $logs as $log ){
                    if( $log['time'] > $lastLogs ){
                        $send_log[] = [ 'time' => date( 'H:i:s', $log['time'] ), 'message' => $log['message'] ];
                    }
                }
            }

            wp_send_json( [ 
                'status' => 'OK', 
                'datas' => [ 
                    'operation_status' => $process['operation_status'],
                    'debug'            => $process['debug'],
                    'start_time'       => $process['start_time'],
                    'total_iterated'   => $process['total_iterated'],
                    'total_elements'   => $process['total_elements'],
                ],
                'log' =>  $send_log,
            ], 200 );
        }else{
            wp_send_json( [ 'status' => 'ERROR', 'datas' => ['log' => 'Some error happens. No jobs found.' ] ], 200 );
        }

    }

    private function close_browser_connection( $txt = '' )
    {
        // header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		header( 'Content-Length: ' . ( empty( $txt ) ? '0' : 4+strlen( $txt ) ) );
		header( 'Connection: close' );
		header( 'Content-Encoding: none' );
		if (session_id()) session_write_close();
		echo "\r\n\r\n";
		echo $txt;
		
		$ob_level = ob_get_level();
		while ($ob_level > 0) {
			ob_end_flush();
			$ob_level--;
		}
		flush();
		if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
    }

}