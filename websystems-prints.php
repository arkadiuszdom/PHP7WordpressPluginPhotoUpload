<?php
/*
 * Plugin Name:       Web Systems Prints
 * Version:           0.0.1
 */
 
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'PLUGIN_DIR_PATH' ) ) {
    define( 'PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

require_once PLUGIN_DIR_PATH . 'includes/class-websystems-prints-manager.php';
require_once PLUGIN_DIR_PATH . 'includes/class-websystems-prints-manager-loader.php';
require_once PLUGIN_DIR_PATH  . 'prints-upload/class-prints-upload-manager.php';
require_once PLUGIN_DIR_PATH  . 'admin/websystems-prints-manager-admin.php';

function run_websystems_prints_manager() {
 
    $websystems_prints_manager_loader = new Websystems_Prints_Manager_Loader();
    $websystems_prints_manager_loader->run();
 
}

run_websystems_prints_manager();
