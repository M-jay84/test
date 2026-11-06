<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RssLogic;

$current_user = Auth::user();

if (($current_user->view_torrents ?? '') !== 'yes' && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('home', __('NO_TORRENT_VIEW'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('rss/custom', __('NOTHING_SELECTED'), 'warning');
}

RssLogic::rsslink($current_user);