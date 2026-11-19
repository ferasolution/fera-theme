<?php
/** Targeted Services addition. Existing content, menus and Reading settings stay intact. */
if (!defined('ABSPATH')) { exit; }

function ff_consultation_url() {
    return ff_url('contact') . '#inquiry';
}

function ff_services_page_id() {
    $id = absint(get_option('ff_services_page_id', 0));
    return $id && get_post_type($id) === 'page' && get_post_status($id) !== 'trash' ? $id : 0;
}

function ff_services_published() {
    $id = ff_services_page_id();
    return $id && get_post_status($id) === 'publish';
}

function ff_primary_nav_labels() {
    return ff_nav_labels() + (ff_services_published() ? ['services' => 'Services'] : []);
}

add_filter('wp_nav_menu_items', function ($items, $args) {
    if (($args->theme_location ?? '') !== 'foundry_primary' || !ff_services_published()) {
        return $items;
    }
    // Preserve hand-configured menu items and dropdowns; do not add a second Services link.
    $url = ff_url('services');
    if (preg_match_all('/href\s*=\s*([\'"])(.*?)\1/i', $items, $links)) {
        foreach ($links[2] as $href) {
            $href = html_entity_decode($href, ENT_QUOTES, 'UTF-8');
            if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
                $home = wp_parse_url(home_url());
                $href = $home['scheme'] . '://' . $home['host'] . (isset($home['port']) ? ':' . $home['port'] : '') . $href;
            }
            if (untrailingslashit($href) === untrailingslashit($url)) {
                return $items;
            }
        }
    }
    $current = get_queried_object_id() === ff_services_page_id() ? ' aria-current="page"' : '';
    return $items . '<li class="menu-item ff-services-menu-item"><a href="' . esc_url($url) . '"' . $current . '>Services</a></li>';
}, 10, 2);

function ff_create_services_draft() {
    if (!current_user_can('manage_options')) {
        return new WP_Error('ff_services_permission', 'Administrator permission required.');
    }
    if ($id = ff_services_page_id()) { return $id; }
    // Atomic option prevents two simultaneous setup requests from creating duplicates.
    if (!add_option('ff_services_setup_lock', time(), '', false)) {
        return new WP_Error('ff_services_busy', 'Setup is already running. Please retry shortly. If interrupted, ask your administrator to clear the Services setup lock.');
    }
    try {
        if ($id = ff_services_page_id()) { return $id; }
        if (get_page_by_path('services', OBJECT, ['page', 'post']) || url_to_postid(home_url('/services/'))) {
            return new WP_Error('ff_services_conflict', 'The services address is already in use. Map your existing page below; no content has been overwritten.');
        }
        $data = ff_resolve(ff_content('services'));
        if (!$data) { return new WP_Error('ff_services_content', 'Bundled Services content is unavailable. Restore the update files before trying again.'); }
        $id = wp_insert_post([
            'post_type' => 'page', 'post_title' => 'Our Services',
            'post_name' => 'services', 'post_status' => 'draft',
        ], true);
        if (is_wp_error($id)) { return $id; }
        update_option('ff_services_page_id', $id, false);
        update_post_meta($id, '_ff_slug', 'services');
        update_post_meta($id, '_ff_content', wp_slash(wp_json_encode($data)));
        $enabled = ff_enable_elementor($id, $data);
        update_post_meta($id, '_ff_initialized', 1);
        // Theme fallback renders the same content when Elementor storage is unavailable.
        if (is_wp_error($enabled)) {
            update_option('ff_services_notice', $enabled->get_error_message(), false);
        }
        return $id;
    } finally {
        delete_option('ff_services_setup_lock');
    }
}

add_action('admin_menu', function () {
    add_theme_page('Fera Services', 'Fera Services', 'manage_options', 'fera-services', 'ff_services_setup_screen');
});

function ff_services_setup_screen() {
    if (!current_user_can('manage_options')) { return; }
    $id = ff_services_page_id();
    echo '<div class="wrap"><h1>Fera Services</h1><p>Create one reviewable Services draft, or map an existing page. This does not publish anything or change your homepage, existing content, footer or saved menus.</p>';
    if ($notice = get_option('ff_services_notice')) {
        echo '<div class="notice notice-warning"><p>' . esc_html($notice) . '</p></div>';
    }
    if ($id) {
        echo '<p>Mapped page: <a href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html(get_the_title($id)) . '</a> (' . esc_html(get_post_status($id)) . ').</p><p>Review the page, then use WordPress Publish when you are ready. Services appears in primary navigation only after publication. Consultation buttons link to your existing Contact inquiry form.</p>';
    } else {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('ff_services_setup');
        echo '<input type="hidden" name="action" value="ff_services_setup"><input type="hidden" name="operation" value="create">';
        submit_button('Create Services draft');
        echo '</form>';
    }
    echo '<h2>Use an existing Services page</h2><p>Mapping preserves its current content and Elementor data. Select only the intended Services page. No other page is modified.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('ff_services_setup');
    echo '<input type="hidden" name="action" value="ff_services_setup"><input type="hidden" name="operation" value="map"><label for="ff-services-existing">Existing page</label> ';
    wp_dropdown_pages(['name' => 'page_id', 'id' => 'ff-services-existing', 'selected' => $id, 'show_option_none' => 'Choose a page', 'option_none_value' => 0, 'post_status' => ['publish', 'draft', 'pending', 'private']]);
    submit_button('Map selected page', 'secondary');
    echo '</form></div>';
}

add_action('admin_post_ff_services_setup', function () {
    if (!current_user_can('manage_options')) { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ff_services_setup');
    $operation = sanitize_key(wp_unslash($_POST['operation'] ?? ''));
    if ($operation === 'create') {
        $result = ff_create_services_draft();
    } elseif ($operation === 'map') {
        $id = absint($_POST['page_id'] ?? 0);
        if (!$id || get_post_type($id) !== 'page' || !in_array(get_post_status($id), ['publish', 'draft', 'pending', 'private'], true) || !current_user_can('edit_post', $id)) {
            $result = new WP_Error('ff_services_page', 'Choose an existing editable page.');
        } else {
            update_option('ff_services_page_id', $id, false);
            $result = $id;
        }
    } else {
        $result = new WP_Error('ff_services_operation', 'Unsupported setup operation.');
    }
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message())); }
    wp_safe_redirect(admin_url('themes.php?page=fera-services'));
    exit;
});
