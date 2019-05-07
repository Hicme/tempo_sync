<?php

namespace admin;

class Register_Settings
{

    public static function register_options()
    {
        register_setting( 'p-settings', 'tempo_api', [ __CLASS__, 'sanitize_return' ] );
        register_setting( 'p-settings', 'tempo_syndicate', [ __CLASS__, 'sanitize_return' ] );
        register_setting( 'p-settings', 'tempo_debug', [ __CLASS__, 'sanitize_checkbox' ] );

        add_settings_section(
            'id_p_general',
            'General Settings',
            [ __CLASS__, 'settings_html' ],
            'p_general_settings'
        );

        add_settings_field(
            'id_api_key',
            'API key',
            [ __CLASS__, 'id_api_key_html' ],
            'p_general_settings',
            'id_p_general'
        );

        add_settings_field(
            'id_syndicate_key',
            'Syndicate key',
            [ __CLASS__, 'id_syndicate_key_html' ],
            'p_general_settings',
            'id_p_general'
        );

        add_settings_field(
            'id_debug',
            'Debug',
            [ __CLASS__, 'id_debug_html' ],
            'p_general_settings',
            'id_p_general'
        );
    }

    public static function sanitize_return( $value )
    {  
        return esc_attr( $value );
    }

    public static function sanitize_checkbox( $value )
    {  

        if( is_null( $value ) ){
            return false;
        }else{
            return esc_attr( $value );
        }

    }

    public static function settings_html()
    {
        echo '<p>Here you can set up API keys and others.</p>';
    }

    public static function id_api_key_html()
    {
        
        render_input( [
            'id'          => 'id_api_key',
            'label'       => '',
            'name'        => 'tempo_api',
            'value'       => get_option( 'tempo_api', '' ),
            'description' => 'Put here API key',
        ] );

    }

    public static function id_syndicate_key_html()
    {
        
        render_input( [
            'id'          => 'id_syndicate_key',
            'label'       => '',
            'name'        => 'tempo_syndicate',
            'value'       => get_option( 'tempo_syndicate', '' ),
            'description' => 'Put here Syndicate key',
        ] );

    }

    public static function id_debug_html()
    {

        render_input( [
            'id'          => 'id_syndicate_key',
            'label'       => '',
            'type'        => 'checkbox',
            'name'        => 'tempo_debug',
            'value'       => '1',
            'attributes'  => ( get_option( 'tempo_debug', false ) ? [ 'checked' => 'checked' ] : [] ) ,
            'description' => 'Enable debug mode?',
        ] );

    }
    
}