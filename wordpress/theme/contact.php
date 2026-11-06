<?php
if (!defined('ABSPATH')) { exit; }

function rssa_contact_send($input, $ip) {
    if (!is_array($input) || !isset($input['nonce']) || !is_string($input['nonce']) || !wp_verify_nonce($input['nonce'], 'rssa_contact')) {
        return new WP_Error('expired', 'Please refresh the page and try again.', ['status'=>403]);
    }
    foreach (['firstName', 'lastName', 'email', 'message', 'website'] as $field) {
        if (!isset($input[$field]) || !is_string($input[$field])) { return new WP_Error('invalid', 'Please complete the form.', ['status'=>400]); }
    }
    if ($input['website'] !== '') { return new WP_Error('spam', 'Unable to send this message. Please email rssa@usi.ch directly.', ['status'=>400]); }
    $first = sanitize_text_field($input['firstName']);
    $last = sanitize_text_field($input['lastName']);
    $email = trim($input['email']);
    $message = sanitize_textarea_field($input['message']);
    if (!$first || !$last || !$message || strlen($first)>100 || strlen($last)>100 || strlen($email)>254 || !is_email($email) || preg_match('/[\r\n]/', $email) || strlen($message)>10000) {
        return new WP_Error('invalid', 'Enter your name, a valid email address, and a message of up to 10,000 bytes.', ['status'=>400]);
    }
    if (untrailingslashit(home_url()) !== 'https://rssa.usi.ch') {
        return new WP_Error('preview', 'This local preview does not send email. Your message has not been sent.', ['status'=>400]);
    }
    // Serialize rate-limit updates without storing the visitor's raw IP address.
    $lock = 'rssa_contact_lock';
    if (!add_option($lock, time(), '', 'no')) {
        if ((int)get_option($lock) < time()-120) { delete_option($lock); }
        return new WP_Error('busy', 'Please wait a moment and try again.', ['status'=>429]);
    }
    try {
        $key = 'rssa_contact_' . hash_hmac('sha256', $ip, wp_salt('nonce'));
        $visitor = (int)get_transient($key);
        $total = (int)get_transient('rssa_contact_total');
        if ($visitor >= 3 || $total >= 20) { return new WP_Error('limit', 'Too many messages. Please try later or email rssa@usi.ch directly.', ['status'=>429]); }
        set_transient($key, $visitor+1, 10*MINUTE_IN_SECONDS);
        set_transient('rssa_contact_total', $total+1, HOUR_IN_SECONDS);
        try {
            $sent = wp_mail('rssa@usi.ch', 'Message via RSSA website', "Name: $first $last\nEmail: $email\n\n$message", ['From: RSSA website <wordpress@rssa.usi.ch>', 'Reply-To: ' . $email, 'Content-Type: text/plain; charset=UTF-8']);
        } catch (Throwable $error) { $sent = false; }
        if (!$sent) { return new WP_Error('mail', 'We could not send your message. Please try later or email rssa@usi.ch directly.', ['status'=>503]); }
        return ['message'=>'Thank you — your message has been submitted to RSSA.'];
    } finally { delete_option($lock); }
}
function rssa_contact_request() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_send_json_error(['message'=>'Use the contact form to send a message.'], 405); }
    $result = rssa_contact_send(wp_unslash($_POST), (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    if (is_wp_error($result)) { wp_send_json_error(['message'=>$result->get_error_message()], $result->get_error_data()['status'] ?? 400); }
    wp_send_json_success($result);
}
add_action('wp_ajax_rssa_contact', 'rssa_contact_request');
add_action('wp_ajax_nopriv_rssa_contact', 'rssa_contact_request');
