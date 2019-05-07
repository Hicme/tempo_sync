function start_admin_sync(){
    this.process = null;
    this.lastLogs = 0;

    var current_class = this;

    this.ajax = function( $datas ){
        return jQuery.ajax({
            method: "POST",
            url: TEMPOAJAX.url,
            data: $datas,
            dataType: 'json'
        });
    }

    this.start_sync = function(){
        current_class.ajax( { action : "start_parsing", nonce : TEMPOAJAX.ajax_nonce } ).done( function( responce ){
            if( responce.status == 'OK' ){              
                setTimeout( current_class.start_check_status(), 2000 );

                if( responce.datas.operation_status == 'complete' ){
                    current_class.write_progress( 0, 0 );
                    current_class.clear_log();
                }
            }
        } );
    }

    this.checkLogs = function( $messages ){

        $messages.forEach( function( item, i, arr ){
            current_class.write_log( item.message, item.time );
            current_class.lastLogs = item.time;
        } );

    }

    this.start_check_status = function(){
        
        current_class.process = setInterval( function(){

            current_class.ajax( { action : "check_parsing", lastLogs : current_class.lastLogs, nonce : TEMPOAJAX.ajax_nonce } ).done( function( responce ){
                if( responce.status == 'OK' ){

                    if( responce.datas.operation_status == 'active' || responce.datas.operation_status == 'complete' ){
                        current_class.write_progress( responce.datas.total_iterated, responce.datas.total_elements );

                        current_class.checkLogs( responce.log );
                    }

                    if( responce.datas.operation_status == 'complete' ){
                        current_class.write_log( responce.datas.operation_status );
                        console.log( 'Task complete' );
                        clearInterval( current_class.process );
                    }

                }else{
                    // clearInterval( current_class.process );
                    console.log('No process found.'); 
                }
            });

        }, 1000);
    }

    this.write_progress = function( $processed, $total ){
        jQuery('#sync_progress_bar').attr( 'max', $total ).attr( 'value',$processed );
    }

    this.write_log = function( $message, $time = false ){
        // console.log($line);
        if( $time ){
            jQuery('#sync_progress_log').append( '<p>' + $time + ' | ' + $message + '</p>' );
        }else{
            jQuery('#sync_progress_log').append( '<p>' + $message + '</p>' );
        }
        
    }

    this.clear_log = function(){
        jQuery('#sync_progress_log').html('');
    }

    this.run = function(){

        if( jQuery( '#sync_progress_bar' ).length > 0 ){
            current_class.start_sync();
        }

        jQuery( '#delete_import' ).on( 'click', function(e){
            e.preventDefault();
            if( confirm("Are you sure? This can't be undone.") ){
                jQuery( '.sync-loading' ).show();
                current_class.ajax( { action : "delete_import", nonce : TEMPOAJAX.ajax_nonce } ).done( function( responce ){

                    var load = jQuery( '.sync-loading' ).html();

                    jQuery( '.sync-loading' ).html( responce.html );

                    setTimeout( current_class.show_respoce, 1500, load );

                } );
            }
        } );    
    }

    this.show_respoce = function( content ){
        jQuery( '.sync-loading' ).hide().html( content );
    }
    
    this.run();
}

jQuery(document).ready(function($){
    start_admin_sync();
});