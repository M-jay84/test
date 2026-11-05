<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Models\TorrentsLogic;
use Bootstrap\Models\TorrentLangLogic;
use PDO;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}
if (($current_user->view_torrents ?? '') !== 'yes' && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('home', __('NO_TORRENT_VIEW'), 'warning');
}

$browse       = TorrentsLogic::countBrowse();
$TORRENTS     = $browse['res'] ?? [];
$PAGER        = $browse['pagerbuttons'] ?? '';
$URL          = $browse['search']['url'] ?? '';
$PARENT_CAT   = $browse['search']['parent_cat'] ?? '';
$WHERE_CAT_IN = $browse['search']['wherecatina'] ?? [];
$CATS         = $browse['cats'] ?? [];
$KEYWORD      = $browse['search']['keyword'] ?? '';
$V_url        = url('torrents/browse') . '?' . ltrim((string) $URL, '&');

theme_header(__('BROWSE_TORRENTS'));
render_flash();
block_begin(__('BROWSE_TORRENTS'));
?>
<div class="tt_blockcenter rounded">
    <form method="get" action="<?= url('torrents/browse') ?>">
        <p class="text-center">
            <input type="text" name="keyword" placeholder="<?= e(__('SEARCH')) ?>" size="40"
                   value="<?= e(stripslashes((string) $KEYWORD)) ?>"><br>
            <?php
            $i = 0;
            $catsperrow = 5;
            $getCat = (int) get('cat', 0);
            foreach ($CATS as $cat) {
                if ($i && $i % $catsperrow === 0) {
                    echo '<br><br>';
                }
                $checked = in_array($cat['id'], $WHERE_CAT_IN, false) || $getCat === (int) $cat['id']
                    ? ' checked' : '';
                echo '<a href="' . url('torrents/browse?cat=' . (int) $cat['id']) . '">'
                   . e($cat['parent_cat'] . ' - ' . $cat['name']) . '</a> '
                   . '<input name="c' . (int) $cat['id'] . '" type="checkbox"' . $checked . ' value="1"> ';
                $i++;
            }
            ?>
            <br><br>
            <a href="<?= url('torrents/browse') ?>" class="ttbtn"><?= e(__('RESET')) ?></a>
            <button type="submit" class="ttbtn"><?= e(__('GO')) ?></button>
        </p>

        <?php if ($PARENT_CAT) :
            $subcats = DB::run(
                'SELECT id, name, parent_cat FROM categories WHERE parent_cat = ? ORDER BY name',
                [$PARENT_CAT]
            )->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <br><b><?= e(__('YOU_ARE_IN')) ?>:</b>
            <a href="<?= url('torrents/browse?parent_cat=' . rawurlencode($PARENT_CAT)) ?>"><?= e($PARENT_CAT) ?></a><br>
            <b><?= e(__('SUB_CATS')) ?>:</b>
            <?php foreach ($subcats as $sub) : ?>
                - <a href="<?= url('torrents/browse?cat=' . (int) $sub['id']) ?>"><?= e($sub['name']) ?></a>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="tt_blockcenter rounded p-2">
            <div class="text-center">
                <a data-bs-toggle="collapse" data-bs-target="#collapseExample">More Search</a>
            </div>
            <div class="collapse" id="collapseExample">
                <div class="input-group mb-3">
                    <select name="cat" class="form-control">
                        <option value="0">(<?= e(__('ALL')) ?> <?= e(__('TYPES')) ?>)</option>
                        <?php foreach ($CATS as $cat) : ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= $getCat === (int) $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['parent_cat'] . ': ' . $cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-3">
                    <select name="incldead" class="form-control">
                        <option value="0"><?= e(__('ACTIVE_TRANSFERS')) ?></option>
                        <option value="1" <?= (string) get('incldead') === '1' ? 'selected' : '' ?>><?= e(__('INC_DEAD')) ?></option>
                        <option value="2" <?= (string) get('incldead') === '2' ? 'selected' : '' ?>><?= e(__('ONLY_DEAD')) ?></option>
                    </select>
                    <select name="freeleech" class="form-control">
                        <option value="0"><?= e(__('ALL')) ?></option>
                        <option value="1" <?= (string) get('freeleech') === '1' ? 'selected' : '' ?>><?= e(__('NOT_FREELEECH')) ?></option>
                        <option value="2" <?= (string) get('freeleech') === '2' ? 'selected' : '' ?>><?= e(__('ONLY_FREELEECH')) ?></option>
                    </select>
                    <?php if (!empty(CONFIG['ALLOWEXTERNAL'])) : ?>
                        <select name="inclexternal" class="form-control">
                            <option value="0"><?= e(__('LOCAL_EXTERNAL')) ?></option>
                            <option value="1" <?= (string) get('inclexternal') === '1' ? 'selected' : '' ?>><?= e(__('LOCAL_ONLY')) ?></option>
                            <option value="2" <?= (string) get('inclexternal') === '2' ? 'selected' : '' ?>><?= e(__('EXTERNAL_ONLY')) ?></option>
                        </select>
                    <?php endif; ?>
                    <?= TorrentLangLogic::langList((string) get('language', '')) ?>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SEARCH')) ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<form id="sort" action="">
    <div class="text-end"><?= e(__('SORT_BY')) ?>:
        <select name="sort" onchange="window.location='<?= e($V_url) ?>sort='+this.value+'&order='+document.forms['sort'].order.value">
            <?php
            $sortOpts = ['id' => 'ADDED', 'name' => 'NAME', 'comments' => 'COMMENTS', 'size' => 'SIZE',
                'times_completed' => 'COMPLETED', 'seeders' => 'SEEDERS', 'leechers' => 'LEECHERS'];
            foreach ($sortOpts as $val => $langKey) :
                ?>
                <option value="<?= $val ?>" <?= (string) get('sort') === $val ? 'selected' : '' ?>><?= e(__($langKey)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="order" onchange="window.location='<?= e($V_url) ?>order='+this.value+'&sort='+document.forms['sort'].sort.value">
            <option value="asc" <?= (string) get('order', 'asc') === 'asc' ? 'selected' : '' ?>><?= e(__('ASCEND')) ?></option>
            <option value="desc" <?= (string) get('order') === 'desc' ? 'selected' : '' ?>><?= e(__('DESCEND')) ?></option>
        </select>
    </div>
</form>

<?php
torrenttable($TORRENTS);
echo $PAGER;
block_end();
theme_footer();