<?php
class PostFlow_N8n {

    public function trigger_workflow() {
    try {
        $next_topic = $this->get_next_pending_topic();
        if ( !$next_topic ) {
            throw new Exception('No pending topics available for generation.');
        }

        // Trigger n8n workflow
        $response = $this->send_to_n8n( $next_topic );
        
        if ( $response['status'] !== 'success' ) {
            throw new Exception('Failed to trigger n8n workflow.');
        }

        // Update post status to published
        $this->update_post_status( $next_topic, 'published' );
    } catch ( Exception $e ) {
        // Handle the error: Save the error message in the session or log
        $_SESSION['import_error_message'] = $e->getMessage();
        wp_redirect( admin_url( 'admin.php?page=postflow_dashboard' ) );
        exit;
    }
}


    private function get_next_pending_topic() {
        global $wpdb;
        return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}postflow_topics WHERE status = 'pending' LIMIT 1" );
    }

    private function send_to_n8n( $topic ) {
        // Send request to n8n API to trigger content generation
        $response = wp_remote_post( 'https://your-n8n-instance.com/webhook/generate-content', array(
            'method'    => 'POST',
            'body'      => json_encode( array( 'topic' => $topic->topic_name ) ),
            'headers'   => array( 'Content-Type' => 'application/json' ),
        ) );
        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    private function update_post_status( $topic, $status ) {
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}postflow_topics",
            array( 'status' => $status ),
            array( 'ID' => $topic->ID ),
            array( '%s' ),
            array( '%d' )
        );
    }
}
