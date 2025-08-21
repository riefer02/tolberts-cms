<?php
/*
 * Remove Default Editor for Event Post Type
 *
 */
function wlRemoveEditorFromEvents() {
    $post_type = 'event';
    remove_post_type_support($post_type, 'editor');
}

add_action('init', 'wlRemoveEditorFromEvents', 100);

/*
 * Validate Event Fields After ACF Save
 * Ensures end_time is provided when publishing events
 */
function validateEventFields($post_id) {
    // Skip if not an event post type
    if (get_post_type($post_id) !== 'event') {
        return;
    }

    // Skip if not publishing
    if (get_post_status($post_id) !== 'publish') {
        return;
    }

    // Skip if user can't edit posts
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Skip during autosave or bulk edit
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Get ACF fields (they should be available now)
    $start_time = get_field('start_time', $post_id);
    $end_time = get_field('end_time', $post_id);
    $date = get_field('date', $post_id);

    $errors = [];

    // Validate required fields
    if (empty($date)) {
        $errors[] = 'Event date is required.';
    }

    if (empty($start_time)) {
        $errors[] = 'Start time is required.';
    }

    if (empty($end_time)) {
        $errors[] = 'End time is required.';
    }

    // Validate that end time is after start time
    if (!empty($start_time) && !empty($end_time)) {
        $start_timestamp = strtotime($start_time);
        $end_timestamp = strtotime($end_time);

        if ($end_timestamp <= $start_timestamp) {
            $errors[] = 'End time must be after start time.';
        }
    }

    // If there are validation errors, revert to draft
    if (!empty($errors)) {
        // Unhook to prevent infinite loop
        remove_action('acf/save_post', 'validateEventFields', 20);

        // Update post status to draft
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'draft'
        ]);

        // Store errors for display
        update_post_meta($post_id, '_event_validation_errors', $errors);

        // Re-hook
        add_action('acf/save_post', 'validateEventFields', 20);
    } else {
        // Clear any previous errors
        delete_post_meta($post_id, '_event_validation_errors');
    }
}

// Use ACF's save_post hook which fires after fields are saved
add_action('acf/save_post', 'validateEventFields', 20);

/*
 * Display validation error messages in admin
 */
function displayEventValidationErrors() {
    $screen = get_current_screen();

    // Only show on event edit screens
    if (!$screen || $screen->post_type !== 'event' || $screen->base !== 'post') {
        return;
    }

    // Get post ID from URL parameter
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;

    if (!$post_id) {
        return;
    }

    $errors = get_post_meta($post_id, '_event_validation_errors', true);

    if ($errors && is_array($errors)) {
        echo '<div class="notice notice-error"><p><strong>Event validation failed and was saved as draft:</strong></p><ul>';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul></div>';

        // Clear errors after displaying
        delete_post_meta($post_id, '_event_validation_errors');
    }
}

add_action('admin_notices', 'displayEventValidationErrors');

/*
 * Cron to set past events to 'draft' status
 *
 */
// function riefCheckAndSetEventStatus()
// {
//     $events = get_posts([
//         'post_type' => 'event',
//         'numberposts' => 100,
//         'post_status' => array('publish', 'future'),
//         'orderby' => 'date',
//         'order' => 'DESC',
//     ]);

//     foreach ($events as $event) {
//         $fields = get_fields($event->ID);
//         $date = $fields['date'];
//         $date_timestamp = strtotime($date);
//         $current_time = current_time('timestamp');
//         $one_day_ago = strtotime('-1 day', $current_time);

//         if ($one_day_ago > $date_timestamp) {
//             $post = ['ID' => $event->ID, 'post_status' => 'draft'];
//             wp_update_post($post);
//         }
//     }
// }

function riefCheckAndSetEventStatus() {
    echo '<h2>Checking and Setting Event Status...</h2>';

    $events = get_posts([
        'post_type' => 'event',
        'numberposts' => -1,
        'post_status' => array('publish', 'future'),
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    echo '<p>Found ' . count($events) . ' events to check.</p>';

    foreach ($events as $event) {
        $fields = get_fields($event->ID);
        $date = $fields['date'];
        $date_timestamp = strtotime($date);
        $current_time = current_time('timestamp');
        $one_day_ago = strtotime('-1 day', $current_time);

        echo '<p>Checking event ' . $event->ID . ' (' . $event->post_title . ') with date ' . $date . '...</p>';

        if ($one_day_ago > $date_timestamp) {
            echo '<p>Updating event ' . $event->ID . ' to draft status...</p>';

            $post = ['ID' => $event->ID, 'post_status' => 'draft'];
            wp_update_post($post);
        } else {
            echo '<p>Event ' . $event->ID . ' is still active.</p>';
        }
    }

    echo '<p>Finished checking and setting event status.</p>';
}

// Action is being added at top level of functions.php file
// add_action('riefCronHook', 'riefCheckAndSetEventStatus');

if (!wp_next_scheduled('riefCronHook')) {
    // Calculate midnight in local timezone (Central Time)
    $local_midnight = strtotime('tomorrow midnight', current_time('timestamp'));
    wp_schedule_event($local_midnight, 'daily', 'riefCronHook');
}
