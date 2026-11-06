<?php
declare(strict_types=1);

use Bootstrap\Models\ShoutboxLogic;

$SHOUTS = ShoutboxLogic::shouts(80);
$defaultAv = url('page/theme/default/images/misc/default_avatar.webp');

theme_header(__('HISTORY'));
render_flash();
block_begin(__('HISTORY'));
?>
<table class="table table-striped">
<?php foreach ($SHOUTS as $row) :
    $av = !empty($row['avatar']) ? (string) $row['avatar'] : $defaultAv;
    ?>
    <tr>
        <td style="width:25%">
            <small><i class="fa fa-clock-o"></i> <?= e(user_timezone_date($row['date'])) ?></small>
            <img src="<?= e($av) ?>" width="20" height="20" alt="">
            <b><?= coloredname((int) $row['userid']) ?>:</b>
        </td>
        <td style="width:75%"><?= nl2br(format_comment($row['message'] ?? '')) ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php
block_end();
theme_footer();