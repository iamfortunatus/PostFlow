<?php
class PostFlow_Content {

    // Render content management page
    public function render_content_page() {
        ?>
        <div class="wrap">
            <h1>Content Management</h1>

            <!-- Add Topic Button -->
            <button id="open-add-modal" class="postflow-button-primary">Add New Topic</button>

            <!-- Form for CSV Upload -->
            <form method="post" action="admin-post.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_content">
                <input type="file" name="csv_file" accept=".csv" class="postflow-input-file" />
                <input type="submit" value="Import Content" class="postflow-button-primary" />
            </form>

            <!-- Display Existing Topics -->
            <h2>Existing Topics</h2>
            <table class="postflow-wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $topics = $this->get_content();
                    if ($topics) {
                        foreach ($topics as $topic) {
                            echo "<tr>
                                    <td>{$topic->topic_name}</td>
                                    <td>{$topic->status}</td>
                                    <td>
                                        <button class='postflow-edit-topic' data-id='{$topic->ID}' data-title='{$topic->topic_name}' data-status='{$topic->status}'>Edit</button>
                                        <button class='postflow-remove-topic' data-id='{$topic->ID}'>Remove</button>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No content found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Success or Error Messages -->
            <?php if ( isset( $_GET['import_error_message'] ) ) { ?>
            <div class="notice notice-success is-dismissible postflow-notice-success">
                <p><?php echo esc_html( $_GET['import_error_message'] ); ?></p>
            </div>
            <?php } ?>

            <!-- Add Topic Modal -->
            <div id="add-topic-modal" class="postflow-modal" style="display:none;">
                <div class="postflow-modal-content">
                    <h2>Add New Topic</h2>
                    <form id="add-topic-form" method="post">
                        <input type="hidden" name="action" value="add_topic">
                        <label for="add_title">Title</label>
                        <input type="text" name="topic_title" id="add_title" required class="postflow-input-text">
                        <label for="add_status">Status</label>
                        <select name="topic_status" id="add_status" class="postflow-input-select">
                            <option value="pending">Pending</option>
                        </select>
                        <button type="submit" class="postflow-button-primary">Add Topic</button>
                    </form>
                    <button id="close-add-modal" class="postflow-button-close">Close</button>
                </div>
            </div>

            <!-- Edit Topic Modal -->
            <div id="edit-topic-modal" class="postflow-modal" style="display:none;">
                <div class="postflow-modal-content">
                    <h2>Edit Topic</h2>
                    <form id="edit-topic-form" method="post">
                        <input type="hidden" name="action" value="edit_topic">
                        <input type="hidden" name="topic_id" id="edit_topic_id">
                        <label for="edit_title">Title</label>
                        <input type="text" name="topic_title" id="edit_title" required class="postflow-input-text">
                        <label for="edit_status">Status</label>
                        <select name="topic_status" id="edit_status" class="postflow-input-select">
                            <option value="pending">Pending</option>
                        </select>
                        <button type="submit" class="postflow-button-primary">Save Changes</button>
                    </form>
                    <button id="close-edit-modal" class="postflow-button-close">Close</button>
                </div>
            </div>

            <!-- Remove Topic Modal -->
            <div id="remove-topic-modal" class="postflow-modal" style="display:none;">
                <div class="postflow-modal-content">
                    <h2>Are you sure you want to remove this topic?</h2>
                    <form id="remove-topic-form" method="post">
                        <input type="hidden" name="action" value="remove_topic">
                        <input type="hidden" name="topic_id" id="remove_topic_id">
                        <button type="submit" class="postflow-button-primary">Yes, Remove</button>
                    </form>
                    <button id="close-remove-modal" class="postflow-button-close">Close</button>
                </div>
            </div>

        </div>
        <?php
    }

    // Fetch content from the database
    private function get_content() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postflow_topics" );
    }

    // Register content management hooks
    public function register_content_management_hooks() {
        add_action( 'admin_post_import_content', array( $this, 'import_content' ) );
        add_action( 'admin_post_edit_topic', array( $this, 'edit_topic' ) );
        add_action( 'admin_post_remove_topic', array( $this, 'remove_topic' ) );
        add_action( 'admin_post_add_topic', array( $this, 'add_topic' ) );
    }

    // Import content from the uploaded CSV
    public function import_content() {
        if ( isset( $_FILES['csv_file'] ) && $_FILES['csv_file']['type'] === 'text/csv' ) {
            $file = $_FILES['csv_file']['tmp_name'];
            if ( ( $handle = fopen( $file, 'r' ) ) !== FALSE ) {
                $row = 0;  // Counter to skip the first row
                while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE ) {
                    // Skip the first row (headers)
                    if ($row === 0) {
                        $row++;
                        continue;
                    }

                    $title = sanitize_text_field( $data[0] ); // Title
                    $status = 'pending'; // Always set status to 'pending'

                    // Validation: Ensure the title is valid
                    if ( empty( $title ) ) {
                        $error_message = 'Invalid CSV format. Ensure the columns are "Title" and "Status".';
                        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $error_message ) ) );
                        exit;
                    }

                    // Insert data into the database
                    global $wpdb;
                    $wpdb->insert(
                        "{$wpdb->prefix}postflow_topics",
                        array(
                            'topic_name' => $title,
                            'status'     => $status,
                        ),
                        array( '%s', '%s' )
                    );
                }
                fclose( $handle );

