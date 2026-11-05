<?php
declare(strict_types=1);

/**
 * URL: /tag/index/{name}  or leftover segments as name
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TagsLogic;

$current_user = Auth::user();
$name = (string) param(0, '');

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if (($current_user->view_torrents ?? '') !== 'yes' && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('home', __('NO_TORRENT_VIEW'), 'warning');
}

$tags = TagsLogic::search($name);
$TAGS = $tags['stmt'] ?? [];

theme_header(__('SEARCH'));
render_flash();
block_begin(__('SEARCH'));
?>
<div class="text-center">
    <a href="<?= url('browse') ?>"><?= e(__('BROWSE_TORRENTS')) ?></a>
</div>
<?php
torrenttable($TAGS);
block_end();
theme_footer();