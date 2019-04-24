<div class="sync-wrapper">
    <?php if( !$sync ){ ?>

        <form method="POST" class="sync_form">
            <input type="hidden" name="start_sync_actions" value="1">
            <?php wp_nonce_field( 'check_nonce_parsing', 'nonce' ); ?>
            <div class="display_flex">
                <div class="wrapper_export">
                    <p>To start synchronization press START button.</p>
                    <?php submit_button( 'START', 'primary', 'start_export', false ); ?>
                </div>
            </div>
        </form>

        <?php
            // $parser = new \system\Parser();
            // $parser->start();
        ?>
    <?php }else{ ?>

        <div class="wrapper_progress">
            <progress id="sync_progress_bar" max="0" value="0">
            </progress>
        </div>
        <fieldset id="sync_log">
            <legend>
                Progress Log
            </legend>
            <div id="sync_progress_log"></div>
        </fieldset>
        
    <?php } ?>
</div>