<?php
declare(strict_types=1);

/**
 * Build a custom RSS link (categories / dead / user filter).
 * URL: /rss/custom
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CatagoriesLogic;

$current_user = Auth::user();

if (($current_user->view_torrents ?? '') !== 'yes' && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('home', __('NO_TORRENT_VIEW'), 'warning');
}

$CATAGORYS = CatagoriesLogic::genreList();

theme_header(__('MAKE_RSS_FEED'));
render_flash();
block_begin(__('MAKE_RSS_FEED'));
?>
<form action="<?= url('rss/submit') ?>" method="post">
    <?= csrf_field() ?>

    <?= e(__('CATS_LEAVE_BLANK_ALL')) ?><br>
    <?php foreach ($CATAGORYS as $row) : ?>
        <label>
            <input type="checkbox" name="cats[]" value="<?= (int) ($row['id'] ?? 0) ?>">
            <?= e(($row['parent_cat'] ?? '') . ' - ' . ($row['name'] ?? '')) ?>
        </label><br>
    <?php endforeach; ?>
    <br>

    <input type="number" name="user" min="0" placeholder="0">
    <?= e(__('USER_id')) ?><br><br>

    <label>
        <input type="checkbox" name="incldead" value="1">
        <?= e(__('INCLUDE_DEAD')) ?>
    </label><br><br>

    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('GET_LINK') !== 'GET_LINK' ? __('GET_LINK') : 'Get Link') ?></button>
</form>

<br>
<p class="text-center">
    <ul>
        <li>Our RSS feeds are properly validated by true RSS 2.0 XML Parsing Standards. Visit FeedValidator.org to validate.</li>
        <li>Our feeds display only the latest 50 uploaded Torrents as default.</li>
    </ul>
</p>
<?php
block_end();
theme_footer();