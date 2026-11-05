<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;

// Start page timer
$GLOBALS['tstart'] = microtime(true);

$pageTitle = $GLOBALS['page_title'] ?? ($data['TITLE'] ?? (CONFIG['SITENAME'] ?? 'Site'));

// ---- Auth instead of raw session ----
$isLoggedIn = Auth::check();
$userId     = Auth::id();
$user       = Auth::user();   // object (guest or member)
?>
<!DOCTYPE html>
<html lang="<?= e('en') ?>">
<head>
    <meta charset="<?= e(CONFIG['CHARSET'] ?? 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="M-jay">
    <meta name="description" content="M-BiTsy is a feature packed and highly customisable PHP/PDO/MVC Based BitTorrent tracker.">
    <title><?= e($pageTitle) ?></title>

    <script src="https://kit.fontawesome.com/2da665f0cd.js" crossorigin="anonymous"></script>
    <link href="<?= url('page/theme/' . get_theme() . '/' . get_theme() . '_style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/styles/atom-one-dark.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
</head>
<body>

<nav class="tt_navbar">
    <div class="nav-inner">

        <div class="nav-left">
            <button class="menu-toggle" id="menuToggle" type="button">
                <i class="fa fa-bars"></i>
            </button>

            <a href="<?= url() ?>" class="nav-brand">
                <img src="<?= url('page/theme/default/images/misc/logo.webp') ?>" alt="Logo">
            </a>

            <?php if ($isLoggedIn): ?>
                <form class="nav-search" method="get" action="<?= url('forum/search') ?>">
                    <input type="search" name="keywords" placeholder="Search..." id="keywords">
                </form>
            <?php endif; ?>
        </div>

        <div class="nav-right">
            <?php if ($isLoggedIn): ?>

                <?php
                $messageQuery = "SELECT messages.*, users.avatar, users.username, users.class, `groups`.Color
                                 FROM messages
                                 LEFT JOIN users ON users.id = messages.sender
                                 LEFT JOIN `groups` ON groups.group_id = users.class
                                 WHERE receiver = ? AND unread = 'yes' AND location IN ('in','both')
                                 LIMIT 3";
                $messages = DB::run($messageQuery, [$userId])->fetchAll();
                $unreadmail = count($messages);
                ?>

                <!-- Messages Dropdown -->
                <div class="dropdown" id="msgDropdown">
                    <div class="nav-icon" onclick="toggleDropdown('msgDropdown')">
                        <i class="fa fa-envelope"></i>
                        <?php if ($unreadmail > 0): ?>
                            <span class="badge"><?= $unreadmail ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-menu">
                        <?php if ($unreadmail === 0): ?>
                            <div class="dropdown-header"><?= __('NO_NEW_MESSAGES') ?></div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <?php
                                $avatar = empty($message['avatar'])
                                    ? url('page/theme/default/images/misc/default_avatar.webp')
                                    : $message['avatar'];
                                ?>
                                <a href="<?= url("message/index/{$message['id']}/inbox") ?>" class="dropdown-item">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= e($avatar) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
                                        <div>
                                            <div>
                                                <?= __('PM_BY') ?>
                                                <span style="color:<?= e($message['Color'] ?? '#000') ?>">
                                                    <?= e($message['username']) ?>
                                                </span>
                                            </div>
                                            <small><?= e($message['added']) ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="<?= url('message/mailbox') ?>" class="dropdown-item text-center">
                            <?= __('SEE_ALL_MESSAGES') ?>
                        </a>
                    </div>
                </div>

                <!-- User Dropdown -->
                <?php
                $userAvatar = !empty($user->avatar)
                    ? $user->avatar
                    : url('page/theme/default/images/misc/default_avatar.webp');
                ?>
                <div class="dropdown" id="userDropdown">
                    <img src="<?= e($userAvatar) ?>" class="user-avatar" onclick="toggleDropdown('userDropdown')" alt="Avatar">
                    <div class="dropdown-menu">
                        <a href="<?= url('profile/view/' . $userId) ?>" class="dropdown-item">
                            <?= __('PROFILE') ?>
                        </a>
                        <?php if (Auth::isStaff()): ?>
                            <a href="<?= url('admin/admincp') ?>" class="dropdown-item">
                                <?= __('STAFF') ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('authorize/logout') ?>" class="dropdown-item">
                            <?= __('LOGOUT') ?>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Guest -->
                <a href="<?= url('authorize/login') ?>" class="ttbtn"><?= __('LOGIN') ?></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <div class="layout">

        <div class="portal_sidebar">
            <?php
            $hideSidebars = false;
            if (!$hideSidebars) {
                include BASEROOT . "/page/block/login_block.php";
                include BASEROOT . "/page/block/maincats_block.php";
                include BASEROOT . "/page/block/popular_block.php";
                include BASEROOT . "/page/block/latestuploads_block.php";
                include BASEROOT . "/page/block/mostactivetorrents_block.php";
                include BASEROOT . "/page/block/latestimages_block.php";
                          
            }
            ?>
        </div>

        <div class="portalmain">