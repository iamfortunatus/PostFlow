<?php
class PostFlow_License {

    public function render_license_upgrade_page() {
        ?>
        <div class="wrap">
            <h1>License Upgrade</h1>
            <p>Your current license allows for <?php echo get_option( 'postflow_posts_per_month', '0' ); ?> posts per month.</p>

            <h2>Upgrade to Pro or Scale</h2>
            <ul>
                <li><strong>Pro:</strong> 500 posts per month - <a href="https://yourplugin.com/upgrade?plan=pro">Upgrade to Pro</a></li>
                <li><strong>Scale:</strong> Unlimited posts per month - <a href="https://yourplugin.com/upgrade?plan=scale">Upgrade to Scale</a></li>
            </ul>

            <h3>Current License Key: <?php echo get_option( 'postflow_license_key', 'N/A' ); ?></h3>
            <p><a href="<?php echo admin_url( 'admin.php?page=postflow_settings' ); ?>">Change License Key</a></p>
        </div>
        <?php
    }
}
