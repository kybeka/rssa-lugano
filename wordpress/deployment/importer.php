<?php
/**
 * Plugin Name: RSSA Content Transfer
 * Description: One-time import of the prepared RSSA content and photos. Remove after migration.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) { exit; }
function rssa_transfer_import() {
    if (!current_user_can('manage_options')) { return new WP_Error('permission', 'Administrator access required.'); }
    if (!function_exists('rssa_fields')) { return new WP_Error('theme', 'Activate the RSSA theme first.'); }
    if (get_option('rssa_transfer_complete')) { return new WP_Error('done', 'This transfer has already completed.'); }
    $data = json_decode(file_get_contents(__DIR__ . '/content.json'), true);
    if (!is_array($data) || ($data['format'] ?? '') !== 'rssa-transfer-1') { return new WP_Error('format', 'Invalid content package.'); }
    if (get_option('rssa_content', false) !== false || get_option('rssa_gallery', false) !== false) { return new WP_Error('existing', 'This importer only supports a fresh RSSA installation. Existing content was not changed.'); }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $map = []; $created = [];
    foreach ($data['media'] as $media) {
        $path = __DIR__ . '/media/' . basename($media['file']);
        if (!is_file($path) || hash_file('sha256', $path) !== $media['sha256']) { $error = new WP_Error('media', 'A packaged photo is missing or damaged.'); break; }
        $tmp = wp_tempnam($media['file']);
        if (!$tmp || !copy($path, $tmp)) { $error = new WP_Error('temp', 'Could not prepare a photo for import.'); break; }
        $id = media_handle_sideload(['name'=>basename($media['file']), 'tmp_name'=>$tmp], 0, sanitize_text_field($media['title']));
        if (is_wp_error($id)) { @unlink($tmp); $error = $id; break; }
        $created[] = $id;
        if (!wp_attachment_is_image($id)) { $error = new WP_Error('image', 'Only photos may be imported.'); break; }
        update_post_meta($id, '_wp_attachment_image_alt', sanitize_text_field($media['alt']));
        $map[(string)$media['id']] = $id;
    }
    if (isset($error)) {
        foreach ($created as $id) { wp_delete_attachment($id, true); }
        return $error;
    }
    $content = [];
    foreach (rssa_fields() as $key=>$field) {
        $value = $data['content'][$key] ?? $field['default'];
        $content[$key] = $field['type'] === 'image' ? ($map[(string)$value] ?? 0) : wp_kses($value, ['br'=>[], 'strong'=>[], 'em'=>[]]);
    }
    $gallery = [];
    foreach ($data['gallery'] as $photo) {
        if (isset($map[(string)$photo['id']])) { $photo['id'] = $map[(string)$photo['id']]; $gallery[] = $photo; }
    }
    update_option('rssa_content', $content);
    update_option('rssa_gallery', rssa_clean_gallery($gallery));
    update_option('rssa_transfer_complete', true);
    return count($created);
}
add_action('admin_menu', function () {
    add_management_page('RSSA content transfer', 'RSSA content transfer', 'manage_options', 'rssa-transfer', function () {
        echo '<div class="wrap"><h1>RSSA content transfer</h1><p>For a fresh RSSA installation only. Imports website content and photos, without users, passwords, or site settings. Back up the database and uploads before proceeding.</p>';
        if (isset($_POST['rssa_transfer'])) {
            check_admin_referer('rssa_transfer');
            $result = rssa_transfer_import();
            echo '<p>' . esc_html(is_wp_error($result) ? $result->get_error_message() : 'Import complete. Review the website, then deactivate and delete this plugin.') . '</p>';
        }
        echo '<form method="post">'; wp_nonce_field('rssa_transfer');
        submit_button('Import prepared content', 'primary', 'rssa_transfer');
        echo '</form></div>';
    });
});
