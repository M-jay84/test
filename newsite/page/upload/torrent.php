<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CatagoriesLogic;
use Bootstrap\Models\TagsLogic;
use Bootstrap\Models\TorrentLangLogic;

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

$announce_urls = array_filter(array_map('trim', explode(',', (string) (CONFIG['ANNOUNCELIST'] ?? ''))));
if ($announce_urls === []) {
    $announce_urls = [rtrim(URLROOT, '/') . '/announce.php'];
}

$categories = CatagoriesLogic::genreList();
$tags       = TagsLogic::getAll();
$languages  = TorrentLangLogic::langList(); // HTML from model

theme_header(__('UPLOAD'));
render_flash();
?>
<p class="tt_blockcenter rounded text-center p-3">
    <b><?= e(__('RULE')) ?></b><br>
    <b><?= stripslashes((string) (CONFIG['UPLOADRULES'] ?? '')) ?></b>
</p>
<br>

<div class="tt_blockcenter rounded p-2">
    <form name="upload" enctype="multipart/form-data" action="<?= e(url('upload/submit')) ?>" method="post">
        <?= csrf_field() ?>

        <div class="text-center">
            <?= e(__('ANNOUNCE_URL')) ?><br>
            <?php foreach ($announce_urls as $value) : ?>
                <b><?= e($value) ?></b><br>
            <?php endforeach; ?>
            <?php if (!empty(CONFIG['ALLOWEXTERNAL'])) : ?>
                <p><b><?= e(__('THIS_SITE_ACCEPTS_EXTERNAL')) ?></b></p>
            <?php endif; ?>
        </div>

        <dl class="row p-4">
            <dt class="col-sm-2"><?= e(__('TORRENT')) ?></dt>
            <dd class="col-sm-10"><input class="form-control fullwidth" type="file" name="torrent" required></dd>
        </dl>
        <dl class="row p-4">
            <dt class="col-sm-2"><?= e(__('NFO')) ?></dt>
            <dd class="col-sm-10"><input class="form-control fullwidth" type="file" name="nfo"></dd>
        </dl>
        <dl class="row p-4">
            <dt class="col-sm-2"><?= e(__('NAME')) ?></dt>
            <dd class="col-sm-10">
                <input class="form-control fullwidth" type="text" name="name" placeholder="<?= e(__('TORRENT_NAME')) ?>">
                <?= e(__('THIS_WILL_BE_TAKEN_TORRENT')) ?>
            </dd>
        </dl>

        <?php if (!empty(CONFIG['_TMDBswitch'])) : ?>
            <dl class="row p-4">
                <dt class="col-sm-2"><?= e(__('TMDB')) ?></dt>
                <dd class="col-sm-10"><input class="form-control fullwidth" type="text" name="tmdb" placeholder="<?= e(__('TMDB')) ?>"></dd>
            </dl>
        <?php endif; ?>

        <?php if (!empty(CONFIG['_OMDBswitch'])) : ?>
            <dl class="row p-4">
                <dt class="col-sm-2"><?= e(__('IMDB')) ?></dt>
                <dd class="col-sm-10"><input class="form-control fullwidth" type="text" name="imdb" placeholder="<?= e(__('IMDB')) ?>"></dd>
            </dl>
        <?php endif; ?>

        <?php if (!empty(CONFIG['YOU_TUBE'])) : ?>
            <dl class="row p-2">
                <dt class="col-sm-2"><?= e(__('YOUTUBE')) ?></dt>
                <dd class="col-sm-10"><input class="form-control fullwidth" type="text" name="tube" placeholder="<?= e(__('YOUTUBE')) ?>"></dd>
            </dl>
        <?php endif; ?>

        <dl class="row p-2">
            <dt class="col-sm-2"><?= e(__('IMAGE')) ?> 1</dt>
            <dd class="col-sm-10"><input class="form-control fullwidth" type="file" name="image0"></dd>
        </dl>
        <dl class="row p-2">
            <dt class="col-sm-2"><?= e(__('IMAGE')) ?> 2</dt>
            <dd class="col-sm-10"><input class="form-control fullwidth" type="file" name="image1"></dd>
        </dl>

        <div class="text-center">
            <?= e(__('MAX_FILE_SIZE')) ?>: <?= e(mksize((int) (CONFIG['IMAGEMAXFILESIZE'] ?? 0))) ?><br>
            <?= e(__('ACCEPTED_FORMATS')) ?>:
            <?= e(implode(', ', array_unique((array) (CONFIG['ALLOWEDIMAGETYPES'] ?? [])))) ?>
        </div>

        <div class="form p-4">
            <select name="type" class="form-select fullwidth">
                <option value="0"><?= e(__('CATEGORY')) ?></option>
                <?php foreach ($categories as $row) : ?>
                    <option value="<?= (int) $row['id'] ?>">
                        <?= e(($row['parent_cat'] ?? '') . ': ' . ($row['name'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form p-4">
            <select class="form-select" id="validationTags" name="tags[]" multiple>
                <option selected disabled hidden value=""><?= e(__('CHOSE_TAG')) ?></option>
                <?php foreach ($tags as $row) : ?>
                    <option value="<?= e($row['name'] ?? '') ?>"><?= e($row['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form p-4"><?= $languages ?></div>

        <?php if ((int) ($current_user->class ?? 0) > (int) (CONFIG['_VIP'] ?? 0)) : ?>
            <div class="form p-4">
                <input class="form-check-input" type="checkbox" name="free" value="1">
                <?= e(__('CHECK_FREELEECH')) ?>
            </div>
            <div class="form p-4">
                <input class="form-check-input" type="checkbox" name="vip" value="yes">
                <?= e(__('VIP')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty(CONFIG['ANONYMOUSUPLOAD'])) : ?>
            <div class="form p-4">
                <input class="form-check-input" type="checkbox" name="anonycheck" value="yes">
                <?= e(__('UPLOAD_ANONY')) ?>
            </div>
        <?php endif; ?>

        <p class="text-center"><?= e(__('DESCRIPTION')) ?></p>
        <?php textbbcode('upload', 'descr'); ?>

        <p class="text-center">
            <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
        </p>
    </form>
</div>
<?php
theme_footer();