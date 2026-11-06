<?php
require __DIR__.'/deletion.php';
$wpdb->query('DELETE FROM '.rssat_table());
deliver(101,201,['photo'=>[['file_id'=>'retention-photo']]]);
$oldPhoto=(int)$wpdb->get_var('SELECT attachment_id FROM '.rssat_table().' WHERE message_id=101');
$oldPath=get_attached_file($oldPhoto);
deliver(102,202,['photo'=>[['file_id'=>'gallery-photo']]]);
$galleryPhoto=(int)$wpdb->get_var('SELECT attachment_id FROM '.rssat_table().' WHERE message_id=102');
update_option('rssa_gallery',[['id'=>$galleryPhoto]]);
deliver(103,203,['media_group_id'=>'retention-album']);
deliver(104,204,['media_group_id'=>'retention-album']);
for($n=200;$n<=206;$n++){ verify(deliver($n,$n+100)['ok'],'Retention delivery failed'); }
verify((int)$wpdb->get_var('SELECT COUNT(DISTINCT group_key) FROM '.rssat_table().' WHERE hidden<>2')===6,'Retention did not keep six groups');
verify(!get_post($oldPhoto)&&!file_exists($oldPath),'Expired photo remains');
verify(get_post($galleryPhoto)&&file_exists(get_attached_file($galleryPhoto)),'Shared gallery photo removed');
verify((int)$wpdb->get_var('SELECT COUNT(*) FROM '.rssat_table()." WHERE hidden=2 AND (body<>'' OR file_id<>'' OR attachment_id<>0)")===0,'Expired content remains');
verify(deliver(101,500)['deleted'],'Old edit resurrected expired post');
verify(deliver(105,501,['media_group_id'=>'retention-album'])['deleted'],'Late album part resurrected expired album');
$wpdb->update(rssat_table(),['hidden'=>1],['message_id'=>206]);
verify(deliver(207,502)['ok'],'Hidden retention failed');
verify((int)$wpdb->get_var('SELECT COUNT(DISTINCT group_key) FROM '.rssat_table().' WHERE hidden<>2')===6,'Hidden posts escaped retention');
echo "Passed: six-group retention, album expiry, media cleanup, shared gallery preservation, hidden-post retention and stale-update suppression.\n";
