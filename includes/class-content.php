<?php
class PostFlow_Content {

    // Render content management page
   public function render_content_page() {
    // Get current page or default to page 1
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $per_page = 20;  // Set number of items per page

    // Fetch topics with pagination
    $topics_data = $this->get_content($current_page, $per_page);
    $topics = $topics_data['topics'];
    $total_pages = $topics_data['total_pages'];
    ?>

    <div class="wrap">
        <h1>Content Management</h1>

        <h4>Import titles (only .csv file allowed)</h4>
        <!-- Form for CSV Upload -->
        <form method="post" action="admin-post.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import_content">
            <input type="file" name="csv_file" accept=".csv" class="postflow-input-file" />
            <input type="submit" value="Import Content" class="postflow-button-primary" />
        </form>

        <h4>Add new title manually</h4>
        <!-- Add Topic Button -->
        <button id="open-add-modal" class="postflow-button-primary">Add New Title</button>

        

        <!-- Display Existing Topics -->
        <h2>Existing Blog Titles</h2>
        <!-- Bulk Actions and Delete Button -->
        <form method="post" action="admin-post.php" id="bulk-actions-form">
            <input type="hidden" name="action" value="bulk_delete_topics">
            <button type="submit" id="bulk-delete-button" class="postflow-button-danger" disabled>Bulk Delete</button>
            <label>
                <input type="checkbox" id="select-all-topics"> Select All
            </label>
        </form>
        <table class="postflow-wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-topics-header" /></th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($topics) {
                    foreach ($topics as $topic) {
                        echo "<tr>
                                <td><input type='checkbox' class='topic-checkbox' data-id='{$topic->ID}' /></td>
                                <td>{$topic->topic_name}</td>
                                <td>{$topic->status}</td>
                                <td>
                                    <button class='postflow-edit-topic' data-id='{$topic->ID}' data-title='{$topic->topic_name}' data-status='{$topic->status}'>Edit</button>
                                    <button class='postflow-remove-topic' data-id='{$topic->ID}'>Delete</button>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No content found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Success or Error Messages -->
        <?php
        // Check if there is any message set in the transient
        $import_message = get_transient('postflow_import_message');
        if ($import_message) {
            $message = $import_message['message'];
            $message_type = $import_message['type'];

            // Display the message with the appropriate class based on type
            echo '<div class="notice notice-' . $message_type . ' is-dismissible postflow-notice-' . $message_type . '">
                    <p>' . esc_html($message) . '</p>
                  </div>';

            // Clear the transient after it's displayed
            delete_transient('postflow_import_message');
        }
        ?>

        <!-- Pagination -->
        <div class="postflow-pagination">
            <?php
            if ($total_pages > 1) {
                $base_url = admin_url('admin.php?page=postflow_content');

                // Generate previous page link
                if ($current_page > 1) {
                    echo '<a href="' . add_query_arg('paged', $current_page - 1, $base_url) . '">&laquo; Previous</a>';
                }

                // Generate page links
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo '<a href="' . add_query_arg('paged', $i, $base_url) . '"';
                    if ($i == $current_page) {
                        echo ' class="current"';
                    }
                    echo ">$i</a> ";
                }

                // Generate next page link
                if ($current_page < $total_pages) {
                    echo '<a href="' . add_query_arg('paged', $current_page + 1, $base_url) . '">Next &raquo;</a>';
                }
            }
            ?>
        </div>
    </div>

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
            <h2>Are you sure you want to delete this topic?</h2>
            <form id="remove-topic-form" method="post">
                <input type="hidden" name="action" value="remove_topic">
                <input type="hidden" name="topic_id" id="remove_topic_id">
                <button type="submit" class="postflow-button-primary">Yes, Delete</button>
            </form>
            <button id="close-remove-modal" class="postflow-button-close">Close</button>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
<div id="bulk-delete-warning-modal" class="postflow-modal" style="display:none;">
    <div class="postflow-modal-content">
        <h2>Are you sure you want to delete the selected topics?</h2>
        <button id="bulk-delete-confirm" class="postflow-button-danger">Yes, Delete</button>
        <button id="close-bulk-delete-modal" class="postflow-button-close">Cancel</button>
    </div>
</div>


    <?php
}


    // Fetch content from the database with pagination
    private function get_content($current_page = 1, $per_page = 20) {
        global $wpdb;

        // Calculate the OFFSET for pagination
        $offset = ($current_page - 1) * $per_page;

        // Fetch topics with pagination
        $topics = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}postflow_topics LIMIT %d OFFSET %d",
                $per_page, $offset
            )
        );

        // Get the total number of topics
        $total_topics = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postflow_topics");

        // Calculate the total number of pages
        $total_pages = ceil($total_topics / $per_page);

        return ['topics' => $topics, 'total_pages' => $total_pages];
    }

    // Register content management hooks
   public function register_content_management_hooks() {
    add_action('admin_post_import_content', array($this, 'import_content'));
    add_action('wp_ajax_add_topic', array($this, 'add_topic'));
    add_action('wp_ajax_edit_topic', array($this, 'edit_topic'));
    add_action('wp_ajax_remove_topic', array($this, 'remove_topic'));
    add_action('wp_ajax_bulk_delete_topics', array($this, 'bulk_delete_topics'));
}

