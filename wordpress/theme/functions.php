<?php
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/gallery.php';
require_once __DIR__ . '/contact.php';
function rssa_fields() {
    static $fields;
    return $fields ??= json_decode(file_get_contents(__DIR__ . '/content.json'), true);
}
function rssa_text($key) {
    $values = get_option('rssa_content', []);
    $value = $values[$key] ?? rssa_fields()[$key]['default'];
    // Preserve custom headings while updating the original imported label.
    if ($key === 'text_16' && $value === 'Latest events') { $value = 'Latest updates'; }
    return wp_kses($value, ['br'=>[], 'strong'=>[], 'em'=>[]]);
}
function rssa_image($key) {
    $values = get_option('rssa_content', []);
    $url = wp_get_attachment_image_url(absint($values[$key] ?? 0), 'full');
    return $url ?: get_theme_file_uri(rssa_fields()[$key]['default']);
}
function rssa_image_alt($key) {
    $values = get_option('rssa_content', []);
    $alt = get_post_meta(absint($values[$key] ?? 0), '_wp_attachment_image_alt', true);
    return $alt ?: rssa_fields()[$key]['alt'];
}
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
});
add_action('init', function () {
    add_role('rssa_editor', 'RSSA Content Editor', ['read'=>true, 'upload_files'=>true, 'edit_rssa_content'=>true]);
    $admin = get_role('administrator');
    if ($admin && !$admin->has_cap('edit_rssa_content')) { $admin->add_cap('edit_rssa_content'); }
});
// Keep the public design unchanged when reviewing it while signed in.
add_filter('show_admin_bar', '__return_false');
add_action('admin_menu', function () {
    add_menu_page('Website content', 'Website content', 'edit_rssa_content', 'rssa-content', 'rssa_editor_screen', 'dashicons-edit-page', 3);
});
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_rssa-content') { return; }
    wp_enqueue_media();
    wp_enqueue_script('rssa-editor', get_theme_file_uri('editor.js'), ['jquery'], '1.0.0', true);
});
add_action('admin_post_rssa_save', function () {
    if (!current_user_can('edit_rssa_content')) { wp_die('You cannot edit website content.', '', ['response'=>403]); }
    check_admin_referer('rssa_save');
    $input = isset($_POST['content']) && is_array($_POST['content']) ? wp_unslash($_POST['content']) : [];
    $clean = get_option('rssa_content', []);
    foreach (rssa_fields() as $key => $field) {
        if (!array_key_exists($key, $input) || !is_scalar($input[$key])) { continue; }
        if ($field['type'] === 'image') {
            $id = absint($input[$key]);
            if ($id === 0 || wp_attachment_is_image($id)) { $clean[$key] = $id; }
        } else {
            $clean[$key] = wp_kses($input[$key], ['br'=>[], 'strong'=>[], 'em'=>[]]);
        }
    }
    if (isset($_POST['gallery_present'])) { update_option('rssa_gallery', rssa_clean_gallery(wp_unslash($_POST['gallery'] ?? []))); }
    update_option('rssa_content', $clean);
    wp_safe_redirect(admin_url('admin.php?page=rssa-content&saved=1'));
    exit;
});
function rssa_editor_screen() {
    if (!current_user_can('edit_rssa_content')) { return; }
    $values = get_option('rssa_content', []);
    echo '<div class="wrap"><h1>Website content</h1><p>Edit the text and photos below. The page layout stays fixed. Events continue to come from Telegram.</p>';
    if (isset($_GET['saved'])) { echo '<div class="notice notice-success"><p>Website content saved.</p></div>'; }
    echo '<p><a class="button" href="' . esc_url(home_url('/')) . '" target="_blank" rel="noopener">View website</a></p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="rssa_save">';
    wp_nonce_field('rssa_save');
    echo '<p>Use the text boxes and formatting buttons below. Save changes, then select View website to check the result.</p>';
    $group = '';
    foreach (rssa_fields() as $key => $field) {
        if ($group !== $field['section']) {
            if ($group !== '') { echo '</table></details>'; }
            $group = $field['section'];
            echo '<details open style="background:white;padding:16px;margin:16px 0;max-width:1000px"><summary style="font-size:18px;font-weight:600">' . esc_html(ucfirst($group)) . '</summary><table class="form-table">';
        }
        echo '<tr><th><label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label></th><td>';
        if ($field['type'] === 'image') {
            echo '<div class="rssa-photo"><img style="max-width:200px;max-height:160px;display:block;margin-bottom:12px" src="' . esc_url(rssa_image($key)) . '" alt="Current photo"><input type="hidden" id="' . esc_attr($key) . '" name="content[' . esc_attr($key) . ']" value="' . absint($values[$key] ?? 0) . '"><button type="button" class="button rssa-select-photo">Choose or upload photo</button> <button type="button" class="button rssa-reset-photo" data-default="' . esc_url(get_theme_file_uri($field['default'])) . '">Restore original</button><p class="description">Set descriptive alt text in the media library. Photos use the existing layout and crop.</p></div>';
        } else {
            $value = $values[$key] ?? $field['default'];
            if (str_contains($field['default'], '<')) {
                wp_editor($value, $key, ['textarea_name'=>'content[' . $key . ']', 'textarea_rows'=>4, 'media_buttons'=>false, 'quicktags'=>false, 'wpautop'=>false, 'tinymce'=>['toolbar1'=>'bold italic undo redo', 'toolbar2'=>'', 'forced_root_block'=>false]]);
            } else {
                echo '<textarea class="large-text" rows="3" id="' . esc_attr($key) . '" name="content[' . esc_attr($key) . ']">' . esc_textarea(html_entity_decode($value, ENT_QUOTES, 'UTF-8')) . '</textarea>';
            }
        }
        echo '</td></tr>';
    }
    if ($group !== '') { echo '</table></details>'; }
    rssa_gallery_editor();
    submit_button('Save changes');
    echo '</form></div>';
}

add_filter('login_redirect', function ($redirect, $requested, $user) {
    if ($user instanceof WP_User && in_array('rssa_editor', $user->roles, true)) {
        return admin_url('admin.php?page=rssa-content');
    }
    return $redirect;
}, 10, 3);

add_filter('wp_robots', function ($robots) {
    if (untrailingslashit(home_url()) !== 'https://rssa.usi.ch') { $robots['noindex'] = true; unset($robots['index']); }
    return $robots;
});
