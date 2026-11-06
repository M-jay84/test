<?php
declare(strict_types=1);

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

form_start(url('rss/submit'), 'post');
echo csrf_field();

echo e(__('CATS_LEAVE_BLANK_ALL')) . '<br>';
foreach ($CATAGORYS as $row) {
    $label = ($row['parent_cat'] ?? '') . ' - ' . ($row['name'] ?? '');
    echo '<label><input type="checkbox" name="cats[]" value="' . (int) ($row['id'] ?? 0) . '"> '
       . e($label) . '</label><br>';
}

echo '<br>';
form_input('user', __('USER_ID'), '', 'number', '0', false, 'min="0"');
form_checkbox('incldead', __('INCLUDE_DEAD'), '1', false);

echo '<div class="text-center">';
button(__('GET_LINK'));
echo '</div>';
form_end();
?>
<br>
<p class="text-center">
    <ul>
        <li><?= e(__('RSS_VALIDATED_NOTE')) ?></li>
        <li><?= e(__('RSS_LATEST_50_NOTE')) ?></li>
    </ul>
</p>
<?php
block_end();
theme_footer();