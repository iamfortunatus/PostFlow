<?php
class PostFlow_Cron {

    public function init() {
        $trigger_interval = get_option( 'postflow_trigger_interval', 'hourly' );
        $custom_duration = get_option( 'postflow_custom_duration', 60 ); // Default to 60 minutes
        $custom_duration_unit = get_option( 'postflow_custom_duration_unit', 'minutes' );

        // Calculate the interval for custom duration in seconds
        if ( $trigger_interval === 'custom') {
            $interval = $this->convert_to_seconds( $custom_duration, $custom_duration_unit );
        } else {
            // Use default intervals for daily, hourly, or weekly
            switch ( $trigger_interval ) {
                case 'hourly':
                    $interval = HOUR_IN_SECONDS;
                    break;
                case 'daily':
                    $interval = DAY_IN_SECONDS;
                    break;
                case 'weekly':
                    $interval = WEEK_IN_SECONDS;
                    break;
                default:
                    $interval = HOUR_IN_SECONDS;
            }
        }

        if ( ! wp_next_scheduled( 'postflow_scheduled_trigger' ) ) {
            wp_schedule_event( time(), $interval, 'postflow_scheduled_trigger' );
        }

        add_action( 'postflow_scheduled_trigger', array( $this, 'trigger_content_generation' ) );
    }

    public function trigger_content_generation() {
        // Trigger content generation, calling the n8n workflow
        $this->n8n->trigger_workflow();
    }

    private function convert_to_seconds( $duration, $unit ) {
        if ( $unit === 'days' ) {
            return $duration * DAY_IN_SECONDS;
        } else { // Default is minutes
            return $duration * MINUTE_IN_SECONDS;
        }
    }
}
