<?php
if (!defined('ABSPATH')) { exit; }
function rssa_clean_gallery($input) {
    $photos = [];
    foreach (is_array($input) ? $input : [] as $item) {
        if (!is_array($item) || !isset($item['id']) || !is_scalar($item['id'])) { continue; }
        $id = absint($item['id']);
        if (!$id || !wp_attachment_is_image($id) || isset($photos[$id])) { continue; }
        $photos[$id] = ['id'=>$id, 'caption'=>sanitize_text_field(is_scalar($item['caption'] ?? null) ? $item['caption'] : ''), 'alt'=>sanitize_text_field(is_scalar($item['alt'] ?? null) ? $item['alt'] : '')];
    }
    return array_values($photos);
}
function rssa_gallery() { return rssa_clean_gallery(get_option('rssa_gallery', [])); }
function rssa_gallery_editor() {
    echo '<details open style="background:white;padding:16px;margin:16px 0;max-width:1000px"><summary style="font-size:18px;font-weight:600">Event photo gallery</summary><p>Add event photos, write optional captions, and arrange their order. Removing a photo here keeps it in the Media Library. Save changes to publish.</p><input type="hidden" name="gallery_present" value="1"><div id="rssa-gallery-list"></div><p id="rssa-gallery-empty">No gallery photos yet. The section stays hidden until you add photos.</p><button type="button" class="button" id="rssa-add-gallery">Add gallery photos</button></details>';
    $photos = array_map(function ($photo) { $photo['url'] = wp_get_attachment_image_url($photo['id'], 'medium') ?: wp_get_attachment_url($photo['id']); return $photo; }, rssa_gallery());
    echo '<script type="application/json" id="rssa-gallery-data">' . wp_json_encode($photos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
}
function rssa_render_gallery() {
    $photos = rssa_gallery();
    if (!$photos) { return; }
    echo '<section class="section-pad rssa-gallery" id="gallery" aria-labelledby="gallery-heading"><div class="container"><div class="sec-hd"><span class="sec-num">03</span><div class="rssa-gallery-title"><h2 class="sec-title" id="gallery-heading">Event photos</h2><a class="rssa-gallery-instagram" href="https://www.instagram.com/rssa_lugano/" target="_blank" rel="noopener noreferrer" aria-label="RSSA Lugano on Instagram (opens in a new tab)"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a></div></div><div class="rssa-gallery-grid" data-count="' . count($photos) . '">';
    foreach ($photos as $photo) {
        echo '<figure><a href="' . esc_url(wp_get_attachment_url($photo['id'])) . '" target="_blank" rel="noopener" aria-label="' . esc_attr('Open photo: ' . ($photo['caption'] ?: ($photo['alt'] ?: 'Event photo'))) . '">';
        $image = wp_get_attachment_image_src($photo['id'], 'large');
        $metadata = wp_get_attachment_metadata($photo['id']);
        $original = get_attached_file($photo['id']);
        $thumbnail = $metadata['sizes']['large']['file'] ?? '';
        // Playground can produce empty AVIF derivatives; retain the original as a fallback.
        if (!$thumbnail || !$original || !is_file(dirname($original) . '/' . $thumbnail) || filesize(dirname($original) . '/' . $thumbnail) === 0) {
            $image = wp_get_attachment_image_src($photo['id'], 'full');
        }
        if ($image) { echo '<img class="rssa-gallery-image" loading="lazy" decoding="async" src="' . esc_url($image[0]) . '" width="' . absint($image[1]) . '" height="' . absint($image[2]) . '" alt="' . esc_attr($photo['alt']) . '">'; }
        echo '</a>';
        if ($photo['caption']) { echo '<figcaption>' . esc_html($photo['caption']) . '</figcaption>'; }
        echo '</figure>';
    }
    echo '</div></div></section>';
}
