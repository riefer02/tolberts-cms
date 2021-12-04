<?php

add_filter('register_post_type_args', function ($args, $post_type) {
    // Change this to the post type you are adding support for
    if ('event' === $post_type) {
        $args['show_in_graphql'] = true;
        $args['graphql_single_name'] = 'event';
        $args['graphql_plural_name'] = 'events';
    }

    if ('bandmate' === $post_type) {
        $args['show_in_graphql'] = true;
        $args['graphql_single_name'] = 'bandmate';
        $args['graphql_plural_name'] = 'bandmates';
    }

    return $args;
}, 10, 2);