public function bulk_delete_topics() {
    if (isset($_POST['topic_ids']) && is_array($_POST['topic_ids'])) {
        global $wpdb;

        // Delete the selected topics
        $topic_ids = array_map('intval', $_POST['topic_ids']);
        $placeholders = implode(',', array_fill(0, count($topic_ids), '%d'));
        $query = "DELETE FROM {$wpdb->prefix}postflow_topics WHERE ID IN ($placeholders)";
        $wpdb->query($wpdb->prepare($query, ...$topic_ids));

        // Send a success response
        echo json_encode(['message' => 'Selected topics have been deleted successfully.']);
    }
    wp_die(); // End the AJAX request
}


    // Import content from the uploaded CSV
    public function import_content() {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['type'] === 'text/csv') {
            $file = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($file, 'r')) !== FALSE) {
                $row = 0;  // Counter to skip the first row
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Skip the first row (headers)
                    if ($row === 0) {
                        $row++;
                        continue;
                    }

                    $title = sanitize_text_field($data[0]); // Title
                    $status = 'pending'; // Always set status to 'pending'

                    // Validation: Ensure the title is valid
                    if (empty($title)) {
                        $error_message = 'Invalid CSV format. Ensure the columns are "Title" and "Status".';
                        set_transient('postflow_import_message', array('message' => $error_message, 'type' => 'error'), 60); // Store transient for 1 minute
                        wp_redirect(admin_url('admin.php?page=postflow_content'));
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
                        array('%s', '%s')
                    );
                }
                fclose($handle);

                // Success message after import
                $success_message = 'CSV content imported successfully!';
                set_transient('postflow_import_message', array('message' => $success_message, 'type' => 'success'), 60); // Store transient for 1 minute
                wp_redirect(admin_url('admin.php?page=postflow_content'));
                exit;
            } else {
                $error_message = 'Error opening the CSV file.';
                set_transient('postflow_import_message', array('message' => $error_message, 'type' => 'error'), 60); // Store transient for 1 minute
                wp_redirect(admin_url('admin.php?page=postflow_content'));
                exit;
            }
        } else {
            $error_message = 'Please upload a valid CSV file.';
            set_transient('postflow_import_message', array('message' => $error_message, 'type' => 'error'), 60); // Store transient for 1 minute
            wp_redirect(admin_url('admin.php?page=postflow_content'));
            exit;
        }
    }



// Add Topic via AJAX
function add_topic() {
    if ( isset( $_POST['topic_title'] ) && isset( $_POST['topic_status'] ) ) {
        $topic_title = sanitize_text_field( $_POST['topic_title'] );
        $topic_status = sanitize_text_field( $_POST['topic_status'] );

        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}postflow_topics",
            array(
                'topic_name' => $topic_title,
                'status' => $topic_status,
            ),
            array( '%s', '%s' )
        );

        // Return success message
        echo json_encode(array('message' => 'Topic added successfully!'));
    } else {
        echo json_encode(array('message' => 'Title and status are required fields.'));
    }
    wp_die(); // Ensure AJAX request is terminated properly
}

// Edit Topic via AJAX
function edit_topic() {
    if ( isset( $_POST['topic_id'] ) && isset( $_POST['topic_title'] ) && isset( $_POST['topic_status'] ) ) {
        $topic_id = intval( $_POST['topic_id'] );
        $topic_title = sanitize_text_field( $_POST['topic_title'] );
        $topic_status = sanitize_text_field( $_POST['topic_status'] );

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

        // Return success message
        echo json_encode(array('message' => 'Topic updated successfully!'));
    } else {
        echo json_encode(array('message' => 'Missing topic details for update.'));
    }
    wp_die(); // Ensure AJAX request is terminated properly
}

// Remove Topic via AJAX
function remove_topic() {
    if ( isset( $_POST['topic_id'] ) ) {
        $topic_id = intval( $_POST['topic_id'] );

        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}postflow_topics",
            array( 'ID' => $topic_id ),
            array( '%d' )
        );

        // Return success message
        echo json_encode(array('message' => 'Topic removed successfully!'));
    } else {
        echo json_encode(array('message' => 'No topic ID provided for removal.'));
    }
    wp_die(); // Ensure AJAX request is terminated properly
}


}

