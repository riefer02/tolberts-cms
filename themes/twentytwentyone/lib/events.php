<?php
/*
 * Remove Default Editor for Event Post Type
 *
 */
function wlRemoveEditorFromEvents()
{
    $post_type = 'event';
    remove_post_type_support($post_type, 'editor');
}

add_action('init', 'wlRemoveEditorFromEvents', 100);

/*
 * Cron to set past events to 'draft' status
 *
 */
function riefCheckAndSetEventStatus()
{
    $events = get_posts([
        'post_type' => 'event',
        'numberposts' => 100,
        'post_status' => array('publish', 'future'),
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    foreach ($events as $event) {
        $fields = get_fields($event->ID);
        $date = $fields['date'];
        $date_timestamp = strtotime($date);
        $current_time = current_time('timestamp');
        $one_day_ago = strtotime('-1 day', $current_time);

        if ($one_day_ago > $date_timestamp) {
            $post = ['ID' => $event->ID, 'post_status' => 'draft'];
            wp_update_post($post);
        }
    }
}

// Action is being added at top level of functions.php file
// add_action('riefCronHook', 'riefCheckAndSetEventStatus');

if (!wp_next_scheduled('riefCronHook')) {
    wp_schedule_event(time(), 'daily', 'riefCronHook');
}