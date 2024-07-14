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
        'supports'              => array('title', 'editor', 'custom-fields'),
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
if(function_exists("register_field_group"))
{
    register_field_group(array (
        'id' => 'acf_contact-form-fields',
        'title' => 'Contact Form Fields',
        'fields' => array (
            array (
                'key' => 'field_1',
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
            ),
            array (
                'key' => 'field_2',
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
            ),
            array (
                'key' => 'field_3',
                'label' => 'Message',
                'name' => 'message',
                'type' => 'textarea',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'contact_form',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array (
            'position' => 'normal',
            'layout' => 'no_box',
            'hide_on_screen' => array (
            ),
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

?>