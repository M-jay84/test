<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UploadLogic;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (($current_user->can_upload ?? '') !== 'yes') {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!empty(CONFIG['UPLOADERSONLY']) && !Auth::isStaff()) {
    redirect('home', __('UPLOAD_ONLY_FOR_UPLOADERS'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('upload/torrent', __('ACTION_FAILED'), 'warning');
}

UploadLogic::torrent($current_user);