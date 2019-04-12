<?php

class Websystems_Prints_Manager_Admin extends Websystems_Prints_Manager{
    public function __construct() {
        parent::__construct();
    }
 
    public function enqueue_styles() {
 
        wp_enqueue_style(
            'websystems-prints-manager-admin',
            plugin_dir_url( __FILE__ ) . 'css/websystems-prints-manager-admin.css',
            array(),
            $this->version,
            FALSE
        );
 
    }

    public function enqueue_scripts() { 
         
    }
    private function get_products_categories() {
        $categories_viewmodel = [];
        $categories = get_categories( [ 'taxonomy' => 'product_cat', 'orderby' => 'name' ] );
        foreach ($categories as $category) {
            $categories_viewmodel[] = ['id' => $category->term_id, 'name' => $category->name];
        }
        return $categories_viewmodel;
    }
    public function add_options_page() {
    
        add_menu_page( 
            'Odbitki',
            'Odbitki', 
            'manage_options', 
            'prints-options-page', 
            [ $this, 'render_options_page' ]
        );
 
    }
 
    public function render_options_page() {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'prints-options-page',
            plugin_dir_url( __FILE__ ) . 'js/prints-options-page.js',
            array(),
            $this->version,
            FALSE
        );
        require_once plugin_dir_path( __FILE__ ) . 'partials/prints-options-page.php';
    }
    
    public function wp_ajax_configure_websystems_prints_plugin() {
        if( ['prints_category_id', 'uploaded_prints_dir' , 'placed_order_uploaded_prints_dir_prefix', 'action'] !== array_keys($_POST) ) {
            wp_send_json_error( 'Błąd przesyłu konfiguracji' );
        }
        unset( $_POST['action'] );
        $errors = [];
        foreach( $_POST as $configuration_key => $configuration_value ) { 
            try {
                $this->persist_plugin_configuration_variable( $configuration_key, $configuration_value );
            } catch( Exception $e) {
                $errors[] =  $configuration_key . ' '. $e->getMessage();
            }  
        }
        if( ! empty( $errors ) ) {
            wp_send_json_error( implode( '; ', $errors) );
        }
        $this->set_plugin_configuration_variable_from_db();
        wp_send_json_success();
    }
}