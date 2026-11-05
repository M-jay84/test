<?php $GLOBALS['tstart'] = array_sum(explode(" ", microtime())); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php echo CONFIG['CHARSET'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="M-jay">
    <title><?= $TITLE ?? 'Admin CP' ?></title>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/2da665f0cd.js" crossorigin="anonymous"></script>

    <!-- Custom CSS -->
    <link href="<?= URLROOT ?>/page/theme/default/default_style.css" rel="stylesheet">

    <!-- Highlight CSS -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/styles/atom-one-dark.min.css">

    <style>
        /* ===== Admin Layout ===== */
        .admin-wrapper {
            display: flex;
            min-height: calc(100vh - 58px);
        }

        /* Left Sidebar */
        .admin-sidebar {
            width: 280px;
            background: #121212;
            border-right: 1px solid #1e3a8a;
            flex-shrink: 0;
            overflow-y: auto;
            padding: 12px 0;
        }

        .admin-sidebar .menu-section {
            margin-bottom: 4px;
        }

        .admin-sidebar .menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            background: #1a1a1a;
            color: #e0e0e0;
            font-weight: 600;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            user-select: none;
        }

        .admin-sidebar .menu-header:hover {
            background: #1e1e1e;
            border-left-color: #3b82f6;
        }

        .admin-sidebar .menu-header.active {
            background: #1e3a8a;
            border-left-color: #60a5fa;
            color: #fff;
        }

        .admin-sidebar .menu-header i.arrow {
            transition: transform 0.2s ease;
            font-size: 12px;
        }

        .admin-sidebar .menu-header.active i.arrow {
            transform: rotate(90deg);
        }

        .admin-sidebar .menu-items {
            display: none;
            background: #0d0d0d;
            padding: 6px 0;
        }

        .admin-sidebar .menu-items.open {
            display: block;
        }

        .admin-sidebar .menu-items a {
            display: block;
            padding: 8px 16px 8px 28px;
            color: #c9c9c9;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: all 0.15s ease;
        }

        .admin-sidebar .menu-items a:hover {
            background: #1e1e1e;
            color: #60a5fa;
            border-left-color: #3b82f6;
        }

        /* Main Content */
        .admin-content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
            background: #0d0d0d;
        }

        /* Top Navbar (Admin) */
        .admin-navbar {
            background: #121212;
            border-bottom: 1px solid #1e3a8a;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-navbar .brand img {
            height: 40px;
        }

        .admin-navbar .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-navbar .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #3b82f6;
            cursor: pointer;
            object-fit: cover;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            min-width: 180px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
            z-index: 200;
            overflow: hidden;
        }
        .dropdown.open .dropdown-menu {
            display: block;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px 14px;
            color: #c9c9c9;
            border-bottom: 1px solid #2a2a2a;
        }
        .dropdown-menu a:last-child {
            border-bottom: none;
        }
        .dropdown-menu a:hover {
            background: #2563eb;
            color: #fff;
        }
/* Active/current link highlight */
.admin-sidebar .menu-items a.active {
    background: #1e3a8a;
    color: #fff;
    border-left-color: #60a5fa;
    font-weight: 600;
}

