<?php
declare(strict_types=1);

/**
 * Sidebar: OS / load / RAM / uptime (staff or members only).
 * Windows uses WMIC; Linux uses /proc and uptime.
 */

use Bootstrap\Libs\Auth;

if (!Auth::check()) {
    return;
}

block_begin('Server Load');

$up = $loadnow = $load5 = $load15 = $operatingsystem = '--';
$users = ['0' => 'NA', '1' => '--'];
$memused = $memtotal = '--';

if (str_starts_with(strtoupper(PHP_OS), 'WIN')) {
    $outputTotal = $outputFree = [];
    @exec('wmic ComputerSystem get TotalPhysicalMemory', $outputTotal);
    @exec('wmic OS get FreePhysicalMemory', $outputFree);
    $memoryTotal = $memoryFree = 0;
    foreach ($outputTotal as $line) {
        if (preg_match('/^[0-9]+$/', trim($line))) {
            $memoryTotal = (float) $line;
            break;
        }
    }
    foreach ($outputFree as $line) {
        if (preg_match('/^[0-9]+$/', trim($line))) {
            $memoryFree = (float) $line * 1024;
            break;
        }
    }
    $memtotal = mksize((int) $memoryTotal);
    $memused  = mksize((int) max(0, $memoryTotal - $memoryFree));
} else {
    $reguptime = @exec('uptime') ?: '';
    if ($reguptime && preg_match('/up (.*), *(\d+) (users?), .*: (.*), (.*), (.*)/', $reguptime, $uptime)) {
        $up      = preg_replace('/(\d\d):(\d\d)/', '\1h\2m', $uptime[1]);
        $users[0] = $uptime[2];
        $users[1] = $uptime[3];
        $loadnow = $uptime[4];
        $load5   = $uptime[5];
        $load15  = $uptime[6];
    }

    $temp = @file_get_contents('/proc/version') ?: '';
    if ($temp !== '') {
        $osarray = explode(' ', $temp);
        $distro  = 'Unknown Distro';
        if (is_file('/etc/lsb-release')) {
            $lsb = (string) file_get_contents('/etc/lsb-release');
            if (preg_match('/DISTRIB_DESCRIPTION="(.*)"/', $lsb, $m)) {
                $distro = $m[1];
            }
        }
        $operatingsystem = $distro . ' (' . ($osarray[0] ?? '') . ' ' . ($osarray[2] ?? '') . ')';
    }

    $meminfo = @file_get_contents('/proc/meminfo') ?: '';
    if (preg_match('/^MemTotal:\s*(\d+) kB/m', $meminfo, $m)) {
        $total = (int) $m[1] * 1024;
        preg_match('/^MemFree:\s*(\d+) kB/m', $meminfo, $mf);
        preg_match('/^Buffers:\s*(\d+) kB/m', $meminfo, $mb);
        preg_match('/^Cached:\s*(\d+) kB/m', $meminfo, $mc);
        $free    = ((int) ($mf[1] ?? 0) + (int) ($mb[1] ?? 0) + (int) ($mc[1] ?? 0)) * 1024;
        $memused  = mksize($total - $free);
        $memtotal = mksize($total);
    }
}
?>
<ul class="list-unstyled">
    <li>Current Users: <strong><?= e((string) $users[0]) ?></strong></li>
    <li>Current Load: <strong><?= e((string) $loadnow) ?></strong></li>
    <li>Load 5 mins ago: <strong><?= e((string) $load5) ?></strong></li>
    <li>Load 15 mins ago: <strong><?= e((string) $load15) ?></strong></li>
    <li>OS: <strong><?= e((string) $operatingsystem) ?></strong></li>
    <li>RAM: <strong>Used: <?= e((string) $memused) ?> / Total: <?= e((string) $memtotal) ?></strong></li>
    <li>Uptime: <strong><?= e((string) $up) ?></strong></li>
</ul>
<?php
block_end();