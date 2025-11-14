<?php
class PostFlow_Dashboard {

    public function render_dashboard_page() {
        ?>
        <div class="wrap">
            <h1>PostFlow Dashboard</h1>
            <div class="postflow-dashboard-stats">
                <h2>Content Stats</h2>
                <ul>
                    <li>Total Content Published: <?php echo get_option( 'postflow_published_count', 0 ); ?></li>
                    <li>Total Content Pending: <?php echo get_option( 'postflow_pending_count', 0 ); ?></li>
                </ul>
            </div>
            <div class="postflow-dashboard-actions">
                <h2>Actions</h2>
                <ul>
                    <li><a href="<?php echo admin_url( 'admin.php?page=postflow_content' ); ?>">Manage Content</a></li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=postflow_settings' ); ?>">Plugin Settings</a></li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=postflow_license_upgrade' ); ?>">License Upgrade</a></li>
                </ul>

                <form method="post" action="admin-post.php">
                    <input type="hidden" name="action" value="trigger_now">
                    <input type="submit" value="Trigger Now" class="button-primary" />
                </form>
            </div>
        </div>
        <?php
    }
}
