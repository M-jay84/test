<?php
declare(strict_types=1);

/**
 * RSS 2.0 feed of recent torrents.
 * URL: /rss/view  (or /rss)
 *
 * No theme/layout — pure XML response.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RssLogic;

$current_user = Auth::user();
$res = RssLogic::rssInfo($current_user);

// Clear any buffered HTML before sending XML
while (ob_get_level() > 0) {
    ob_end_clean();
}

$charset = (string) (CONFIG['CHARSET'] ?? 'UTF-8');
$site    = (string) (CONFIG['SITENAME'] ?? 'Site');
$linkKey = (string) ($res['link'] ?? ''); // passkey / auth token from model

header('Content-Type: text/xml; charset=' . $charset);
header('Cache-Control: public, max-age=300');

echo '<?xml version="1.0" encoding="' . htmlspecialchars($charset, ENT_XML1) . '"?>' . "\n";
?>
<rss version="2.0">
  <channel>
    <title><?= htmlspecialchars($site, ENT_XML1) ?> | RSS</title>
    <link><?= htmlspecialchars(rtrim(URLROOT, '/'), ENT_XML1) ?></link>
    <description><?= htmlspecialchars($site, ENT_XML1) ?> RSS</description>
    <language>en-us</language>
<?php
foreach ($res['res'] ?? [] as $row) {
    $id      = (int) ($row['id'] ?? 0);
    $name    = (string) ($row['name'] ?? '');
    $pubdate = date('r', strtotime((string) ($row['added'] ?? 'now')) ?: time());
    $cat     = trim(($row['cat_parent'] ?? '') . ': ' . ($row['cat_name'] ?? ''), ': ');
    $desc    = 'Size: ' . mksize((int) ($row['size'] ?? 0))
             . ' Seeders: ' . (int) ($row['seeders'] ?? 0)
             . ' Leechers: ' . (int) ($row['leechers'] ?? 0);

    // Align paths with your router (torrent/view, download, etc.)
    $guid = rtrim(URLROOT, '/') . '/torrent/view/' . $id;
    $dl   = rtrim(URLROOT, '/') . '/torrents/download/' . $id;
    if ($linkKey !== '') {
        $dl .= '/' . rawurlencode($linkKey);
    }
    ?>
    <item>
      <title><?= htmlspecialchars($name, ENT_XML1) ?></title>
      <guid isPermaLink="true"><?= htmlspecialchars($guid, ENT_XML1) ?></guid>
      <link><?= htmlspecialchars($dl, ENT_XML1) ?></link>
      <pubDate><?= htmlspecialchars($pubdate, ENT_XML1) ?></pubDate>
      <category><?= htmlspecialchars($cat, ENT_XML1) ?></category>
      <description><?= htmlspecialchars($desc, ENT_XML1) ?></description>
    </item>
    <?php
}
?>
  </channel>
</rss>
<?php
exit;