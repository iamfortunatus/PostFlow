<?php
class PostFlow_Settings {

    // This is your method to render the settings page
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>PostFlow Settings</h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'postflow_settings_group' );
                    do_settings_sections( 'postflow' ); // You can remove this if not using sections
                ?>
                <table class="form-table">
                    <!-- License Key Setting -->
                    <tr valign="top">
                        <th scope="row">License Key</th>
                        <td><input type="text" name="postflow_license_key" value="<?php echo esc_attr( get_option( 'postflow_license_key' ) ); ?>" /></td>
                    </tr>

                    <!-- WordPress Admin Username -->
                    <tr valign="top">
                        <th scope="row">WordPress Admin Username</th>
                        <td><input type="text" name="postflow_wp_username" value="<?php echo esc_attr( get_option( 'postflow_wp_username' ) ); ?>" /></td>
                    </tr>

                    <!-- Trigger Interval Settings -->
                    <tr valign="top">
                        <th scope="row">Trigger Interval</th>
                        <td>
                            <select name="postflow_trigger_interval" id="trigger_interval">
                                <option value="hourly" <?php selected( get_option( 'postflow_trigger_interval' ), 'hourly' ); ?>>Every Hour</option>
                                <option value="daily" <?php selected( get_option( 'postflow_trigger_interval' ), 'daily' ); ?>>Every Day</option>
                                <option value="weekly" <?php selected( get_option( 'postflow_trigger_interval' ), 'weekly' ); ?>>Every Week</option>
                                <option value="custom" <?php selected( get_option( 'postflow_trigger_interval' ), 'custom' ); ?>>Custom Duration</option>
                            </select>
                            <div id="custom_duration_fields" style="display: none;">
                                <input type="number" name="postflow_custom_duration" value="<?php echo esc_attr( get_option( 'postflow_custom_duration' ) ); ?>" placeholder="Enter value" id="custom_duration" />
                                <select name="postflow_custom_duration_unit" id="custom_duration_unit">
                                    <option value="minutes" <?php selected( get_option( 'postflow_custom_duration_unit' ), 'minutes' ); ?>>Minutes</option>
                                    <option value="days" <?php selected( get_option( 'postflow_custom_duration_unit' ), 'days' ); ?>>Days</option>
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Application Password Setting -->
                    <tr valign="top">
                        <th scope="row">Application Password</th>
                        <td><input type="password" name="postflow_application_password" value="<?php echo esc_attr( get_option( 'postflow_application_password' ) ); ?>" /></td>
                    </tr>

                    <!-- WordPress URL Setting -->
                    <tr valign="top">
                        <th scope="row">WordPress URL</th>
                        <td><input type="text" name="postflow_wp_url" value="<?php echo esc_attr( home_url() ); ?>" readonly /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
