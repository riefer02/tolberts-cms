<?php
// Register Custom Post Type
function custom_contact_form_post_type() {
    $labels = array(
        'name'                  => _x('Contact Forms', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Contact Form', 'Post Type Singular Name', 'text_domain'),
    );
    $args = array(
        'label'                 => __('Contact Form', 'text_domain'),
        'description'           => __('Post Type Description', 'text_domain'),
        'labels'                => $labels,
        'supports'              => array('custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,  // Enable the REST API endpoint
    );
    register_post_type('contact_form', $args);
}
add_action('init', 'custom_contact_form_post_type', 0);


// Register Custom Fields
if (function_exists("register_field_group")) {
    register_field_group(array(
        'id' => 'acf_contact-form-fields',
        'title' => 'Contact Form Fields',
        'fields' => array(
            array(
                'key' => 'field_1',
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
            ),
            array(
                'key' => 'field_2',
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
            ),
            array(
                'key' => 'field_3',
                'label' => 'Message',
                'name' => 'message',
                'type' => 'textarea',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'contact_form',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array(
            'position' => 'normal',
            'layout' => 'no_box',
            'hide_on_screen' => array(),
        ),
        'menu_order' => 0,
    ));
}

function add_custom_fields_to_rest_response($response, $post, $request) {
    $fields = ['name', 'email', 'message']; // List your ACF field names here
    foreach ($fields as $field) {
        $value = get_field($field, $post->ID);
        $response->data[$field] = $value;
    }
    return $response;
}
add_filter('rest_prepare_contact_form', 'add_custom_fields_to_rest_response', 10, 3);

function register_acf_fields_for_rest() {
    $fields = ['name', 'email', 'message']; // Add all the ACF field keys you need

    foreach ($fields as $field) {
        register_rest_field('contact_form', $field, [
            'get_callback' => function ($object) use ($field) {
                // Get the ACF field value
                return get_field($field, $object['id']);
            },
            'update_callback' => function ($value, $object, $field_name) {
                // Update the ACF field value
                return update_field($field_name, $value, $object->ID);
            },
            'schema' => null,
        ]);
    }
}
add_action('rest_api_init', 'register_acf_fields_for_rest');

function handle_contact_form_submission(WP_REST_Request $request) {
    // Get the submitted data from the request
    $name = sanitize_text_field($request->get_param('name'));
    $email = sanitize_email($request->get_param('email'));
    $message = sanitize_textarea_field($request->get_param('message'));

    // Create a new post for the contact form submission
    $post_id = wp_insert_post(array(
        'post_title'   => $name,
        'post_type'    => 'contact_form',
        'post_status'  => 'publish',
    ));

    if (is_wp_error($post_id)) {
        return new WP_Error('post_error', 'There was an error creating the post.', array('status' => 500));
    }

    // Update the ACF fields
    update_field('field_1', $name, $post_id);
    update_field('field_2', $email, $post_id);
    update_field('field_3', $message, $post_id);

    // Log for debugging
    error_log("Name: $name, Email: $email, Message: $message");

    // Send an email with a reply and CC
    send_contact_form_email($post_id);

    // Return a success response
    return new WP_REST_Response(array(
        'status'  => 'success',
        'message' => 'Form submission successful.',
        'post_id' => $post_id,
    ), 200);
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/submit_contact_form', array(
        'methods' => 'POST',
        'callback' => 'handle_contact_form_submission',
        'permission_callback' => function (WP_REST_Request $request) {
            return is_user_logged_in();
        },
    ));
});

function send_contact_form_email($post_id) {
    if (get_post_type($post_id) != 'contact_form') {
        return;
    }

    $name = get_field('field_1', $post_id);
    $email = get_field('field_2', $post_id);
    $message = get_field('field_3', $post_id);

    // Generate a unique Message-ID and thread ID
    $message_id = '<' . time() . '.' . uniqid() . '@tolbertsrestaurant.com>';
    $thread_id = '[TID:' . substr(uniqid(), -6) . ']'; // Short, unique thread ID

    $to = $email; // Customer's email
    $cc = 'info@tolbertsrestaurant.com'; // CC email
    $subject = "Tolbert's Restaurant Contact Form: $name $thread_id"; // Include name and thread ID in subject
    $body = "Thank you for contacting Tolbert's Restaurant, $name. We appreciate your message and will get back to you as soon as possible.\n\nYour Message:\n $message";

    // Enhanced headers for better email threading and delivery
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Tolbert\'s Restaurant <info@tolbertsrestaurant.com>',
        'Reply-To: ' . $email, // Set reply-to as customer's email
        'CC: ' . $cc,
        'Message-ID: ' . $message_id,
        'References: ' . $message_id,
        'In-Reply-To: ' . $message_id,
        'Thread-Topic: Contact Form from ' . $name, // Add thread topic
        'Thread-Index: ' . base64_encode(time() . uniqid()), // Add thread index
        'X-Priority: 3'
    );

    // Log email attempt for debugging
    error_log("Sending email to: $to with Message-ID: $message_id");

    // Send the email
    $sent = wp_mail($to, $subject, $body, $headers);

    // Log the result
    if (!$sent) {
        error_log("Failed to send email to: $to");
    }

    return $sent;
}
