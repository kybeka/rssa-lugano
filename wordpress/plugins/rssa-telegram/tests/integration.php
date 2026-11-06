<?php
// Run only against the disposable rehearsal installation, never the working preview.
if (PHP_SAPI !== 'cli' || !isset($argv[1]) || !str_starts_with(realpath($argv[1]) ?: '', '/private/tmp/rssa-release-rehearsal/')) { exit("Pass the disposable rehearsal WordPress directory.\n"); }
$_SERVER['HTTP_HOST']='rehearsal.invalid';
require rtrim($argv[1],'/').'/wp-load.php';
require dirname(__DIR__).'/rssa-telegram.php';
rssat_install();
global $wpdb; $wpdb->query('DELETE FROM '.rssat_table());
update_option('rssat_config',['secret'=>'test-secret','chat_id'=>'-100123','token'=>'test-token','enabled'=>true],false);
function verify($condition,$message){if(!$condition)throw new Exception($message);}
function deliver($mid,$uid,$changes=[]){
 $request=new WP_REST_Request('POST','/rssa-telegram/v1/update');$request->set_header('Content-Type','application/json');
 $request->set_body(wp_json_encode(['update_id'=>$uid,'channel_post'=>array_replace(['message_id'=>$mid,'chat'=>['id'=>-100123,'type'=>'channel'],'date'=>1700000000,'text'=>'Announcement'],$changes)]));
 return rssat_receive($request);
}
$request=new WP_REST_Request('POST');verify(is_wp_error(rssat_authorize($request)),'Missing secret accepted');
$request->set_header('x-telegram-bot-api-secret-token','wrong');verify(is_wp_error(rssat_authorize($request)),'Wrong secret accepted');
$request->set_header('x-telegram-bot-api-secret-token','test-secret');verify(rssat_authorize($request)===true,'Correct secret rejected');
verify(deliver(1,1,['chat'=>['id'=>-999,'type'=>'channel']]) instanceof WP_REST_Response,'Wrong channel not ignored');
verify(deliver(1,2)['ok'],'Initial post failed');verify(deliver(1,2)['duplicate'],'Duplicate accepted');
verify(deliver(1,3,['text'=>'Changed <script>alert(1)</script>'])['ok'],'Edit failed');verify(deliver(1,2)['duplicate'],'Older update overwrote edit');
verify((int)$wpdb->get_var('SELECT COUNT(*) FROM '.rssat_table())===1,'Duplicate rows');
deliver(2,4,['media_group_id'=>'123','text'=>'Album caption']);deliver(3,5,['media_group_id'=>'123','text'=>'']);
verify((int)$wpdb->get_var("SELECT COUNT(*) FROM ".rssat_table()." WHERE group_key='album-123'")===2,'Album grouping failed');
$wpdb->update(rssat_table(),['hidden'=>1],['group_key'=>'album-123']);deliver(4,6,['media_group_id'=>'123','text'=>'Later album member']);
verify((int)$wpdb->get_var('SELECT hidden FROM '.rssat_table().' WHERE message_id=4')===1,'Hidden album became visible');
add_option('rssat_receive_lock',time(),'','no');verify(is_wp_error(deliver(9,9)),'Concurrent update not retried');delete_option('rssat_receive_lock');
add_filter('pre_http_request',function($pre,$args,$url){
 if(str_contains($url,'/getFile'))return ['response'=>['code'=>200],'body'=>wp_json_encode(['ok'=>true,'result'=>['file_path'=>'photos/example.jpg','file_size'=>10000]])];
 if(str_contains($url,'/file/bot')){copy('/Users/kylakaplan/Desktop/rssa+/rssa/wordpress/theme/images/katherine.jpg',$args['filename']);return ['response'=>['code'=>200],'body'=>''];}
 return new WP_Error('blocked','Network disabled for test');
},10,3);
verify(deliver(5,7,['photo'=>[['file_id'=>'example-photo']],'text'=>'Photo caption'])['ok'],'Photo import failed');
$id=$wpdb->get_var('SELECT attachment_id FROM '.rssat_table().' WHERE message_id=5');verify($id&&is_file(get_attached_file($id)),'Original image not stored');
ob_start();rssat_render();$html=ob_get_clean();verify(!str_contains($html,'<script>'),'Unsafe HTML rendered');verify(!str_contains($html,'test-token'),'Token exposed');verify(!str_contains($html,'Album caption'),'Hidden album rendered');
verify(str_contains($html,'https://t.me/RSSA_Lugano/5'),'Source link missing');
echo "Passed: authentication, channel allowlist, new posts, edits, duplicate/out-of-order delivery, albums, hide persistence, concurrent retry, mocked photo download/storage, output escaping, and source links.\n";
