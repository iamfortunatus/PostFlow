<?php
class PostFlow_Dashboard {

    public function render_dashboard_page() {
        // Fetch data from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'postflow_topics';

        // Fetch content counts from the custom table
        $published_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE status = %s", 'published'));
        $pending_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE status = %s", 'pending'));

        // Get success or error message from transient
        $message = get_transient('postflow_trigger_message');
        if ($message) {
            $message_type = $message['type'];
            echo "<div class='notice notice-{$message_type} is-dismissible'>
                    <p>{$message['message']}</p>
                </div>";
            delete_transient('postflow_trigger_message'); // Clear the message after displaying
        }

        ?>
        <div class="postflow_wrap">
            <h1 class="postflow_h1">PostFlow  AI Dashboard</h1>

            <!-- Content Stats Section -->
            <div class="postflow-dashboard-stats">
                <h2 class="postflow-dashboard-section-title">Content Stats</h2>
                <ul class="postflow-stats-list">
                    <li><strong>Total Content Published:</strong> <?php echo esc_html( $published_count ? $published_count : '0' ); ?></li>
                    <li><strong>Total Content Pending:</strong> <?php echo esc_html( $pending_count ? $pending_count : '0' ); ?></li>
                </ul>
            </div>

            <!-- Actions Section -->
            <div class="postflow-dashboard-actions">
                <h2 class="postflow-dashboard-section-title">Quick Links</h2>
                <ul class="postflow-actions-list">
                    <li><a href="<?php echo admin_url( 'admin.php?page=postflow_content' ); ?>" class="postflow-action-link">Manage Content</a></li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=postflow_settings' ); ?>" class="postflow-action-link">Plugin Settings</a></li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=postflow_license_upgrade' ); ?>" class="postflow-action-link">License Upgrade</a></li>
                </ul>

                <form method="post" action="admin-post.php">
                    <input type="hidden" name="action" value="trigger_now">
                    <input type="submit" value="Trigger Now" class="postflow-button postflow-trigger-button" />
                </form>
            </div>
        </div>
        <?php
    }

    // Hook to handle the "Trigger Now" action
    public function trigger_now_action() {
        global $wpdb;

        // Fetch the first pending topic
        $table_name_topics = $wpdb->prefix . 'postflow_topics';
        $pending_topic = $wpdb->get_row($wpdb->prepare("SELECT topic_name FROM $table_name_topics WHERE status = %s LIMIT 1", 'pending'));

        // Fetch settings from the postflow_settings table
        $table_name_settings = $wpdb->prefix . 'postflow_settings';
        $wp_username = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name_settings WHERE setting_name = %s", 'postflow_wp_username'));
        $app_password = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name_settings WHERE setting_name = %s", 'postflow_application_password'));
        $wp_url = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name_settings WHERE setting_name = %s", 'postflow_wp_url'));

        // Prepare data to be sent to n8n webhook
        $data = array(
            'topic_name' => $pending_topic->topic_name,
            'wp_username' => $wp_username,
            'app_password' => $app_password,
            'wp_url' => $wp_url,
        );

        // Send data to n8n webhook using wp_remote_post
        $response = wp_remote_post('http://localhost:5678/webhook-test/db1e1adf-570a-42b1-a555-986eb79e808c', array(
            'method'    => 'POST',
            'body'      => json_encode($data),
            'headers'   => array(
                'Content-Type' => 'application/json'
            ),
        ));

        // Check for successful response
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Set an error message to display
            set_transient('postflow_trigger_message', array('message' => 'Error: ' . $error_message, 'type' => 'error'), 60);
        } else {
            // Set a success message to display
            $success_message = 'Successfully triggered the process!';
            set_transient('postflow_trigger_message', array('message' => $success_message, 'type' => 'success'), 60);
        }

        // Redirect back to the dashboard page after handling the action
        wp_redirect(admin_url('admin.php?page=postflow_dashboard'));
        exit;
    }
}

// Instantiate the dashboard class
$postflow_dashboard = new PostFlow_Dashboard();

// Hook to handle the form submission for "Trigger Now"
add_action('admin_post_trigger_now', array($postflow_dashboard, 'trigger_now_action'));

