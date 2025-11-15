<?php
class PostFlow {

    private $license;
    private $settings;
    private $dashboard;
    private $content;
    private $n8n;
    private $cron;

    public function __construct() {
        $this->license = new PostFlow_License();
        $this->settings = new PostFlow_Settings();
        $this->dashboard = new PostFlow_Dashboard();
        $this->content = new PostFlow_Content();
        $this->n8n = new PostFlow_N8n();
        $this->cron = new PostFlow_Cron();
    }

    public function init() {
        // Register the custom admin menu
        add_action( 'admin_menu', array( $this, 'add_postflow_menu' ) );

        add_action( 'admin_post_trigger_now', array( $this->n8n, 'trigger_workflow' ) );

        // Register cron job
        $this->cron->init();

        // Check license on activation
        register_activation_hook( __FILE__, array( $this->license, 'check_license' ) );

        // Register content import/export
        $this->content->register_content_management_hooks();
    }

    // Add custom admin menu and submenus
    public function add_postflow_menu() {
        add_menu_page( 
            'PostFlow AI Dashboard',         // Page title
            'PostFlow AI',                   // Menu title
            'manage_options',             // Capability
            'postflow_dashboard',         // Menu slug
            array( $this->dashboard, 'render_dashboard_page' ), // Function to render the page
            'dashicons-admin-generic',    // Icon
            3                             // Position
        );

        // Add submenus
        add_submenu_page(
            'postflow_dashboard',         // Parent slug
            'Dashboard',                  // Page title
            'Dashboard',                  // Menu title
            'manage_options',             // Capability
            'postflow_dashboard',         // Submenu slug
            array( $this->dashboard, 'render_dashboard_page' ) // Function to render dashboard
        );
        
        add_submenu_page(
            'postflow_dashboard',
            'Content Management',
            'Content',
            'manage_options',
            'postflow_content',
            array( $this->content, 'render_content_page' )
        );

        add_submenu_page(
            'postflow_dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'postflow_settings',
            array( $this->settings, 'render_settings_page' )
        );

        add_submenu_page(
            'postflow_dashboard',
            'License Upgrade',
            'License Upgrade',
            'manage_options',
            'postflow_license_upgrade',
            array( $this->license, 'render_license_upgrade_page' )
        );
    }
}
