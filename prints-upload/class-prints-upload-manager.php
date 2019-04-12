<?php 

class Prints_Upload_Manager extends Websystems_Prints_Manager{
    public function __construct() {
        parent::__construct();
    }
    private function get_print_cart_items( $count = false ) {
        $print_cart_items = [];

        // start of the loop that fetches the cart items

        foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
            $_product = $values['data'];
            $terms = get_the_terms( $_product->id, 'product_cat' );

            foreach ($terms as $term) {
                $_categoryid = $term->term_id;
                if ( TRUE || $this->prints_category_id == $_categoryid ) {//kateoria odbitek do wyciagniecia do options
                    $print_cart_items[] = [ 
                        'quantity' => $values['quantity'],
                        'product_name' => $_product->get_name(),
                        'product_id' => $_product->get_id()
                    ];
                    break;
                }
            }
        }
        return $print_cart_items;
    }
    private function get_temp_dir_name( $product_id, $salt ) {
        return '/' . $this->get_dir_name() . '/' . $product_id . '_TEMP_' .  $salt;
    }
    private function get_dir_name() {
        return $this->uploaded_prints_dir;
    }
    public function wp_ajax_save_file() {
        if( ! isset( $_FILES['file'] ) ) {
            wp_die( json_encode( ['error' => 'Nie przesłano pliku!' ] ) );
        }
        //if( IMAGETYPE_JPEG !== exif_imagetype( $_FILES['file']['tmp_name'] ) ) {
        //if( 'JPG'  != pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) && 'jpg'  != pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) && 'JPEG'  != pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION )  && 'jpeg'  != pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) )//R.
        if( ! in_array(  strtolower( pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) ), ['jpg', 'jpeg' ] ) ) {
            wp_die( json_encode( ['error' => 'Przesłano niepoprawny plik! Dopuszczalne rozszerzenie to JPG/JPEG' ] ) );		
        } 
		
		

        $filename = uniqid() . '_' . str_replace( ' ', '', $_FILES['file']['name'] );

        $uploaded_prints_dir_salts_in_wc_session = WC()->session->get( 'uploaded_prints_dir_salts' );
        
        $uploaded_prints_dir_salts_in_wc_session = unserialize( $uploaded_prints_dir_salts_in_wc_session );
        if( isset( $uploaded_prints_dir_salts_in_wc_session[ $_POST['product_id'] ] ) ) {
            $salt_for_dir = $uploaded_prints_dir_salts_in_wc_session[ $_POST['product_id'] ];
        }
        else {
            $salt_for_dir = uniqid();
            $uploaded_prints_dir_salts_in_wc_session[ $_POST['product_id'] ] = $salt_for_dir;
            WC()->session->set( 'uploaded_prints_dir_salts', serialize( $uploaded_prints_dir_salts_in_wc_session ) );

        }
        $temp_dir = $this->get_temp_dir_name( $_POST['product_id'], $salt_for_dir);
        //$filename = htmlentities(  $_FILES['file']['name'] );
        $temp_file_dir = wp_upload_dir()['basedir'] . $temp_dir . '/' . $filename;
        mkdir(wp_upload_dir()['basedir'] . $temp_dir );
        $temp_thumbnail_file_dir = wp_upload_dir()['basedir'] . $temp_dir . '_THUMBS/' . $filename;
        mkdir(wp_upload_dir()['basedir'] . $temp_dir . '_THUMBS/');

        move_uploaded_file( $_FILES['file']['tmp_name'], $temp_file_dir );

        $wp_image_editor = wp_get_image_editor( $temp_file_dir );
        $size = $wp_image_editor->get_size();
        $wp_image_editor->resize( 100, 100, true );  
        //$wp_image_editor->resize( $size['width']/10, $size['height']/10 );      
        $wp_image_editor->save( $temp_thumbnail_file_dir );

        

        wp_die( json_encode(  ['url' => wp_upload_dir()['baseurl']  . $temp_dir . '_THUMBS/' . $filename ]) );
    }
    public function clear_uploaded_prints_dir_salts_in_wc_session ( $cart_item_key ) {
        $cart_item = WC()->cart->get_cart_item( $cart_item_key );
        if( $cart_item['variation_id'] ) {
            $product_id = $cart_item['variation_id'];
        } else {            
            $product_id = $cart_item['product_id'];
        }

        $uploaded_prints_dir_salts_in_wc_session = WC()->session->get( 'uploaded_prints_dir_salts' );

        if( !$uploaded_prints_dir_salts_in_wc_session ) {
            $uploaded_prints_dir_salts_in_wc_session = [];
        } else {
            $uploaded_prints_dir_salts_in_wc_session = unserialize( $uploaded_prints_dir_salts_in_wc_session );
        }
        if( is_array( $uploaded_prints_dir_salts_in_wc_session ) ) {
            unset( $uploaded_prints_dir_salts_in_wc_session[ $product_id ] );
        } else {
            $uploaded_prints_dir_salts_in_wc_session = [];            
        }

        WC()->session->set( 'uploaded_prints_dir_salts', serialize( $uploaded_prints_dir_salts_in_wc_session ) );
    }
 	/*public function wp_ajax_save_file() {
 		$filename = uniqid() . '_' . str_replace( ' ', '', $_FILES['file']['name'] );
 		//$filename = htmlentities(  $_FILES['file']['name'] );
 		$temp_file_dir = wp_upload_dir()['basedir'] . '/temp/' . $filename;
		$temp_thumbnail_file_dir = wp_upload_dir()['basedir']  . '/temp/thumbnails/' . $filename;

		move_uploaded_file( $_FILES['file']['tmp_name'], $temp_file_dir );

		$wp_image_editor = wp_get_image_editor( $temp_file_dir );
		$size = $wp_image_editor->get_size();
		$wp_image_editor->resize( $size['width']/10, $size['height']/10 );		
		$wp_image_editor->save( $temp_thumbnail_file_dir );

 		wp_die( wp_upload_dir()['baseurl']  . '/temp/thumbnails/' . $filename );
 	}*/

 	public function wp_ajax_get_file() {
 		$temp_file_dir = wp_upload_dir()['basedir'] . '/temp/' . $_GET[ 'filename' ];

 		wp_die( base64_encode( $temp_file_dir ) );
 	}
    public function enqueue_styles() {
        die();
        if( is_cart() && true == $this->get_print_cart_items( true ) ) {
            wp_enqueue_style(
                'prints-upload-style',
                plugin_dir_url( __FILE__ ) . 'css/prints-upload.css',
                array(),
                $this->version,
                FALSE
            );
        }
    }
    private function get_uploaded_prints_thumbnails_urls() {
        $uploaded_prints_thumbnails_urls = [];
        $uploaded_prints_dir_salts = WC()->session->get( 'uploaded_prints_dir_salts');
        //$this->create_zip( 2807, 'sgt', 280, '5c83c5f0a8b7d' );
        if( $uploaded_prints_dir_salts ) {
            $uploaded_prints_dir_salts = unserialize( $uploaded_prints_dir_salts );
            if(is_array($uploaded_prints_dir_salts)) {
                foreach (  $uploaded_prints_dir_salts as $product_id => $uploaded_prints_dir_salt ) {
                 $uploaded_prints_thumbnails_urls[ $product_id ] = $this->get_temp_dir_name( $product_id, $uploaded_prints_dir_salt ) . '_THUMBS';
                }
            }
        }
        return $uploaded_prints_thumbnails_urls;
    }
    private function get_zip_filename( $order_id, $sku ) {
        return $order_id . '_' . str_replace(' ', '_', $sku) . '.zip';
    }
    public function create_zip( $order_id, $sku, $product_id, $uploaded_prints_dir_salt ) {
        $zip = new ZipArchive();
        $zip_filename = $this->get_zip_filename( $order_id, $sku );
        $zip_dir = wp_upload_dir()['basedir'] . $this->get_dir_name();

        $zip->open( $zip_dir . $zip_filename, ZipArchive::CREATE);
        $uploaded_prints_dir = wp_upload_dir()['basedir'] . $this->get_temp_dir_name( $product_id, $uploaded_prints_dir_salt );
        $uploaded_prints_filenames = scandir( $uploaded_prints_dir );
        foreach ( $uploaded_prints_filenames as $uploaded_prints_filename) {
            if( $uploaded_prints_filename != '.' && $uploaded_prints_filename != '..' ) {
                $zip->addFile($uploaded_prints_dir . '/' . $uploaded_prints_filename);
            }
        }

        $zip->close();
        //rename($uploaded_prints_dir, $zip_dir . $zip_filename);
    }
    public function create_zip_and_add_zip_filepath_as_order_item_meta( $item_id ) {
        $item = new WC_Order_Item_Product( $item_id );
        $variation_id = $item->get_variation_id();
        if( ! isset(  $variation_id ) ) {
            $variation_id = $item->get_product_id();
        }
            
        $sku = $item->get_product()->get_sku();
        $order_id = $item->get_order_id();  

        $uploaded_prints_dir_salts = WC()->session->get( 'uploaded_prints_dir_salts');
        if( $uploaded_prints_dir_salts ) {
            $uploaded_prints_dir_salts = unserialize( $uploaded_prints_dir_salts );
            if( isset( $uploaded_prints_dir_salts[ $variation_id ] ) ) {
                //$this->create_zip( $order_id, $sku, $variation_id, $uploaded_prints_dir_salts[ $variation_id ] );

                ///na razie zamiana nzawy
                $uploaded_prints_dir = wp_upload_dir()['basedir'] . $this->get_temp_dir_name( $variation_id, $uploaded_prints_dir_salts[ $variation_id ]  );
                rename( $uploaded_prints_dir, wp_upload_dir()['basedir'] . $this->get_dir_name() . 'Z_' . $order_id . '_' .$variation_id . '_' . $uploaded_prints_dir_salts[ $variation_id ]  );

                unset( $uploaded_prints_dir_salts_in_wc_session[ $variation_id ] );

                WC()->session->set( 'uploaded_prints_dir_salts', serialize( $uploaded_prints_dir_salts_in_wc_session ) );

                //wc_add_order_item_meta( $item_id, 'uploaded_prints_zip_filepath', $this->get_zip_filename( $order_id, $sku ) );
                //uwaga bo sie pokaze na podsumowaniu zamowienia

                //clear_uploaded_prints_dir_salts_in_wc_session(5555, $variation_id );
            }
            
        }        

    }

    public function block_order_creation_on_invalid_uploaded_prints_number( $order ) {
        $uploaded_prints_dir_salts = unserialize( WC()->session->get( 'uploaded_prints_dir_salts') );

        foreach ( $order->get_items() as $WC_Order_Item_Product ) {

            $variation_id = $WC_Order_Item_Product->get_variation_id();
            $product_id = $WC_Order_Item_Product->get_product_id();
            if( ! isset(  $variation_id ) ) {
                $variation_id = $product_id;
            }

            $terms = get_the_terms( $product_id, 'product_cat' );
            if( isset( $terms ) && ! empty( $terms ) ) {
                foreach ($terms as $term) {
                    $product_category_id = $term->term_id;
                    if ( TRUE || $this->prints_category_id == $product_category_id ) {//kateoria odbitek do wyciagniecia do options                
                        $temp_dir = $this->get_temp_dir_name( $variation_id, $uploaded_prints_dir_salts[$variation_id]);
                        $prints_names = scandir( wp_upload_dir()['basedir'] . $temp_dir );
                        $prints_number = count( $prints_names ) - 2; //minus 2, for files '.' and '..'
                        if( $prints_number != $WC_Order_Item_Product->get_quantity() ) {
                            throw new Exception( 'Nie wgrano wystarczającej liczby odbitek ' . $WC_Order_Item_Product->get_name() . ' <a href="' . wc_get_cart_url() . '">Kliknij tutaj, aby wgrać brakujące odbitki</a>' );
                        }
                    }
                }
            }
            
        }

    }

    public function enqueue_scripts() {
        if( is_cart() && true == $this->get_print_cart_items( true ) ) {
            wp_enqueue_script('jquery');
            wp_enqueue_script(
                'prints-upload',
                plugin_dir_url( __FILE__ ) . 'js/prints-upload.js',
                array(),
                $this->version,
                FALSE
            );
            wp_localize_script( 'prints-upload', 'front_ajax_object', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
        }
    }
    public function add_file_upload() {
        require( dirname( __FILE__ ) . '/partials/prints-upload-partial.php' );
    }

    public function change_quantity_input_on_cart_page( $product_quantity, $cart_item_key, $cart_item ) {//usunac mozliosc zmiany ilosc na stronie koszyka
        $terms = get_the_terms( $cart_item['product_id'], 'product_cat' );
        foreach ($terms as $term) {
            $_categoryid = $term->term_id;
            if ( TRUE || $this->prints_category_id == $_categoryid ) {//kateoria odbitek do wyciagniecia do options                
                return '<span>' . $cart_item['quantity'] . '</span>';
            }
        }

        return $product_quantity;
    }

    public function remove_order_button_on_not_completed_upload( $order_button_html ) {
        return '<span>Nie wgrano odbitek</span>';
        return $order_button_html;
    }
}