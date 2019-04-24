function start_admin_sync(){
    this.process = null;
    this.sync_name = null;
    this.last_status = null;
    this.lastLogs = [];

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
                current_class.sync_name = responce.datas.process_name;
                
                setTimeout( current_class.start_check_status(), 2000 );
            }
        } );
    }

    this.checkLogs = function( $messages ){

        $messages.forEach( function( item, i, arr ){
            
            if( current_class.lastLogs.indexOf( i ) == -1 ){
                current_class.write_log( item.message, item.time );
                current_class.lastLogs.push( i );
            }

        } );

    }

    this.start_check_status = function(){
        
        current_class.process = setInterval( function(){

            current_class.ajax( { action : "check_parsing", nonce : TEMPOAJAX.ajax_nonce, process_name : current_class.sync_name } ).done( function( responce ){
                if( responce.status == 'OK' ){
                    if( current_class.last_status != responce.datas.status ){
                        current_class.write_progress( responce.datas.status );
                        current_class.checkLogs( responce.datas.logged );
                        // console.log(responce.datas.status);
                        current_class.last_status = responce.datas;

                        if( responce.datas.operation != 'WORKING' ){
                            current_class.write_log( responce.datas.operation );
                            clearInterval( current_class.process );
                        }
                    }
                }else{
                    // clearInterval( current_class.process );
                    console.log('No process found.'); 
                }
            });

        }, 1000);
    }

    this.write_progress = function( $progress ){
        jQuery('#sync_progress_bar').attr( 'max', $progress.total_count ).attr( 'value', $progress.total_processed );
    }

    this.write_log = function( $message, $time = false ){
        // console.log($line);
        if( $time ){
            var date = new Date( $time * 1000 );
            jQuery('#sync_progress_log').append( '<p>' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' | ' + $message + '</p>' );
        }else{
            jQuery('#sync_progress_log').append( '<p>' + $message + '</p>' );
        }
        
    }

    this.run = function(){

        current_class.start_sync();
        
    }
    
    this.run();
}

jQuery(document).ready(function($){

    if( $( '#sync_progress_bar' ).length > 0 ){
        start_admin_sync();
    }

});