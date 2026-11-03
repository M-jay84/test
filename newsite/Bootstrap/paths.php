<?php

declare(strict_types=1);

// Disk paths only — never echo these in HTML
define('ATTACHMENT_PATH', BASEROOT . '/storage/attachments');
define('CACHE_PATH',BASEROOT . '/storage/cache');
define('IMAGE_PATH', BASEROOT . '/storage/images');
define('LOG_PATH', BASEROOT . '/storage/logs');
define('NFOS_PATH', BASEROOT . '/storage/nfos');
define('TORRENT_PATH', BASEROOT . '/storage/torrents');

// public_html images (avatars and thumbnails)
define('UPLOAD_PATH', BASEROOT . '/storage/uploads');

// View and controller paths
//define('VIEW_PATH', BASEROOT . '/App/Views');
//define('CONTROLLER_PATH', BASEROOT . '/App/Controllers');

// Public URL base — must stay a URL, never a C:\ path.
define('URLROOT', 'http://localhost/newsite');