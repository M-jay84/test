<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ToptenLogic;

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$limit = (int) param(0, 10);
$info  = ToptenLogic::getAll($limit);

theme_header(__('TOP_10'));
render_flash();
block_begin(__('TOP_10'));
?>
<div class="row">
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['active']['title']) ?><br><?= toptorrent($info['active']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['snatch']['title']) ?><br><?= toptorrent($info['snatch']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['country']['title']) ?><br><?= topcountry($info['country']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['uploaders']['title']) ?><br><?= topuser($info['uploaders']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['downloaders']['title']) ?><br><?= topuser($info['downloaders']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['fastest']['title']) ?><br><?= topuser($info['fastest']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['bestsharer']['title']) ?><br><?= topuser($info['bestsharer']['rows']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($info['worstshare']['title']) ?><br><?= topuser($info['worstshare']['rows']) ?></div>
    </div>
</div>
<?php
block_end();
theme_footer();