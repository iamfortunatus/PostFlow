<?php
/*
Plugin Name: PostFlow
Plugin URI: https://www.ufirstdev.com/postflow
Description: A plugin to automate content generation via n8n workflows.
Version: 1.0.0
Author: UfirstDEV Technologies
Author URI: https://www.ufirstdev.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'POSTFLOW_VERSION', '1.0' );
define( 'POSTFLOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include required files for functionality
require_once POSTFLOW_PLUGIN_DIR . 'includes/class-postflow.php';
require_once POSTFLOW_PLUGIN_DIR . 'includes/class-license.php';
require_once POSTFLOW_PLUGIN_DIR . 'includes/class-settings.php';
require_once POSTFLOW_PLUGIN_DIR . 'includes/class-dashboard.php';
require_once POSTFLOW_PLUGIN_DIR . 'includes/class-content.php';
require_once POSTFLOW_PLUGIN_DIR . 'includes/class-cron.php';
require_once POSTFLOW_PLUGIN_DIR . 'n8n-integration/class-n8n.php';

// Initialize the plugin
function postflow_init() {
    $postflow = new PostFlow();
    $postflow->init(); // Make sure the PostFlow class has the 'init' method defined
}

// Enqueue custom admin styles for the plugin
function postflow_enqueue_styles() {
    wp_enqueue_style( 'postflow-styles', plugin_dir_url( __FILE__ ) . 'assets/styles.css' );
}

add_action( 'admin_enqueue_scripts', 'postflow_enqueue_styles' );
add_action( 'plugins_loaded', 'postflow_init' );

// Register settings for saving the custom duration, unit, and other options
function postflow_register_settings() {
    register_setting( 'postflow_settings_group', 'postflow_trigger_interval' );
    register_setting( 'postflow_settings_group', 'postflow_custom_duration', 'absint' );  // Ensures it's an integer
    register_setting( 'postflow_settings_group', 'postflow_custom_duration_unit' );
    register_setting( 'postflow_settings_group', 'postflow_application_password' );
    register_setting( 'postflow_settings_group', 'postflow_license_key' );
    register_setting( 'postflow_settings_group', 'postflow_wp_url' );
    register_setting( 'postflow_settings_group', 'postflow_wp_username' ); // Add this line to register WordPress Admin Username
}
add_action( 'admin_init', 'postflow_register_settings' );

// Create necessary tables when the plugin is activated
function postflow_create_tables() {
    global $wpdb;
    $table_name_topics = $wpdb->prefix . 'postflow_topics';
    $table_name_settings = $wpdb->prefix . 'postflow_settings';

    // Create the postflow_topics table if it doesn't exist
    $sql_topics = "CREATE TABLE $table_name_topics (
        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
        topic_name TEXT NOT NULL,
        status VARCHAR(20) NOT NULL,
        post_link VARCHAR(255),
        PRIMARY KEY (ID)
    ) {$wpdb->get_charset_collate()};";

    // Create the postflow_settings table if it doesn't exist
    $sql_settings = "CREATE TABLE $table_name_settings (
        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
        setting_name VARCHAR(255) NOT NULL,
        setting_value TEXT NOT NULL,
        PRIMARY KEY (ID)
    ) {$wpdb->get_charset_collate()};";

    // Include the upgrade script for table creation
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_topics);
    dbDelta($sql_settings);
}

register_activation_hook(__FILE__, 'postflow_create_tables');

// Drop the tables if they exist
function postflow_drop_tables() {
    global $wpdb;
    $table_name_topics = $wpdb->prefix . 'postflow_topics';
    $table_name_settings = $wpdb->prefix . 'postflow_settings';

    // Drop the tables if they exist
    $wpdb->query("DROP TABLE IF EXISTS $table_name_topics");
    $wpdb->query("DROP TABLE IF EXISTS $table_name_settings");

    // Optionally, remove plugin settings from the options table
    delete_option('postflow_trigger_interval');
    delete_option('postflow_custom_duration');
    delete_option('postflow_custom_duration_unit');
    delete_option('postflow_application_password');
    delete_option('postflow_license_key');
    delete_option('postflow_wp_url');
    delete_option('postflow_wp_username');
}

register_deactivation_hook(__FILE__, 'postflow_drop_tables');


