<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\Cache; // adjust namespace

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$TTCache = new Cache(); // or \Bootstrap\Libs\Cache
$TTCache->Delete('sql_cache/latestuploadsblock');
$TTCache->Delete('sql_cache/mostactivetorrents_block');
$TTCache->Delete('sql_cache/newestmember_block');
$TTCache->Delete('sql_cache/usersonline_block');

redirect('home', __('PURGE_COMPLETED'), 'success'); // or admin dashboard path