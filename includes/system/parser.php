<?php


namespace system;

class Parser extends Processor{

    private $elements = [];
    
    public function __construct( array $params )
    {
        if( empty( $params ) ){
            $this->get_name();
            $this->update_state();
        }else{
            $this->set_name( $params['name'] );
        }

        $this->start();
    }

    public function start()
    {

        $this->set_elements( \system\api\Product::get_products() );

        foreach( $this->elements as $element ){

            $this->process_element( $element );

            $this->save_state();
        }

    }

    public function process_element( $element ){
        global $wpdb;

        $old_post = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->posts . ' WHERE post_type = "" AND post_name = "' . $element['slug'] . '"', OBJECT );

        $args = [
            'comment_status'  => 'closed',
            'post_author'     => 0,
            'post_content'    => json_encode( $element ),
            'post_excerpt'    => json_encode( [ 'product_id' => $element['productId'], 'product_name' => $element['productName'] ] ),
            'post_name'       => $element['slug'],
            'post_title'      => $element['productName'],
        ];

        if( ! empty( $old_post ) ){
            $args['ID'] = $old_post->ID;
        }

        wp_insert_post( $args );
    }

    private function set_elements( array $elements )
    {
        $state = $this->get_state();
        $state['total_products'] = $elements['@odata.count'];
        $this->set_state( $state );

        $this->elements = $elements['value'];
    }


    


}