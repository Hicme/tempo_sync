<?php

function tempo()
{
    return \system\StartUp::instance();
}

if( ! function_exists( 'render_input' ) ){
    function render_input( array $args )
    {
        if( empty( $args['id'] ) ){
            return;
        }

        $args['type'] = isset( $args['type'] ) ? $args['type'] : 'text';
        $args['name'] = isset( $args['name'] ) ? $args['name'] : $args['id'];
        $args['class'] = isset( $args['class'] ) ? $args['class'] : '';
        $args['value'] = isset( $args['value'] ) ? $args['value'] : '';
        $args['description'] = isset( $args['description'] ) ? $args['description'] : '';

        $attributes = [];

        if ( ! empty( $args['attributes'] ) && is_array( $args['attributes'] ) ) {

            foreach ( $args['attributes'] as $attribute => $value ) {
                $attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
            }
        }

       ?>
       <p class="input-field-wrapper input-<?php echo $args['id'] ?>">
            <label for="input_filed_<?php echo $args['id'] ?>">
                <?php echo wp_kses_post( $args['label'] ); ?>
            </label>

            <input type="<?php echo esc_attr( $args['type'] ); ?>" id="input_filed_<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" <?php echo implode( ' ', $attributes ); ?> />

            <?php
                if( !empty( $args['description'] ) ){
                    echo '<span class="description">'. wp_kses_post( $args['description'] ) .'</span>';
                }
            ?>
       </p>
       <?php
    }
}

function get_category_by_tempo_id( $meta_value )
{
    global $wpdb;

    $result = $wpdb->get_row( 'SELECT term_id FROM ' . $wpdb->termmeta . ' WHERE meta_key = "_categoryId" AND meta_value = "'. $meta_value .'"' , OBJECT );

    return ( $result ? $result->term_id : false );
}

function get_product_by_tempo_id( $meta_value )
{
    global $wpdb;

    $result = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_productId" AND meta_value = "'. $meta_value .'"' , OBJECT );

    return ( $result ? $result->post_id : false );
}

function get_product_tempo_id( $post_id )
{

    if( empty( $post_id ) ){
        return false;
    }

    return get_post_meta( $post_id, '_productId', true );

}

function generate_string( $length = 4 )
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_tempo_image( $post_id = null, $size = 'thumbnail' )
{

    if( empty( $post_id ) ){
        global $post;
        $post_id = $post->ID;
        $title = $post->title;
    }else{
        $title = get_the_title( $post_id );
    }

    switch( $size ){
        case 'small':
            $src = get_post_meta( $post_id, '_imageTiny', true );
        break;
        case 'thumbnail':
            $src = get_post_meta( $post_id, '_imageThumb', true );
        break;
        case 'full':
            $src = get_post_meta( $post_id, '_imageFull', true );
        break;
    }

    if( $src ){
        echo '<img class="img--fluid" src="'. $src .'" alt="'. $title .'">';
    }

}

function get_tempo_attributes( $post_id = null )
{

    if( empty( $post_id ) ){
        global $post;
        $post_id = $post->ID;
    }

    $p_code = get_post_meta( $post_id, '_productCode', true );

    $i_codes = [];

    if( $temps = get_post_meta( $post_id, '_items', true ) ){
        foreach ( $temps['value'] as $temp ) {
            $i_codes[] = $temp[ 'itemNumber' ];
        }
    }

    ?>

    <div class="tempo-product__ids">
        <?php

            if( $p_code ){
                echo '<div class="tempo-product__id">Catalog# '. $p_code .'</div>';
            }

            if( $i_codes ){
                foreach ( $i_codes as $i_code ) {
                    echo '<div class="tempo-product__id">Item # ' . $i_code . '</div>';
                }
            }

        ?>
    </div>
    
    <?php
}