.admin-sidebar .menu-items a.active:hover {
    background: #1e40af;
    color: #fff;
}
        /* Mobile */
        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed;
                left: -280px;
                top: 58px;
                bottom: 0;
                z-index: 90;
                transition: left 0.25s ease;
            }
            .admin-sidebar.open {
                left: 0;
            }
            .admin-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <div class="admin-navbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button onclick="toggleSidebar()" style="background:none;border:none;color:#c9c9c9;font-size:1.3rem;cursor:pointer;display:none;" id="sidebarToggle">
                <i class="fa fa-bars"></i>
            </button>
            <a href="<?=url() ?>" class="brand">
                <img src="<?=url('page/theme/default/images/misc/logo.webp') ?>" alt="Logo">
            </a>
            <strong style="color:#60a5fa;margin-left:8px;">Admin CP</strong>
        </div>

        <div class="user-area">
            <?php
            $avatar = !empty($_SESSION['avatar']) ? htmlspecialchars($_SESSION['avatar']) :url('page/theme/default/images/misc/default_avatar.webp');
            ?>
            <div class="dropdown" id="userDropdown">
                <img src="<?= $avatar ?>" class="user-avatar" onclick="toggleDropdown('userDropdown')" alt="Avatar">
                <div class="dropdown-menu">
                    <a href="<?=url('profile/view/' . ($_SESSION['id'] ?? $current_user->id ?? '')) ?>"><?= LANG['PROFILE'] ?></a>
                    <a href="<?=url('admin/admincp') ?>"><?= LANG['STAFF'] ?></a>
                    <a href="<?=url('authorise/logout') ?>"><?= LANG['LOGOUT'] ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-wrapper">

        <!-- Left Sidebar -->
        <div class="admin-sidebar" id="adminSidebar">

            <!-- Quick Links -->
            <div class="menu-section">
                <div class="menu-header active" onclick="toggleMenu(this)">
                    <span><i class="fa fa-home"></i> Quick Links</span>
                    <i class="fa fa-chevron-right arrow"></i>
                </div>
                <div class="menu-items open">
                    <a href="<?=url() ?>"><?= LANG["HOME"] ?></a>
                    <a href="<?=url('admin/admincp') ?>"><?= LANG["ADMIN"] ?></a>
                </div>
            </div>

            <!-- Users & Security -->
            <div class="menu-section">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span><i class="fa fa-users"></i> Users & Security</span>
                    <i class="fa fa-chevron-right arrow"></i>
                </div>
                <div class="menu-items">
                    <a href="<?=url('users/search') ?>"><?= LANG["USER_SEARCH"] ?></a>
                    <a href="<?=url('group/view') ?>"><?= LANG["GROUPS"] ?></a>
                    <a href="<?=url('ban/admin') ?>"><?= LANG["BANNED"] ?></a>
                    <a href="<?=url('warning/admin') ?>"><?= LANG["WARNINGS"] ?></a>
                    <a href="<?=url('inactive/view/30') ?>"><?= LANG["INACTIVE"] ?></a>
                    <a href="<?=url('invite/home') ?>"><?= LANG["INVITES"] ?></a>
                    <a href="<?=url('invite/home/pending') ?>"><?= LANG["PENDING"] ?></a>
                </div>
            </div>

            <!-- Content -->
            <div class="menu-section">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span><i class="fa fa-folder"></i> Content</span>
                    <i class="fa fa-chevron-right arrow"></i>
                </div>
                <div class="menu-items">
                    <a href="<?=url('categorie/view') ?>"><?= LANG["CATEGORIES"] ?></a>
                    <a href="<?=url('torrents/admin') ?>"><?= LANG["TORRENT"] ?></a>
                    <a href="<?=url('comment/admin') ?>"><?= LANG["COMMENT"] ?></a>
                    <a href="<?=url('news/view') ?>"><?= LANG["NEWS"] ?></a>
                    <a href="<?=url('tag/admin') ?>"><?= LANG["TAGS"] ?></a>
                    <a href="<?=url('forumcat/admin') ?>"><?= LANG["FORUM"] ?></a>
                </div>
            </div>

            <!-- Tracker -->
            <div class="menu-section">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span><i class="fa fa-server"></i> Tracker</span>
                    <i class="fa fa-chevron-right arrow"></i>
                </div>
                <div class="menu-items">
                    <a href="<?=url('peers/admin') ?>"><?= LANG["PEERS"] ?></a>
                    <a href="<?=url('client/view/peers') ?>"><?= LANG["CLIENTS"] ?></a>
                    <a href="<?=url('snatch/admin') ?>"><?= LANG["SNATCH"] ?></a>
                    <a href="<?=url('task/clearshout') ?>"><?= LANG["SHOUTBOX_CLEAR"] ?></a>
                </div>
            </div>

            <!-- System -->
            <div class="menu-section">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span><i class="fa fa-cogs"></i> System</span>
                    <i class="fa fa-chevron-right arrow"></i>
                </div>
                <div class="menu-items">
                    <a href="<?=url('log/view') ?>"><?= LANG["LOGS"] ?></a>
                    <a href="<?=url('message/admin') ?>"><?= LANG["MESSAGES"] ?></a>
                    <a href="<?=url('report/view') ?>"><?= LANG["REPORT"] ?></a>
                    <a href="<?=url('email/view') ?>"><?= LANG["EMAIL"] ?></a>
                </div>
            </div>

            <!-- Extra Blocks (optional) -->
            <div style="padding: 12px 10px 0;">
                <?php require_once BASEROOT . "/page/block/statistics_block.php"; ?>
                <?php require_once BASEROOT . "/page/block/usersonline_block.php"; ?>
                <?php require_once BASEROOT . "/page/block/newestmember_block.php"; ?>
                <?php require_once BASEROOT . "/page/block/serverload_block.php"; ?>
            </div>

        </div>

        <!-- Main Content Area -->
        <div class="admin-content">
            <?php block_begin('STAFF'); ?>
                <?php require_once BASEROOT . "/page/admin/admin_panel.php"; ?>
            <?php block_end(); ?>