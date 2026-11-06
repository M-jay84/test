<?php
declare(strict_types=1);

/**
 * URL: /attachment/download/{id}/{hash}
 * Streams a file — no theme.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\AttachmentsLogic;

$id   = (int) param(0, 0);
$hash = (string) param(1, '');

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

// Model should validate row + hash, build path, redirect on fail
$attachment = AttachmentsLogic::downloadAttachment($id, $hash);

$file     = (string) ($attachment['file'] ?? '');
$filename = (string) ($attachment['filename'] ?? 'download');
$ext      = (string) ($attachment['extension'] ?? 'octet-stream');

if ($file === '' || !is_file($file)) {
    redirect('home', __('NO_FILE', $hash), 'warning');
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

$safeName = str_replace(['"', "\r", "\n"], '', $filename);
$safeExt  = preg_replace('/[^a-z0-9.+-]/i', '', $ext) ?: 'octet-stream';

header('Content-Disposition: attachment; filename="' . $safeName . '"');
header('Content-Length: ' . (string) filesize($file));
header('Content-Type: application/' . $safeExt);
readfile($file);
exit;