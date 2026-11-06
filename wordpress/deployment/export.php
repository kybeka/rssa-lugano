<?php
if (PHP_SAPI !== 'cli') { exit; }
$_SERVER['HTTP_HOST'] = '127.0.0.1:9400';
require __DIR__ . '/../runtime/wp-load.php';
if (!function_exists('rssa_fields')) { require __DIR__ . '/../theme/functions.php'; }
$destination = $argv[1] ?? '';
if (!$destination || !is_dir($destination)) { throw new Exception('Provide an existing output directory.'); }
$content = get_option('rssa_content', []); $gallery = rssa_gallery(); $ids = [];
foreach (rssa_fields() as $key=>$field) { if ($field['type']==='image' && !empty($content[$key])) { $ids[] = absint($content[$key]); } }
foreach ($gallery as $photo) { $ids[] = $photo['id']; }
$media = []; mkdir($destination . '/media');
foreach (array_unique($ids) as $id) {
    $path = get_attached_file($id);
    if (!is_file($path) || !filesize($path)) { throw new Exception('Missing original photo: ' . $id); }
    $file = $id . '-' . basename($path);
    if (!copy($path, $destination . '/media/' . $file)) { throw new Exception('Photo copy failed.'); }
    $media[] = ['id'=>$id,'file'=>$file,'sha256'=>hash_file('sha256',$path),'title'=>get_the_title($id),'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true)];
}
file_put_contents($destination . '/content.json', wp_json_encode(['format'=>'rssa-transfer-1','content'=>$content,'gallery'=>$gallery,'media'=>$media], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo count($media) . " original photos exported. No accounts or settings exported.\n";
