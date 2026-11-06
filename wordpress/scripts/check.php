<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1:9400';
require __DIR__ . '/../runtime/wp-load.php';
if (!function_exists('rssa_fields')) { require __DIR__ . '/../theme/functions.php'; }
$role = get_role('rssa_editor');
if (!$role || !$role->has_cap('edit_rssa_content') || $role->has_cap('manage_options') || $role->has_cap('edit_themes')) { throw new Exception('Incorrect editor permissions'); }
$original = get_option('rssa_content', []);
try {
    update_option('rssa_content', array_merge($original, ['text_5'=>'Local persistence check <script>alert(1)</script><strong>safe</strong>']));
    $rendered = rssa_text('text_5');
    if (str_contains($rendered, '<script>') || !str_contains($rendered, '<strong>safe</strong>')) { throw new Exception('Text escaping failed'); }
    echo "Editor capability checks and output filtering passed.\n";
} finally { update_option('rssa_content', $original); }
