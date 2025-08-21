<?php

/**
 * Menu Management Custom Post Type
 * 
 * Allows restaurant staff to self-serve menu updates by uploading PDFs
 * through the WordPress admin interface.
 */

// Register Menu Custom Post Type
function register_menu_post_type() {
    $labels = array(
        'name'                  => _x('Menus', 'Post Type General Name', 'twentytwentyone'),
        'singular_name'         => _x('Menu', 'Post Type Singular Name', 'twentytwentyone'),
        'menu_name'             => __('Restaurant Menus', 'twentytwentyone'),
        'name_admin_bar'        => __('Menu', 'twentytwentyone'),
        'archives'              => __('Menu Archives', 'twentytwentyone'),
        'attributes'            => __('Menu Attributes', 'twentytwentyone'),
        'parent_item_colon'     => __('Parent Menu:', 'twentytwentyone'),
        'all_items'             => __('All Menus', 'twentytwentyone'),
        'add_new_item'          => __('Add New Menu', 'twentytwentyone'),
        'add_new'               => __('Add New', 'twentytwentyone'),
        'new_item'              => __('New Menu', 'twentytwentyone'),
        'edit_item'             => __('Edit Menu', 'twentytwentyone'),
        'update_item'           => __('Update Menu', 'twentytwentyone'),
        'view_item'             => __('View Menu', 'twentytwentyone'),
        'view_items'            => __('View Menus', 'twentytwentyone'),
        'search_items'          => __('Search Menu', 'twentytwentyone'),
        'not_found'             => __('Not found', 'twentytwentyone'),
        'not_found_in_trash'    => __('Not found in Trash', 'twentytwentyone'),
        'featured_image'        => __('Featured Image', 'twentytwentyone'),
        'set_featured_image'    => __('Set featured image', 'twentytwentyone'),
        'remove_featured_image' => __('Remove featured image', 'twentytwentyone'),
        'use_featured_image'    => __('Use as featured image', 'twentytwentyone'),
        'insert_into_item'      => __('Insert into menu', 'twentytwentyone'),
        'uploaded_to_this_item' => __('Uploaded to this menu', 'twentytwentyone'),
        'items_list'            => __('Menus list', 'twentytwentyone'),
        'items_list_navigation' => __('Menus list navigation', 'twentytwentyone'),
        'filter_items_list'     => __('Filter menus list', 'twentytwentyone'),
    );

    $args = array(
        'label'                 => __('Menu', 'twentytwentyone'),
        'description'           => __('Restaurant menu management', 'twentytwentyone'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-food',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => false,
        'rest_base'             => 'menus',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('restaurant_menu', $args);
}
add_action('init', 'register_menu_post_type', 0);

// Remove Default Editor for Menu Post Type (following events pattern)
function remove_editor_from_menus() {
    $post_type = 'restaurant_menu';
    remove_post_type_support($post_type, 'editor');
}
add_action('init', 'remove_editor_from_menus', 100);

// Add custom columns to the admin list
function add_menu_admin_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['main_menu'] = __('Main Menu', 'twentytwentyone');
    $new_columns['brunch_menu'] = __('Brunch Menu', 'twentytwentyone');
    $new_columns['is_active'] = __('Active', 'twentytwentyone');
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_restaurant_menu_posts_columns', 'add_menu_admin_columns');

// Populate custom columns
function populate_menu_admin_columns($column, $post_id) {
    switch ($column) {
        case 'menu_type':
            $menu_type = get_field('menu_type', $post_id);
            echo $menu_type ? esc_html(ucfirst($menu_type)) : '—';
            break;

        case 'main_menu':
            $main_menu = get_field('main_menu_pdf', $post_id);
            if ($main_menu) {
                echo '<a href="' . esc_url($main_menu['url']) . '" target="_blank">📄 View PDF</a>';
            } else {
                echo '—';
            }
            break;

        case 'brunch_menu':
            $brunch_menu = get_field('brunch_menu_pdf', $post_id);
            if ($brunch_menu) {
                echo '<a href="' . esc_url($brunch_menu['url']) . '" target="_blank">📄 View PDF</a>';
            } else {
                echo '—';
            }
            break;

        case 'is_active':
            $is_active = get_field('is_active', $post_id);
            if ($is_active) {
                echo '<span style="color: green;">✓ Active</span>';
            } else {
                echo '<span style="color: #ccc;">○ Inactive</span>';
            }
            break;
    }
}
add_action('manage_restaurant_menu_posts_custom_column', 'populate_menu_admin_columns', 10, 2);

// Make columns sortable
function make_menu_columns_sortable($columns) {
    $columns['menu_type'] = 'menu_type';
    $columns['is_active'] = 'is_active';
    return $columns;
}
add_filter('manage_edit-restaurant_menu_sortable_columns', 'make_menu_columns_sortable');

// Add admin notice for menu management instructions
function menu_admin_notices() {
    $screen = get_current_screen();
    if ($screen->post_type === 'restaurant_menu') {
?>
        <div class="notice notice-info">
            <p><strong>Menu Management:</strong> Upload your menu PDFs here. Only one menu should be marked as "Active" at a time. The active menu will be displayed on your website automatically.</p>
        </div>
<?php
    }
}
add_action('admin_notices', 'menu_admin_notices');



// Ensure only one menu is active at a time
function ensure_single_active_menu($post_id) {
    // Only run on restaurant_menu post type
    if (get_post_type($post_id) !== 'restaurant_menu') {
        return;
    }

    // Check if this menu is being set to active
    $is_active = get_field('is_active', $post_id);

    if ($is_active) {
        // Deactivate all other menus
        $args = array(
            'post_type' => 'restaurant_menu',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post__not_in' => array($post_id), // Exclude current post
            'meta_query' => array(
                array(
                    'key' => 'is_active',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );

        $other_active_menus = get_posts($args);

        foreach ($other_active_menus as $menu) {
            update_field('is_active', false, $menu->ID);
        }

        // Add admin notice
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Menu activated!</strong> All other menus have been automatically deactivated.</p>';
            echo '</div>';
        });
    }
}
add_action('acf/save_post', 'ensure_single_active_menu', 20);

// Helper function to get the active menu
function get_active_menu() {
    $args = array(
        'post_type' => 'restaurant_menu',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'is_active',
                'value' => '1',
                'compare' => '='
            )
        ),
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        return $query->posts[0];
    }

    return null;
}

// Helper function to get menu URLs
function get_menu_urls() {
    $active_menu = get_active_menu();

    if (!$active_menu) {
        return array(
            'main_menu' => null,
            'brunch_menu' => null
        );
    }

    $main_menu = get_field('main_menu_pdf', $active_menu->ID);
    $brunch_menu = get_field('brunch_menu_pdf', $active_menu->ID);

    return array(
        'main_menu' => $main_menu ? $main_menu['url'] : null,
        'brunch_menu' => $brunch_menu ? $brunch_menu['url'] : null
    );
}

// REST API endpoint for menu data
function register_menu_rest_route() {
    register_rest_route('tolberts/v1', '/menu', array(
        'methods' => 'GET',
        'callback' => 'get_menu_rest_data',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_menu_rest_route');

function get_menu_rest_data() {
    $menu_urls = get_menu_urls();
    $active_menu = get_active_menu();

    $response = array(
        'main_menu_url' => $menu_urls['main_menu'],
        'brunch_menu_url' => $menu_urls['brunch_menu'],
        'last_updated' => $active_menu ? $active_menu->post_modified : null,
        'menu_title' => $active_menu ? $active_menu->post_title : null
    );

    return rest_ensure_response($response);
}
