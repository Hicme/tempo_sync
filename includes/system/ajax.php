<?php

namespace system;

class Ajax{

    public function __construct()
    {
        add_action( 'wp_ajax_start_parsing', [ $this, 'start_parsing' ] );
        add_action( 'wp_ajax_check_parsing', [ $this, 'check_parsing' ] );
    }

    public function start_parsing()
    {
        if( !wp_verify_nonce( $_POST['nonce'], 'tempo-admin-ajax-nonce' ) ){
            wp_send_json_error( [], 404 );
        }

        @ignore_user_abort(true);
        @set_time_limit(900);

        $parser = new Parser();

        $this->close_browser_connection( wp_json_encode( [ 'status' => 'OK', 'datas' => [ 'process_name' => $parser->get_name(), 'status' => '', 'logged' => '' ] ] ) .'        ' );

        $parser->start();

        // die;
    }

    public function check_parsing()
    {
        if( !wp_verify_nonce( $_POST['nonce'], 'tempo-admin-ajax-nonce' ) ){
            wp_send_json_error( [], 400 );
        }

        if( $process_data = $this->get_process_by_name( $_POST['process_name'] ) ){

            wp_send_json( [ 
                'status' => 'OK', 
                'datas' => [ 
                        'process_name' => $process_data['process_name'], 
                        'status'       => $process_data['status'], 
                        'logged'       => $process_data['log'],
                        'operation'    => $process_data['operation'],
                    ] 
                ], 200 );
        }else{
            wp_send_json( [ 'status' => 'ERROR', 'datas' => ['log' => 'Some error happens. No jobs found.' ] ], 200 );
        }

    }

    private function get_process_by_name( $process_name )
    {
        $operation_name = esc_attr( $process_name );

        if( ( $status = get_transient( $operation_name ) ) && ( $status_log = get_transient( $operation_name . '_log' ) ) ){

            if( $done = get_transient( esc_attr( $process_name ) . '_done' ) ){
                $message = 'Operaton ended in ' . $done['time'];
                
                $operation = $message;
            }else{
                $operation = 'WORKING';
            }

            return [
                'process_name' => $operation_name,
                'status'       => $status,
                'log'          => $status_log,
                'operation'    => $operation,
            ];
        }else{
            return false;
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