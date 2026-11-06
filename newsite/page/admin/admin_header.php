<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;

$GLOBALS['tstart'] = microtime(true);
$user   = Auth::user();
$avatar = !empty($user->avatar ?? null)
    ? (string) $user->avatar
    : url('page/theme/default/images/misc/default_avatar.webp');
$uid    = (int) ($user->id ?? Auth::id() ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?= e(CONFIG['CHARSET'] ?? 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="M-jay">
    <title><?= e($TITLE ?? $GLOBALS['page_title'] ?? 'Admin CP') ?></title>
    <script src="https://kit.fontawesome.com/2da665f0cd.js" crossorigin="anonymous"></script>
    <link href="<?= e(url('page/theme/default/default_style.css')) ?>" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/styles/atom-one-dark.min.css">
    <style>
        .admin-wrapper { display: flex; min-height: calc(100vh - 58px); }
        .admin-sidebar {
            width: 280px; background: #121212; border-right: 1px solid #1e3a8a;
            flex-shrink: 0; overflow-y: auto; padding: 12px 0;
        }
        .admin-sidebar .menu-section { margin-bottom: 4px; }
        .admin-sidebar .menu-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 11px 16px; background: #1a1a1a; color: #e0e0e0; font-weight: 600;
            cursor: pointer; border-left: 3px solid transparent; transition: all 0.2s ease;
            user-select: none;
        }
        .admin-sidebar .menu-header:hover { background: #1e1e1e; border-left-color: #3b82f6; }
        .admin-sidebar .menu-header.active { background: #1e3a8a; border-left-color: #60a5fa; color: #fff; }
        .admin-sidebar .menu-header i.arrow { transition: transform 0.2s ease; font-size: 12px; }
        .admin-sidebar .menu-header.active i.arrow { transform: rotate(90deg); }
        .admin-sidebar .menu-items { display: none; background: #0d0d0d; padding: 6px 0; }
        .admin-sidebar .menu-items.open { display: block; }
        .admin-sidebar .menu-items a {
            display: block; padding: 8px 16px 8px 28px; color: #c9c9c9; font-size: 14px;
            border-left: 3px solid transparent; transition: all 0.15s ease;
        }
        .admin-sidebar .menu-items a:hover { background: #1e1e1e; color: #60a5fa; border-left-color: #3b82f6; }
        .admin-sidebar .menu-items a.active {
            background: #1e3a8a; color: #fff; border-left-color: #60a5fa; font-weight: 600;
        }
        .admin-sidebar .menu-items a.active:hover { background: #1e40af; color: #fff; }
        .admin-content { flex: 1; padding: 20px; overflow-x: auto; background: #0d0d0d; }
        .admin-navbar {
            background: #121212; border-bottom: 1px solid #1e3a8a; height: 58px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px; position: sticky; top: 0; z-index: 100;
        }
        .admin-navbar .brand img { height: 40px; }
        .admin-navbar .user-area { display: flex; align-items: center; gap: 12px; }
        .admin-navbar .user-avatar {
            width: 38px; height: 38px; border-radius: 50%; border: 2px solid #3b82f6;
            cursor: pointer; object-fit: cover;
        }
        .dropdown { position: relative; }
        .dropdown-menu {
            display: none; position: absolute; right: 0; top: 100%; margin-top: 8px;
            background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px;
            min-width: 180px; box-shadow: 0 8px 20px rgba(0,0,0,0.5); z-index: 200; overflow: hidden;
        }
        .dropdown.open .dropdown-menu { display: block; }
        .dropdown-menu a {
            display: block; padding: 10px 14px; color: #c9c9c9; border-bottom: 1px solid #2a2a2a;
        }
        .dropdown-menu a:last-child { border-bottom: none; }
        .dropdown-menu a:hover { background: #2563eb; color: #fff; }
        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed; left: -280px; top: 58px; bottom: 0; z-index: 90;
                transition: left 0.25s ease;
            }
            .admin-sidebar.open { left: 0; }
            .admin-content { padding: 15px; }
        }
    </style>
</head>
<body>
<div class="admin-navbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <button type="button" onclick="toggleSidebar()" id="sidebarToggle"
                style="background:none;border:none;color:#c9c9c9;font-size:1.3rem;cursor:pointer;display:none;">
            <i class="fa fa-bars"></i>
        </button>
        <a href="<?= e(url()) ?>" class="brand">
            <img src="<?= e(url('page/theme/default/images/misc/logo.webp')) ?>" alt="Logo">
        </a>
        <strong style="color:#60a5fa;margin-left:8px;"><?= e(__('ADMIN')) ?></strong>
    </div>
    <div class="user-area">
        <div class="dropdown" id="userDropdown">
            <img src="<?= e($avatar) ?>" class="user-avatar" onclick="toggleDropdown('userDropdown')" alt="">
            <div class="dropdown-menu">
                <a href="<?= e(url('profile/view/' . $uid)) ?>"><?= e(__('PROFILE')) ?></a>
                <a href="<?= e(url('admin/admincp')) ?>"><?= e(__('STAFF')) ?></a>
                <a href="<?= e(url('authorise/logout')) ?>"><?= e(__('LOGOUT')) ?></a>
            </div>
        </div>
    </div>
</div>

<div class="admin-wrapper">
    <div class="admin-sidebar" id="adminSidebar">
        <div class="menu-section">
            <div class="menu-header active" onclick="toggleMenu(this)">
                <span><i class="fa fa-home"></i> <?= e(__('HOME')) ?></span>
                <i class="fa fa-chevron-right arrow"></i>
            </div>
            <div class="menu-items open">
                <a href="<?= e(url()) ?>"><?= e(__('HOME')) ?></a>
                <a href="<?= e(url('admin/admincp')) ?>"><?= e(__('ADMIN')) ?></a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-header" onclick="toggleMenu(this)">
                <span><i class="fa fa-users"></i> <?= e(__('USERS')) ?></span>
                <i class="fa fa-chevron-right arrow"></i>
            </div>
            <div class="menu-items">
                <a href="<?= e(url('users/search')) ?>"><?= e(__('USER_SEARCH')) ?></a>
                <a href="<?= e(url('group/view')) ?>"><?= e(__('GROUPS')) ?></a>
                <a href="<?= e(url('ban/admin')) ?>"><?= e(__('BANNED')) ?></a>
                <a href="<?= e(url('warning/admin')) ?>"><?= e(__('WARNINGS')) ?></a>
                <a href="<?= e(url('inactive/view/30')) ?>"><?= e(__('INACTIVE')) ?></a>
                <a href="<?= e(url('invite/home')) ?>"><?= e(__('INVITES')) ?></a>
                <a href="<?= e(url('invite/home/pending')) ?>"><?= e(__('PENDING')) ?></a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-header" onclick="toggleMenu(this)">
                <span><i class="fa fa-folder"></i> <?= e(__('CONTENT')) ?></span>
                <i class="fa fa-chevron-right arrow"></i>
            </div>
            <div class="menu-items">
                <a href="<?= e(url('categorie/view')) ?>"><?= e(__('CATEGORIES')) ?></a>
                <a href="<?= e(url('torrents/admin')) ?>"><?= e(__('TORRENT')) ?></a>
                <a href="<?= e(url('comment/admin')) ?>"><?= e(__('COMMENT')) ?></a>
                <a href="<?= e(url('news/view')) ?>"><?= e(__('NEWS')) ?></a>
                <a href="<?= e(url('tag/admin')) ?>"><?= e(__('TAGS')) ?></a>
                <a href="<?= e(url('forumcat/admin')) ?>"><?= e(__('FORUM')) ?></a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-header" onclick="toggleMenu(this)">
                <span><i class="fa fa-server"></i> <?= e(__('TRACKER')) ?></span>
                <i class="fa fa-chevron-right arrow"></i>
            </div>
            <div class="menu-items">
                <a href="<?= e(url('peers/admin')) ?>"><?= e(__('PEERS')) ?></a>
                <a href="<?= e(url('client/view/peers')) ?>"><?= e(__('CLIENTS')) ?></a>
                <a href="<?= e(url('snatch/admin')) ?>"><?= e(__('SNATCH')) ?></a>
                <a href="<?= e(url('task/clearshout')) ?>"><?= e(__('SHOUTBOX_CLEAR')) ?></a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-header" onclick="toggleMenu(this)">
                <span><i class="fa fa-cogs"></i> <?= e(__('SYSTEM')) ?></span>
                <i class="fa fa-chevron-right arrow"></i>
            </div>
            <div class="menu-items">
                <a href="<?= e(url('log/view')) ?>"><?= e(__('LOGS')) ?></a>
                <a href="<?= e(url('message/admin')) ?>"><?= e(__('MESSAGES')) ?></a>
                <a href="<?= e(url('report/view')) ?>"><?= e(__('REPORT')) ?></a>
                <a href="<?= e(url('email/view')) ?>"><?= e(__('EMAIL')) ?></a>
            </div>
        </div>

        <div style="padding: 12px 10px 0;">
            <?php require_once BASEROOT . '/page/block/statistics_block.php'; ?>
            <?php require_once BASEROOT . '/page/block/usersonline_block.php'; ?>
            <?php require_once BASEROOT . '/page/block/newestmember_block.php'; ?>
            <?php require_once BASEROOT . '/page/block/serverload_block.php'; ?>
        </div>
    </div>

    <div class="admin-content">