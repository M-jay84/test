<?php
declare(strict_types=1);

/**
 * Sidebar: posters from popular category IDs (CONFIG['PopularCats']).
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TmdbLogic;
use Bootstrap\Models\TorrentsLogic;

if (!Auth::check()) {
    return;
}

$cats = CONFIG['PopularCats'] ?? [];
if (!is_array($cats) || $cats === []) {
    return;
}

$tcat = implode(',', array_map('intval', $cats));
$q    = TorrentsLogic::popular($tcat);

block_begin(__('POPULAR'));
echo '<table><tr>';
$i = 0;
foreach ($q as $r) {
    $href = url('torrent/view/' . (int) $r['id']);
    $img  = e(TmdbLogic::getImage($r));
    if ($i > 0 && $i % 2 === 1) {
        echo '<td><a href="' . $href . '"><img src="' . $img . '" height="100" width="100" alt=""></a></td></tr>';
    } else {
        echo '<tr><td><a href="' . $href . '"><img src="' . $img . '" height="100" width="80" alt=""></a></td>';
    }
    $i++;
}
echo '</tr></table>';
block_end();