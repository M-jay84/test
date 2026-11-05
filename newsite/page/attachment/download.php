<?php
declare(strict_types=1);

/**
 * URL: /attachment/download/{id}/{hash}
 * Streams a file — no theme.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\AttachmentsLogic;

$current_user = Auth::user();
$id   = (int) param(0, 0);
$hash = (string) param(1, '');

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1 || $hash === '') {
    redirect('home', __('NOTHING_FOUND'), 'warning');
}

$attachment = AttachmentsLogic::downloadAttachment($id, $hash);
$file     = $attachment['file'] ?? '';
$filename = $attachment['filename'] ?? 'download';
$ext      = $attachment['extension'] ?? 'octet-stream';

if ($file === '' || !is_file($file)) {
    redirect('home', __('NO_FILE', $hash), 'warning');
}

// Clear buffers so the file is not corrupted
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
header('Content-Length: ' . (string) filesize($file));
header('Content-Type: application/' . preg_replace('/[^a-z0-9.+-]/i', '', $ext));
readfile($file);
exit;