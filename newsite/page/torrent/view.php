<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TorrentsLogic;

$current_user = Auth::user();
$id     = (int) param(0, 0);
$action = (string) param(1, ''); // e.g. bump, hit

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}
if ($id < 1) {
    redirect('browse', __('MISSING_ID'), 'warning');
}

$pack    = TorrentsLogic::fetchIdDetails($id, $current_user, $action);
$torrent = $pack['torrent'] ?? $pack; // model shape may nest or flatten
// Prefer nested keys from your model:
$tmdb1   = $pack['tmdb1'] ?? [];
$image   = $pack['image'] ?? '';
$video   = $pack['video'] ?? '';
$likes   = $pack['likes'] ?? [];
$thanks  = $pack['thanks'] ?? ($pack['V_thanks'] ?? '');
$tags    = $pack['tags'] ?? '';
$html    = $pack['html'] ?? [];
$scraper = $pack['scraper'] ?? '';

// If model returns torrent row only under ['torrent']:
if (isset($pack['torrent']) && is_array($pack['torrent'])) {
    $torrent = $pack['torrent'];
}

$page_title = (__('TORRENT_DETAILS_FOR') !== 'TORRENT_DETAILS_FOR'
    ? __('TORRENT_DETAILS_FOR') . ' '
    : '') . ($torrent['name'] ?? '');

theme_header(strip_tags($page_title));
render_flash();
block_begin($page_title);
?>
<p>
    <a href="<?= url('torrents/download/' . (int) $torrent['id']) ?>">
        <button type="button" class="btn btn-sm ttbtn"><i class="fa fa-download" style="color:green"></i> <?= e(__('DOWNLOAD')) ?></button>
    </a>
    <?php if (($torrent['external'] ?? '') === 'yes') : ?>
        <button type="button" class="btn btn-sm ttbtn"><?php magnetlink($torrent); ?> <?= e(__('MAGNET')) ?></button>
    <?php endif; ?>
    <span style="float:right;">
        <?php if (Auth::check()) : ?>
            <button type="button" class="btn btn-sm ttbtn" data-bs-toggle="modal" data-bs-target="#reportModal"><?= e(__('REPORT')) ?></button>
        <?php endif; ?>
        <?php if ((int) ($torrent['seeders'] ?? 0) <= 1) : ?>
            <a href="<?= url('torrents/reseed/' . (int) $torrent['id']) ?>" class="btn btn-sm ttbtn"><?= e(__('RESEED')) ?></a>
        <?php endif; ?>
        <?php if (($current_user->edit_torrents ?? '') === 'yes') : ?>
            <a href="<?= url('torrent/view/' . (int) $torrent['id'] . '/bump') ?>" class="btn btn-sm ttbtn"><?= e(__('BUMP')) ?></a>
        <?php endif; ?>
    </span>
</p>

<!-- keep your 4-column layout, swapping LANG → __(), URLROOT → url(), htmlspecialchars → e() -->
<!-- … poster / tmdb / dl rows from your template … -->

<?php
block_end();
// Keep image/youtube/files/nfo/report/delete/edit modals from your paste;
// add csrf_field() inside every POST form and fix:
//   owner check: $current_user->id == $torrent['owner']  (not $torrent['id'])
//   delete permission: ($current_user->delete_torrents ?? '') === 'yes'
theme_footer();