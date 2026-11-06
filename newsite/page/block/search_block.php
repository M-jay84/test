<?php
declare(strict_types=1);

/**
 * Sidebar: quick torrent search (keyword + category + dead/external filters).
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CatagoriesLogic;

if (!Auth::check()) {
    return;
}

block_begin(__('SEARCH'));
$search = (string) get('search', '');
?>
<form method="get" action="<?= url('torrents/browse') ?>">
    <input type="text" name="keyword" style="width:95%" class="form-control"
           value="<?= e($search) ?>" placeholder="<?= e(__('SEARCH')) ?>"><br>
    <select name="cat" style="width:95%" class="form-select">
        <option value="0">(<?= e(__('ALL')) ?>)</option>
        <?= CatagoriesLogic::dropdown((string) get('cat', '')) ?>
    </select><br>
    <select name="incldead" style="width:95%" class="form-select">
        <option value="0"><?= e(__('ACTIVE')) ?></option>
        <option value="1"><?= e(__('INCLUDE_DEAD')) ?></option>
        <option value="2"><?= e(__('ONLY_DEAD')) ?></option>
    </select><br>
    <?php if (!empty(CONFIG['ALLOWEXTERNAL'])) : ?>
        <select name="inclexternal" style="width:95%" class="form-select">
            <option value="0"><?= e(__('LOCAL')) ?>/<?= e(__('EXTERNAL')) ?></option>
            <option value="1"><?= e(__('LOCAL_ONLY')) ?></option>
            <option value="2"><?= e(__('EXTERNAL_ONLY')) ?></option>
        </select><br>
    <?php endif; ?>
    <div class="text-center">
        <button type="submit" class="btn ttbtn"><?= e(__('SEARCH')) ?></button>
    </div>
</form>
<?php
block_end();