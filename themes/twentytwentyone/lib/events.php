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
 * Validate Event Fields Before Saving
 * Ensures end_time is provided when publishing events
 */
function validateEventFields($post_id, $post, $update) {
    // Only validate for event post type
    if ($post->post_type !== 'event') {
        return;
    }

    // Only validate when publishing (not drafts)
    if ($post->post_status !== 'publish') {
        return;
    }

    // Get ACF fields
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

    // If there are validation errors, prevent publishing
    if (!empty($errors)) {
        // Remove the action to prevent infinite loop
        remove_action('wp_insert_post', 'validateEventFields', 10);

        // Update post status to draft
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'draft'
        ]);

        // Add the action back
        add_action('wp_insert_post', 'validateEventFields', 10, 3);

        // Set admin notice
        set_transient('event_validation_errors_' . $post_id, $errors, 30);

        // Redirect to prevent the publish action from completing
        wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&validation_error=1'));
        exit;
    }
}

add_action('wp_insert_post', 'validateEventFields', 10, 3);

/*
 * Display validation error messages in admin
 */
function displayEventValidationErrors() {
    if (isset($_GET['validation_error']) && isset($_GET['post'])) {
        $post_id = intval($_GET['post']);
        $errors = get_transient('event_validation_errors_' . $post_id);

        if ($errors) {
            echo '<div class="notice notice-error"><p><strong>Event could not be published:</strong></p><ul>';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';

            // Clean up the transient
            delete_transient('event_validation_errors_' . $post_id);
        }
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
