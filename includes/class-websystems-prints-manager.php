<?php
 
class Websystems_Prints_Manager {
 
    protected $loader;
 
    protected $plugin_slug;
 
    protected $version;

    protected $plugin_options_prefix;

    protected $prints_category_id;

    protected $placed_order_uploaded_prints_dir_prefix;
    
    private function get_maybe_set_plugin_configuration_variable( string $name, $default_value )  {
        $value = get_option( $this->plugin_options_prefix . $name );
        if( ! $value  ) {
            $value = $default_value;
            $this->persist_plugin_configuration_variable( $name, $value );
        }
        return $value;
    }

    protected function persist_plugin_configuration_variable( string $name, $value )  {
        update_option( $this->plugin_options_prefix . $name, $value );        
    }

    private function maybe_create_uploaded_prints_dir()  {
        if( ! is_dir( wp_upload_dir()['basedir'] . '/' . $this->uploaded_prints_dir ) ) {
            if( ! mkdir( wp_upload_dir()['basedir'] . '/' . $this->uploaded_prints_dir ) ) {
                throw new Exception('Configuration directory creation error!');
            }
        }        
    }

    protected function set_plugin_configuration_variable_from_db()  {
        $this->uploaded_prints_dir = get_option( $this->plugin_options_prefix . 'uploaded_prints_dir' );
        $this->prints_category_id = get_option( $this->plugin_options_prefix . 'prints_category_id' );
        $this->placed_order_uploaded_prints_dir_prefix = get_option( $this->plugin_options_prefix . 'placed_order_uploaded_prints_dir_prefix' );
    }

    public function __construct() {
 
        $this->plugin_slug = 'websystems-prints-manager-slug';
        $this->version = '0.0.1'; 
        $this->plugin_options_prefix = 'web_systems_prints_';

        $this->uploaded_prints_dir = $this->get_maybe_set_plugin_configuration_variable( 'uploaded_prints_dir', 'uploaded-prints' );
        $this->prints_category_id = $this->get_maybe_set_plugin_configuration_variable( 'prints_category_id', 16 );
        $this->placed_order_uploaded_prints_dir_prefix = $this->get_maybe_set_plugin_configuration_variable( 'placed_order_uploaded_prints_dir_prefix', 'Z_' );
        
        $this->maybe_create_uploaded_prints_dir();        
    }
 
    public function get_version() {
        return $this->version;
    }
 
}