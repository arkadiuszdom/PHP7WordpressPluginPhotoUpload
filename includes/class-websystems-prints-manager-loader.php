<?php
 
class Websystems_Prints_Manager_Loader {
 
    protected $actions;
 
    protected $filters;
 
    public function __construct() {
 
        $this->actions = array();
        $this->filters = array();
     
    }
 
    public function add_action( $hook, $component, $callback, $priority = false, $arguments_number = false ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $arguments_number );
    }
 
    public function add_filter( $hook, $component, $callback, $priority  = false, $arguments_number = false ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $arguments_number );
    }
 
    private function add( $hooks, $hook, $component, $callback, $priority, $arguments_number  ) {
         $hooks[ $hook ]['component_callback'] = [$component, $callback];
        if( $priority ) {
            $hooks[ $hook ][ 'priority' ] = $priority;
        }
        if( $priority ) {
            $hooks[ $hook ][ 'arguments_number' ] = $arguments_number;
        }

        return $hooks;
 
    }
 
    public function run() {
        $this->define_admin_hooks();

        $this->define_prints_upload_manager_hooks();

        foreach ( $this->filters as $hook => $args ) {
            if( $args['priority'] && $args['arguments_number'] ) {
                add_filter( $hook, $args['component_callback'], $args['priority'], $args['arguments_number'] );
            } else {
                add_filter( $hook, $args['component_callback'] );
            }
        }
 
        foreach ( $this->actions as $hook => $args ) {            
            if( $args['priority'] && $args['arguments_number'] ) {
                add_action( $hook, $args['component_callback'], $args['priority'], $args['arguments_number'] );
            } else {
                add_action( $hook, $args['component_callback'] );
            }
        }
 
    }
    private function define_admin_hooks() {
 
        $admin = new Websystems_Prints_Manager_Admin();

        $this->add_action( 'admin_enqueue_styles', $admin, 'enqueue_styles' );
        $this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

        $this->add_action( 'admin_menu', $admin, 'add_options_page' );

        $this->add_action( 'wp_ajax_configure_websystems_prints_plugin', $admin, 'wp_ajax_configure_websystems_prints_plugin' );
 
    }
    private function define_prints_upload_manager_hooks() {
 
        $file_manager = new Prints_Upload_Manager();


        $this->add_action( 'wp_enqueue_styles', $file_manager, 'enqueue_styles' );
        $this->add_action( 'wp_enqueue_scripts', $file_manager, 'enqueue_scripts' );


        $this->add_action( 'woocommerce_before_cart', $file_manager, 'add_file_upload' );


        $this->add_action( 'wp_ajax_save_file', $file_manager, 'wp_ajax_save_file' );
        $this->add_action( 'wp_ajax_nopriv_save_file', $file_manager, 'wp_ajax_save_file' );

        $this->add_action( 'wp_ajax_get_file', $file_manager, 'wp_ajax_get_file' );
        $this->add_action( 'wp_ajax_nopriv_get_file', $file_manager, 'wp_ajax_get_file' );

        $this->add_action( 'woocommerce_remove_cart_item', $file_manager, 'clear_uploaded_prints_dir_salts_in_wc_session', 10, 2 );
        $this->add_action( 'woocommerce_add_order_item_meta', $file_manager, 'create_zip_and_add_zip_filepath_as_order_item_meta' );

        $this->add_filter( 'woocommerce_cart_item_quantity', $file_manager, 'change_quantity_input_on_cart_page', 10, 3 );     

        //$this->add_filter( 'woocommerce_order_button_html', $file_manager, 'replace_order_button_html', 10, 2 );        

        $this->add_filter( 'woocommerce_checkout_create_order', $file_manager, 'block_order_creation_on_invalid_uploaded_prints_number', 10, 1 );//passes WC_Order   

    }
 
 
}