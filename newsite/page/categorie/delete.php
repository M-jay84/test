<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CatagoriesLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$id   = (int) param(0, 0);
$sure = (string) param(1, '');

if ($id < 1) {
    redirect('categorie/view', __('MISSING_ID'), 'warning');
}

// Confirm form lives on page when not sure yet
if ($sure !== 'sure') {
    theme_admin_header(__('CATEGORIES'));
    render_flash();
    block_begin(__('CATEGORIES'));
    form_start(url("categorie/delete/{$id}/sure"), 'post');
    echo csrf_field();
    form_input('newcat', __('NEW_CAT_ID'), '', 'number', __('NEW_CAT_ID'), true);
    echo '<div class="text-center">';
    button(__('SUBMIT'));
    echo '</div>';
    form_end();
    block_end();
    theme_admin_footer();
    return;
}

if (!is_post() || !csrf_verify()) {
    redirect('categorie/view', __('INVALID_REQUEST'), 'warning');
}

CatagoriesLogic::deleteAndUpdateTorrent($id, true);