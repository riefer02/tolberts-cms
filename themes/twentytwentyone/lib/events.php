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
 * Cron to set past events to 'draft' status
 *
 */
function riefCheckAndSetEventStatus() {
    $tomorrow           = new DateTime('tomorrow');
    $tomorrow_timestamp = $tomorrow->getTimestamp();
    $events             = get_posts([
        'post_type'   => 'event',
        'numberposts' => -1,
    ]);

    foreach ($events as $event) {
        $fields         = get_fields($event->ID);
        $date           = $fields['date'];
        $date_timestamp = strtotime($date);

        if ($tomorrow_timestamp < $date_timestamp) {
            $post = ['ID' => $event->ID, 'post_status' => 'draft'];
            wp_update_post($post);
        }
    }

    return;
}
// Action is being added at top level of functions.php file
// add_action('riefCronHook', 'riefCheckAndSetEventStatus');

if (!wp_next_scheduled('riefCronHook')) {
    wp_schedule_event(time(), 'daily', 'riefCronHook');
}