function postflow_modal_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Open Add Topic Modal
            $('#open-add-modal').on('click', function() {
                $('#add-topic-modal').show(); // Show the Add Topic modal
            });

            // Open Edit Topic Modal
            $('.postflow-edit-topic').on('click', function() {
                var topic_id = $(this).data('id');
                var topic_title = $(this).data('title');
                var topic_status = $(this).data('status');
                
                // Populate the Edit modal with the current topic data
                $('#edit_topic_id').val(topic_id);
                $('#edit_title').val(topic_title);
                $('#edit_status').val(topic_status);

                // Show the Edit Topic modal
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
                $(this).closest('.postflow-modal').hide(); // Close the respective modal
            });

            // Handle Add Topic form submission with AJAX
            $('#add-topic-form').on('submit', function(e) {
                e.preventDefault();
                var data = {
                    action: 'add_topic',  // The action hook for adding a topic
                    topic_title: $('#add_title').val(),
                    topic_status: $('#add_status').val(),
                };
                $.post(ajaxurl, data, function(response) {
                    var res = JSON.parse(response);  // Parse the response
                    if(res.message) {
                        alert(res.message); // Display the success message
                        location.reload(); // Reload the page to reflect the new topic
                    } else {
                        alert('There was an error while adding the topic.');
                    }
                });
            });

            // Handle Edit Topic form submission with AJAX
            $('#edit-topic-form').on('submit', function(e) {
                e.preventDefault();
                var data = {
                    action: 'edit_topic',  // The action hook for editing a topic
                    topic_id: $('#edit_topic_id').val(),
                    topic_title: $('#edit_title').val(),
                    topic_status: $('#edit_status').val(),
                };
                $.post(ajaxurl, data, function(response) {
                    var res = JSON.parse(response);  // Parse the response
                    if(res.message) {
                        alert(res.message); // Display the success message
                        location.reload(); // Reload the page to reflect the changes
                    } else {
                        alert('There was an error while editing the topic.');
                    }
                });
            });

            // Handle Remove Topic form submission with AJAX
            $('#remove-topic-form').on('submit', function(e) {
                e.preventDefault();
                var data = {
                    action: 'remove_topic',  // The action hook for removing a topic
                    topic_id: $('#remove_topic_id').val(),
                };
                $.post(ajaxurl, data, function(response) {
                    var res = JSON.parse(response);  // Parse the response
                    if(res.message) {
                        alert(res.message); // Display the success message
                        location.reload(); // Reload the page to reflect the removal
                    } else {
                        alert('There was an error while removing the topic.');
                    }
                });
            });

            // Toggle "Select All" checkbox functionality
            $('#select-all-topics, #select-all-topics-header').on('change', function() {
                var isChecked = $(this).prop('checked');
                $('.topic-checkbox').prop('checked', isChecked);
                toggleBulkDeleteButton();
            });

            // Enable/Disable the bulk delete button based on selected checkboxes
            $('.topic-checkbox').on('change', function() {
                toggleBulkDeleteButton();
            });

            // Check if any checkboxes are selected and enable/disable the bulk delete button
            function toggleBulkDeleteButton() {
                var selectedCount = $('.topic-checkbox:checked').length;
                if (selectedCount > 0) {
                    $('#bulk-delete-button').prop('disabled', false);  // Enable the button
                } else {
                    $('#bulk-delete-button').prop('disabled', true);   // Disable the button
                }
            }

            // Show warning modal for bulk delete
            $('#bulk-delete-button').on('click', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Get selected topic IDs
                var selectedIds = [];
                $('.topic-checkbox:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length > 0) {
                    // Show the warning modal for confirmation
                    $('#bulk-delete-warning-modal').show();
                    $('#bulk-delete-confirm').on('click', function() {
                        // Proceed with the bulk delete
                        var data = {
                            action: 'bulk_delete_topics',  // The action hook for bulk delete
                            topic_ids: selectedIds,
                        };

                        $.post(ajaxurl, data, function(response) {
                            $('#bulk-delete-warning-modal').hide(); // Hide warning modal
                            location.reload(); // Reload the page to reflect changes after bulk delete
                        });
                    });
                } else {
                    alert('Please select at least one topic to delete.');
                }
            });

            // Close the warning modal
            $('#close-bulk-delete-modal').on('click', function() {
                $('#bulk-delete-warning-modal').hide(); // Hide the warning modal
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'postflow_modal_js');