                // Success message after import
                $success_message = 'CSV content imported successfully!';
                wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $success_message ) ) );
                exit;
            } else {
                $error_message = 'Error opening the CSV file.';
                wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $error_message ) ) );
                exit;
            }
        } else {
            $error_message = 'Please upload a valid CSV file.';
            wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $error_message ) ) );
            exit;
        }
    }

    // Add Topic
public function add_topic() {
    if ( isset( $_POST['topic_title'] ) && isset( $_POST['topic_status'] ) ) {
        $topic_title = sanitize_text_field( $_POST['topic_title'] );
        $topic_status = sanitize_text_field( $_POST['topic_status'] );

        // Insert topic into the database
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}postflow_topics",
            array(
                'topic_name' => $topic_title,
                'status' => $topic_status,
            ),
            array( '%s', '%s' )
        );

        // Redirect to success message
        $success_message = 'Topic added successfully!';
        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $success_message ) ) );
        exit;
    } else {
        $error_message = 'Title and status are required fields.';
        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $error_message ) ) );
        exit;
    }
}


    // Edit Topic
public function edit_topic() {
    if ( isset( $_POST['topic_id'] ) && isset( $_POST['topic_title'] ) && isset( $_POST['topic_status'] ) ) {
        $topic_id = intval( $_POST['topic_id'] );
        $topic_title = sanitize_text_field( $_POST['topic_title'] );
        $topic_status = sanitize_text_field( $_POST['topic_status'] );

        // Update topic in the database
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}postflow_topics",
            array(
                'topic_name' => $topic_title,
                'status' => $topic_status,
            ),
            array( 'ID' => $topic_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        // Redirect with success message
        $success_message = 'Topic updated successfully!';
        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $success_message ) ) );
        exit;
    } else {
        $error_message = 'Missing topic details for update.';
        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $error_message ) ) );
        exit;
    }
}


    // Remove Topic
    public function remove_topic() {
    if ( isset( $_POST['topic_id'] ) ) {
        $topic_id = intval( $_POST['topic_id'] );

        // Delete topic from the database
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}postflow_topics",
            array( 'ID' => $topic_id ),
            array( '%d' ) // Use integer for the condition
        );

        // Redirect with success message
        $success_message = 'Topic removed successfully!';
        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $success_message ) ) );
        exit;
    } else {
        $error_message = 'No topic ID provided for removal.';
        wp_redirect( admin_url( 'admin.php?page=postflow_content&import_error_message=' . urlencode( $error_message ) ) );
        exit;
    }
}

}

// Add custom JavaScript to handle modal actions
function postflow_modal_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Open Add Topic Modal
            $('#open-add-modal').on('click', function() {
                $('#add-topic-modal').show();
            });

            // Open Edit Topic Modal
            $('.postflow-edit-topic').on('click', function() {
                var topic_id = $(this).data('id');
                var topic_title = $(this).data('title');
                var topic_status = $(this).data('status');
                
                $('#edit_topic_id').val(topic_id);
                $('#edit_title').val(topic_title);
                $('#edit_status').val(topic_status);

                $('#edit-topic-modal').show();
            });

            // Open Remove Topic Modal
            $('.postflow-remove-topic').on('click', function() {
                var topic_id = $(this).data('id');
                $('#remove_topic_id').val(topic_id);
                $('#remove-topic-modal').show();
            });

            // Close modals
            $('#close-add-modal, #close-edit-modal, #close-remove-modal').on('click', function() {
                $(this).closest('.postflow-modal').hide();
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'postflow_modal_js');
