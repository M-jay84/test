<?php
declare(strict_types=1);

/**
 * Sidebar: grid of recent torrent poster images (max 8).
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TmdbLogic;
use Bootstrap\Models\TorrentsLogic;

if (Auth::check() || empty(CONFIG['MEMBERSONLY'])) {
    $res = TorrentsLogic::getAll('WHERE visible = ? AND banned = ?', ['yes', 'no']);
    if (($res['count'] ?? 0) < 1) {
        return;
    }

    block_begin(__('LATEST_POSTERS'));
    echo '<table><tr>';
    $i = 0;
    foreach ($res['stmt'] as $r) {
        $img = e(TmdbLogic::getImage($r));
        $href = url('torrent/view/' . (int) $r['id']);
        // Two posters per row
        if ($i > 0 && $i % 2 === 1) {
            echo '<td><a href="' . $href . '"><img src="' . $img . '" height="100" width="100" alt=""></a></td></tr>';
        } else {
            echo '<tr><td><a href="' . $href . '"><img src="' . $img . '" height="100" width="80" alt=""></a></td>';
        }
        $i++;
        if ($i > 7) {
            break;
        }
    }
    echo '</tr></table>';
    block_end();
}