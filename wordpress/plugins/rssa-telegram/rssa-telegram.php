<?php
/**
 * Plugin Name: RSSA Telegram Announcements
 * Description: Receives RSSA channel posts and edits, with locally stored photos and editor visibility controls.
 * Version: 0.3.0
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }
function rssat_table() { global $wpdb; return $wpdb->prefix . 'rssa_telegram'; }
function rssat_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = rssat_table(); $collation = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $table (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        message_id bigint NOT NULL,
        update_id bigint NOT NULL,
        group_key varchar(100) NOT NULL,
        published bigint NOT NULL,
        body longtext NOT NULL,
        attachment_id bigint NOT NULL DEFAULT 0,
        file_id text NOT NULL,
        hidden tinyint NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY message_id (message_id),
        KEY group_key (group_key)
    ) $collation;");
    update_option('rssat_schema', '1');
}
register_activation_hook(__FILE__, 'rssat_install');
function rssat_config() { return get_option('rssat_config', []); }
function rssat_api($method, $args = [], $token = null) {
    $token = $token ?? (rssat_config()['token'] ?? '');
    if (!$token) { return new WP_Error('token', 'Enter a bot token first.'); }
    $response = wp_remote_post('https://api.telegram.org/bot' . $token . '/' . $method, ['timeout'=>20,'redirection'=>0,'body'=>$args]);
    // Never surface HTTP errors containing the credential-bearing API URL.
    if (is_wp_error($response)) { return new WP_Error('network', 'Telegram could not be reached. Check outbound HTTPS and retry.'); }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($data['ok'])) { return new WP_Error('api', 'Telegram rejected the request. Check the token, channel membership, and bot permissions.'); }
    return $data['result'];
}
function rssat_photo($file_id) {
    $file = rssat_api('getFile', ['file_id'=>$file_id]);
    if (is_wp_error($file)) { return $file; }
    $path = $file['file_path'] ?? '';
    if (!preg_match('~^[a-zA-Z0-9_/-]+\.(jpg|jpeg|png|webp)$~i', $path) || str_contains($path, '..') || ($file['file_size'] ?? 0) > 8 * MB_IN_BYTES) {
        return new WP_Error('photo', 'Photo is unsupported or exceeds the 8 MB limit.');
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $tmp = wp_tempnam('rssa-telegram');
    if (!$tmp) { return new WP_Error('temp', 'Temporary storage unavailable.'); }
    $result = wp_remote_get('https://api.telegram.org/file/bot' . rssat_config()['token'] . '/' . $path, ['timeout'=>25,'redirection'=>0,'stream'=>true,'filename'=>$tmp,'limit_response_size'=>8 * MB_IN_BYTES + 1]);
    if (is_wp_error($result) || wp_remote_retrieve_response_code($result) !== 200 || filesize($tmp) > 8 * MB_IN_BYTES || !wp_get_image_mime($tmp)) {
        @unlink($tmp); return new WP_Error('download', 'Photo download failed. Telegram will retry the update.');
    }
    $id = media_handle_sideload(['name'=>'telegram-' . substr(hash('sha256', $file_id),0,20) . '.' . pathinfo($path, PATHINFO_EXTENSION), 'tmp_name'=>$tmp], 0, 'RSSA Telegram photo');
    if (is_wp_error($id)) { @unlink($tmp); return new WP_Error('upload', 'Could not save the Telegram photo. Check uploads permissions.'); }
    return $id;
}
function rssat_authorize($request) {
    $secret = rssat_config()['secret'] ?? '';
    if (!$secret || !hash_equals($secret, (string)$request->get_header('x-telegram-bot-api-secret-token'))) {
        return new WP_Error('forbidden', 'Invalid webhook authentication.', ['status'=>403]);
    }
    return true;
}
function rssat_receive($request) {
    global $wpdb;
    if (strlen($request->get_body()) > 1024 * 1024) { return new WP_Error('size','Update too large.',['status'=>413]); }
    $update = $request->get_json_params();
    $message = $update['channel_post'] ?? $update['edited_channel_post'] ?? null;
    $config = rssat_config();
    if (!$message || ($message['chat']['type'] ?? '') !== 'channel' || (string)($message['chat']['id'] ?? '') !== (string)($config['chat_id'] ?? '')) {
        return new WP_REST_Response(['ok'=>true,'ignored'=>true],200);
    }
    if (!is_int($update['update_id'] ?? null) || !is_int($message['message_id'] ?? null)) { return new WP_Error('invalid','Invalid update.',['status'=>400]); }
    // One short database lock prevents concurrent duplicate inserts and album visibility races.
    $lock = 'rssat_receive_lock';
    if (!add_option($lock,time(),'','no')) {
        if ((int)get_option($lock) < time()-120) { delete_option($lock); }
        return new WP_Error('busy','Retry this update.',['status'=>503]);
    }
    try {
        $table = rssat_table(); $mid = $message['message_id'];
        $old = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE message_id=%d",$mid), ARRAY_A);
        if ($old && (int)$old['update_id'] >= $update['update_id']) { return ['ok'=>true,'duplicate'=>true]; }
        $group = isset($message['media_group_id']) ? 'album-' . sanitize_key($message['media_group_id']) : 'post-' . $mid;
        if (($old && (int)$old['hidden'] === 2) || (int)$wpdb->get_var($wpdb->prepare("SELECT MAX(hidden) FROM $table WHERE group_key=%s",$group)) === 2) { return ['ok'=>true,'deleted'=>true]; }
        $text = $message['text'] ?? $message['caption'] ?? '';
        if (!is_string($text)) { $text = ''; }
        $file_id = ''; $attachment = 0;
        if (!empty($message['photo']) && is_array($message['photo'])) {
            $photo = end($message['photo']); $file_id = (string)($photo['file_id'] ?? '');
            if ($file_id) {
                $attachment = $old && $old['file_id'] === $file_id ? (int)$old['attachment_id'] : rssat_photo($file_id);
                if (is_wp_error($attachment)) { update_option('rssat_error',$attachment->get_error_message(),false); return new WP_Error('photo',$attachment->get_error_message(),['status'=>503]); }
            }
        }
        if (!$text && !$file_id && !$old) { $text = 'View this announcement on Telegram.'; }
        $hidden = $old ? (int)$old['hidden'] : (int)$wpdb->get_var($wpdb->prepare("SELECT MAX(hidden) FROM $table WHERE group_key=%s",$group));
        $row = ['message_id'=>$mid,'update_id'=>$update['update_id'],'group_key'=>$group,'published'=>(int)($message['date'] ?? time()),'body'=>sanitize_textarea_field($text),'attachment_id'=>$attachment,'file_id'=>$file_id,'hidden'=>$hidden];
        $saved = $old ? $wpdb->update($table,$row,['message_id'=>$mid]) : $wpdb->insert($table,$row);
        if ($saved === false) { return new WP_Error('database','Could not save update.',['status'=>503]); }
        update_option('rssat_last_received',time(),false); delete_option('rssat_error');
        $cleanup = rssat_prune();
        if (is_wp_error($cleanup)) { update_option('rssat_error', $cleanup->get_error_message(), false); }
        return ['ok'=>true];
    } finally { delete_option($lock); }
}
add_action('rest_api_init',function(){
    register_rest_route('rssa-telegram/v1','/update',['methods'=>'POST','callback'=>'rssat_receive','permission_callback'=>'rssat_authorize']);
});
function rssat_enabled() { return !empty(rssat_config()['enabled']); }
function rssat_render() {
    global $wpdb; $table=rssat_table();
    $groups=$wpdb->get_col("SELECT group_key FROM $table WHERE hidden=0 GROUP BY group_key ORDER BY MAX(published) DESC LIMIT 6");
    if (!$groups) { echo '<p>Announcements will appear here after the next Telegram post. <a href="https://t.me/RSSA_Lugano">See all announcements on Telegram ↗</a></p>'; return; }
    echo '<div class="rssa-announcements">';
    foreach($groups as $group) {
        $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_key=%s AND hidden=0 ORDER BY message_id",$group));
        $first=$rows[0];
        echo '<article class="rssa-announcement"><header class="rssa-announcement-header"><span class="rssa-announcement-avatar" aria-hidden="true">➤</span><div><a class="rssa-announcement-channel" href="https://t.me/RSSA_Lugano" target="_blank" rel="noopener">RSSA Lugano</a><span class="rssa-announcement-handle">@RSSA_Lugano</span></div></header>';
        foreach($rows as $row) {
            if($row->attachment_id) { echo wp_get_attachment_image($row->attachment_id,'large',false,['loading'=>'lazy','alt'=>'Photo accompanying this Telegram announcement']); }
            if($row->body) { echo '<p>'.nl2br(esc_html($row->body)).'</p>'; }
        }
        echo '<footer class="rssa-announcement-footer"><time datetime="'.esc_attr(gmdate('c',$first->published)).'">'.esc_html(wp_date('j M · H:i',$first->published)).'</time><a class="rssa-announcement-source" href="https://t.me/RSSA_Lugano/'.absint($first->message_id).'" target="_blank" rel="noopener">Open in Telegram ↗</a></footer></article>';
    }
    echo '</div>';
}
add_action('admin_menu',function(){add_menu_page('Telegram announcements','Telegram announcements','edit_rssa_content','rssa-telegram','rssat_screen','dashicons-megaphone',4);});

function rssat_delete_group($key) {
    global $wpdb;
    if (!current_user_can('edit_rssa_content')) { return new WP_Error('forbidden', 'You cannot delete announcements.'); }
    if (!add_option('rssat_receive_lock', time(), '', 'no')) { return new WP_Error('busy', 'An update is being received. Please retry shortly.'); }
    try { return rssat_remove_group($key); }
    finally { delete_option('rssat_receive_lock'); }
}
// Call only while holding rssat_receive_lock; shared by manual and automatic cleanup.
function rssat_remove_group($key) {
    global $wpdb;
        $table = rssat_table();
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_key=%s", $key));
        if (!$rows) { return new WP_Error('missing', 'Announcement not found.'); }
        // Keep only identifiers as tombstones so retries, edits and album parts cannot restore deleted content.
        $saved = $wpdb->update($table, ['hidden'=>2, 'body'=>'', 'file_id'=>''], ['group_key'=>$key]);
        if ($saved === false) { return new WP_Error('database', 'Could not delete the announcement. Please retry.'); }
        $retained = 0;
        foreach (array_unique(array_map('intval', wp_list_pluck($rows, 'attachment_id'))) as $id) {
            if (!$id) { continue; }
            $shared = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE attachment_id=%d AND group_key<>%s", $id, $key));
            $content = get_option('rssa_content', []);
            $gallery = get_option('rssa_gallery', []);
            array_walk_recursive($content, function($value) use ($id, &$shared) { if ((string)$value === (string)$id) { $shared++; } });
            array_walk_recursive($gallery, function($value) use ($id, &$shared) { if ((string)$value === (string)$id) { $shared++; } });
            $shared += (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%s", (string)$id));
            $url = wp_get_attachment_url($id);
            if ($url) { $shared += (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type<>'attachment' AND (post_content LIKE %s OR post_content LIKE %s)", '%'.$wpdb->esc_like($url).'%', '%'.$wpdb->esc_like('wp-image-'.$id).'%')); }
            if ($shared) { $retained++; }
            elseif (get_post($id) && !wp_delete_attachment($id, true)) { return new WP_Error('media', 'Post removed, but a photo could not be deleted. Retry deleting this entry.'); }
        }
        $wpdb->update($table, ['attachment_id'=>0], ['group_key'=>$key]);
        return 'Deleted from WordPress only. Telegram is unchanged.' . ($retained ? ' Photos used elsewhere on the website were kept.' : ' Associated photos were removed from the Media Library.');
}
function rssat_prune() {
    global $wpdb;
    $table = rssat_table();
    $groups = $wpdb->get_col("SELECT group_key FROM $table WHERE hidden<>2 GROUP BY group_key ORDER BY MAX(published) DESC, MAX(message_id) DESC");
    if ($wpdb->last_error) { return new WP_Error('retention', 'Could not check announcement retention.'); }
    // Retry any media deletion that previously failed, even after its text was cleared.
    $pending = $wpdb->get_col("SELECT DISTINCT group_key FROM $table WHERE hidden=2 AND attachment_id<>0");
    foreach (array_unique(array_merge(array_slice($groups, 6), $pending)) as $key) {
        $result = rssat_remove_group($key);
        if (is_wp_error($result)) { return $result; }
    }
    return true;
}

function rssat_screen() {
    global $wpdb;
    if(!current_user_can('edit_rssa_content'))return;
    $config=rssat_config(); $notice='';
    if($_SERVER['REQUEST_METHOD']==='POST') {
        check_admin_referer('rssat_settings');
        if(isset($_POST['delete_announcement'])) {
            $result = rssat_delete_group(sanitize_text_field(wp_unslash($_POST['group_key'] ?? '')));
            $notice = is_wp_error($result) ? $result->get_error_message() : $result;
        } elseif(isset($_POST['visibility'])) {
            $key=sanitize_text_field(wp_unslash($_POST['group_key'] ?? ''));
            $wpdb->update(rssat_table(),['hidden'=>($_POST['visibility']==='hide'?1:0)],['group_key'=>$key,'hidden'=>($_POST['visibility']==='hide'?0:1)]); $notice='Visibility updated.';
        } elseif(current_user_can('manage_options')) {
            $action=sanitize_key($_POST['operation'] ?? 'save');
            if($action==='save') {
                $token=trim(wp_unslash($_POST['token'] ?? ''));
                if($token && !preg_match('/^\d+:[A-Za-z0-9_-]+$/',$token)){$notice='Token format is invalid.';}
                else {
                    if($token)$config['token']=$token;
                    $config['enabled']=isset($_POST['enabled']);
                    $config['secret']=$config['secret']??wp_generate_password(48,false,false);
                    update_option('rssat_config',$config,false); $notice='Settings saved. Saving does not connect Telegram.';
                }
            } elseif($action==='connect') {
                $url=rest_url('rssa-telegram/v1/update');
                if(untrailingslashit(home_url())!=='https://rssa.usi.ch') {$notice='Connect after deployment to https://rssa.usi.ch. Telegram cannot reach localhost.';}
                else {
                    $me=rssat_api('getMe'); $chat=rssat_api('getChat',['chat_id'=>'@RSSA_Lugano']); $webhook=rssat_api('getWebhookInfo');
                    if(is_wp_error($me)||is_wp_error($chat)||is_wp_error($webhook)) {$notice='Connection check failed. Check the token, channel membership, and outbound HTTPS.';}
                    elseif(!empty($webhook['url'])&&$webhook['url']!==$url) {$notice='This bot already has a different webhook. Use a dedicated RSSA website bot.';}
                    else {
                        $member=rssat_api('getChatMember',['chat_id'=>$chat['id'],'user_id'=>$me['id']]);
                        if(is_wp_error($member)||!in_array($member['status']??'',['member','administrator','creator'],true)) {$notice='Add the bot to the RSSA channel first.';}
                        else {
                            $config['chat_id']=(string)$chat['id']; $config['secret']=$config['secret']??wp_generate_password(48,false,false); update_option('rssat_config',$config,false);
                            $result=rssat_api('setWebhook',['url'=>$url,'secret_token'=>$config['secret'],'allowed_updates'=>wp_json_encode(['channel_post','edited_channel_post']),'max_connections'=>1]);
                            $notice=is_wp_error($result)?$result->get_error_message():'Webhook connected. Publish a new channel post to verify delivery.';
                        }
                    }
                }
            } elseif($action==='status') {
                $status=rssat_api('getWebhookInfo');
                $notice=is_wp_error($status)?$status->get_error_message():'Webhook '.(empty($status['url'])?'not connected':($status['url']===rest_url('rssa-telegram/v1/update')?'matches this site':'points elsewhere')).'. Pending updates: '.absint($status['pending_update_count']??0).'. Recent delivery error: '.(!empty($status['last_error_date'])?wp_date('Y-m-d H:i',(int)$status['last_error_date']):'none reported').'.';
            }
        }
    }
    echo '<div class="wrap"><h1>Telegram announcements</h1><p>Telegram is the source of truth. Edit announcements in Telegram; hide or show them here.</p>';
    if($notice) echo '<div class="notice notice-info"><p>'.esc_html($notice).'</p></div>';
    echo '<p>Last received: '.esc_html(get_option('rssat_last_received')?wp_date('Y-m-d H:i',get_option('rssat_last_received')):'No updates yet').'</p><p>'.esc_html(get_option('rssat_error','')).'</p>';
    if(current_user_can('manage_options')) {
        echo '<form method="post">'; wp_nonce_field('rssat_settings');
        echo '<p><label>Bot token <input type="password" name="token" autocomplete="new-password" value="" placeholder="'.(!empty($config['token'])?'Saved — leave blank to keep':'Paste token here').'" size="50"></label></p><p>The token is stored privately in the WordPress database and is never displayed back. Protect database backups.</p><p><label><input type="checkbox" name="enabled" '.checked(!empty($config['enabled']),true,false).'> Use the Telegram integration on the website instead of Juicer</label></p><button class="button button-primary" name="operation" value="save">Save settings</button> <button class="button" name="operation" value="connect">Connect webhook</button> <button class="button" name="operation" value="status">Check Telegram status</button></form>';
    }
    $rows=$wpdb->get_results('SELECT group_key, MAX(published) AS published, MAX(hidden) AS hidden, MAX(body) AS body FROM '.rssat_table().' WHERE hidden<>2 OR attachment_id<>0 GROUP BY group_key ORDER BY MAX(published) DESC');
    echo '<h2>Received announcements</h2><p>WordPress keeps the six newest announcements, including hidden ones; each album counts as one. On the next received update, older text and unused photos are automatically removed from WordPress. Tiny ID records prevent old edits from restoring them. The website shows the visible announcements among those six. Hide keeps the copy only until it ages out. Delete removes its text and downloaded photos from WordPress only; Telegram is unchanged. Photos used in website content or the gallery are kept. Albums are deleted together.</p>';
    if(!$rows)echo '<p>No announcements received yet. Older channel history is not imported automatically.</p>';
    foreach($rows as $row){echo '<form method="post" style="padding:16px;background:white;margin:12px 0">';wp_nonce_field('rssat_settings');echo '<input type="hidden" name="group_key" value="'.esc_attr($row->group_key).'"><p>'.esc_html(wp_date('j F Y',$row->published).' — '.wp_trim_words($row->body,30)).'</p><button class="button" name="visibility" value="'.($row->hidden?'show':'hide').'">'.($row->hidden?'Show on website':'Hide from website').'</button> <button class="button" name="delete_announcement" value="1" onclick="return confirm(&quot;Permanently delete this announcement and its downloaded photos from WordPress? Telegram will remain unchanged. This cannot be undone.&quot;)">Delete from WordPress</button></form>';}
    echo '</div>';
}
