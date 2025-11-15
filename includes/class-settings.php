<?php
class PostFlow_Settings {

    // Constructor to register settings
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_postflow_save_settings', array($this, 'save_settings_to_db')); // Handling form submission
        add_action('admin_footer', array($this, 'add_custom_js')); // Enqueue custom JS to handle the custom duration toggle
    }

    // Register settings (no need for options in this case as we are saving directly to the table)
    public function register_settings() {
        // No need to register settings since we're using a custom table
    }

    // Render the settings page
    public function render_settings_page() {

        // Ensure the user has the right permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to access this page.' );
        }

        // Fetch success or error message from transient
        $message = get_transient('postflow_import_message');
        if ($message) {
            $message_type = $message['type'];
            echo "<div class='notice notice-{$message_type} is-dismissible'>
                    <p>{$message['message']}</p>
                </div>";
            delete_transient('postflow_import_message');
        }
        
        ?>
        <div class="postflow_wrap">
            <h1 class="postflow_h1">PostFlow AI Settings</h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="postflow_save_settings"> <!-- Form action -->
                <?php wp_nonce_field('postflow_save_settings_nonce'); // Nonce field for security ?>

                <table class="postflow_table">
                    <!-- License Key Setting -->
                    <tr valign="top">
                        <th scope="row" class="postflow_label">License Key</th>
                        <td><input type="text" name="postflow_license_key"
                                value="<?php echo esc_attr($this->get_setting_value('postflow_license_key')); ?>"
                                class="postflow_input_text" /></td>
                    </tr>

                    <!-- WordPress Admin Username -->
                    <tr valign="top">
                        <th scope="row" class="postflow_label">WordPress Admin Username</th>
                        <td><input type="text" name="postflow_wp_username"
                                value="<?php echo esc_attr($this->get_setting_value('postflow_wp_username')); ?>"
                                class="postflow_input_text" /></td>
                    </tr>

                    <!-- Trigger Interval Settings -->
                    <tr valign="top">
                        <th scope="row" class="postflow_label">Trigger Interval</th>
                        <td>
                            <select name="postflow_trigger_interval" id="trigger_interval" class="postflow_select">
                                <option value="hourly"
                                        <?php selected($this->get_setting_value('postflow_trigger_interval'), 'hourly'); ?>>Every
                                    Hour</option>
                                <option value="daily"
                                        <?php selected($this->get_setting_value('postflow_trigger_interval'), 'daily'); ?>>Every
                                    Day
                                </option>
                                <option value="weekly"
                                        <?php selected($this->get_setting_value('postflow_trigger_interval'), 'weekly'); ?>>Every
                                    Week</option>
                                <option value="custom"
                                        <?php selected($this->get_setting_value('postflow_trigger_interval'), 'custom'); ?>>Custom
                                    Duration</option>
                            </select>
                            <div id="custom_duration_fields" class="postflow_custom_duration_fields" style="display: none;">
                                <input type="number" name="postflow_custom_duration"
                                       value="<?php echo esc_attr($this->get_setting_value('postflow_custom_duration')); ?>"
                                       placeholder="Enter value" id="custom_duration" class="postflow_custom_duration" />
                                <select name="postflow_custom_duration_unit" id="custom_duration_unit" class="postflow_select">
                                    <option value="minutes"
                                            <?php selected($this->get_setting_value('postflow_custom_duration_unit'), 'minutes'); ?>>
                                        Minutes</option>
                                    <option value="days"
                                            <?php selected($this->get_setting_value('postflow_custom_duration_unit'), 'days'); ?>>
                                        Days</option>
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Application Password Setting -->
                    <tr valign="top">
                        <th scope="row" class="postflow_label">Application Password</th>
                        <td><input type="password" name="postflow_application_password"
                                value="<?php echo esc_attr($this->get_setting_value('postflow_application_password')); ?>"
                                class="postflow_input_password" /></td>
                    </tr>

                    <!-- WordPress URL Setting -->
                    <tr valign="top">
                        <th scope="row" class="postflow_label">WordPress URL</th>
                        <td><input type="text" name="postflow_wp_url" value="<?php echo esc_attr(home_url()); ?>"
                                class="postflow_wp_url" readonly /></td>
                    </tr>
                </table>

                <?php submit_button('Save Settings', 'postflow_submit_button'); ?>
            </form>
        </div>
        <?php
    }

    // Save settings to the custom table
    public function save_settings_to_db() {
        // Verify nonce for security
        // if (!isset($_POST['postflow_save_settings_nonce']) || !wp_verify_nonce($_POST['postflow_save_settings_nonce'], 'postflow_save_settings_nonce')) {
        //     die('Security check failed');
        // }

        global $wpdb;
        $table_name_settings = $wpdb->prefix . 'postflow_settings';

        // Data to be saved
        $settings = array(
            'postflow_license_key' => sanitize_text_field($_POST['postflow_license_key']),
            'postflow_wp_username' => sanitize_text_field($_POST['postflow_wp_username']),
            'postflow_trigger_interval' => sanitize_text_field($_POST['postflow_trigger_interval']),
            'postflow_custom_duration' => intval($_POST['postflow_custom_duration']),
            'postflow_custom_duration_unit' => sanitize_text_field($_POST['postflow_custom_duration_unit']),
            'postflow_application_password' => sanitize_text_field($_POST['postflow_application_password']),
            'postflow_wp_url' => sanitize_text_field($_POST['postflow_wp_url'])
        );

        // Loop through each setting and insert/update in the settings table
        foreach ($settings as $setting_name => $setting_value) {
            // Check if the setting already exists
            $existing_setting = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $table_name_settings WHERE setting_name = %s", $setting_name));

            if ($existing_setting) {
                // Update existing setting
                $wpdb->update(
                    $table_name_settings,
                    array('setting_value' => $setting_value),
                    array('setting_name' => $setting_name),
                    array('%s'),
                    array('%s')
                );
            } else {
                // Insert new setting
                $wpdb->insert(
                    $table_name_settings,
                    array(
                        'setting_name' => $setting_name,
                        'setting_value' => $setting_value
                    ),
                    array('%s', '%s')
                );
            }
        }

        // Set a success message to be displayed
        $success_message = 'Settings saved successfully!';
        set_transient('postflow_import_message', array('message' => $success_message, 'type' => 'success'), 60);

        // Redirect back to settings page after saving
        wp_redirect(admin_url('admin.php?page=postflow_settings'));
        exit;
    }

    // Get the setting value from the database
    private function get_setting_value($setting_name) {
        global $wpdb;
        $table_name_settings = $wpdb->prefix . 'postflow_settings';

        $setting_value = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name_settings WHERE setting_name = %s", $setting_name));
        return $setting_value ? $setting_value : '';
    }

    // Enqueue custom JS to toggle custom duration field visibility
    public function add_custom_js() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Check the selected Trigger Interval on page load
                toggleCustomDurationFields($('#trigger_interval').val());

                // Show/Hide custom duration fields based on the Trigger Interval
                $('#trigger_interval').on('change', function() {
                    toggleCustomDurationFields($(this).val());
                });

                // Function to toggle the custom duration fields
                function toggleCustomDurationFields(selectedValue) {
                    if (selectedValue === 'custom') {
                        $('#custom_duration_fields').show();
                    } else {
                        $('#custom_duration_fields').hide();
                    }
                }
            });
        </script>
        <?php
    }
}

// Instantiate the settings class
$postflow_settings = new PostFlow_Settings();
