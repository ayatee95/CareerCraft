<?php

/*
 * Shared layout for the private pages: sidebar, mobile top bar and the opening <main>.
 * Set $pageTitle and $activeNav ('dashboard', 'profile', 'skills', 'projects', 'resume', 'settings')
 * before including this file, and close the page with includes/app_footer.php.
 */

$activeNav = isset($activeNav) ? $activeNav : "";

$bodyClass = "app";

include __DIR__ . "/header.php";

$navItems = array(
    array("dashboard", "dashboard.php", "Dashboard", "home"),
    array("profile",   "profile.php",   "Profile",   "user"),
    array("skills",    "skills.php",    "Skills",    "code"),
    array("projects",  "projects.php",  "Projects",  "briefcase"),
    array("resume",    "resume.php",    "Resume",    "file-text"),
    array("settings",  "settings.php",  "Settings",  "settings")
);

$navName = isset($_SESSION['fullname']) ? (string)$_SESSION['fullname'] : "";

$navInitial = initial_letter($navName);

// Profile picture for the sidebar (only when the file really exists)
$navAvatar = "";

if(isset($conn) && isset($_SESSION['user_id'])){

    $navUser = db_one($conn, "SELECT profile_image FROM users WHERE id = ? LIMIT 1", "i", array((int)$_SESSION['user_id']));

    if($navUser && !empty($navUser['profile_image']) && stored_file_exists("profiles", $navUser['profile_image'])){

        $navAvatar = $navUser['profile_image'];

    }

}

?>
<noscript><style>.sidebar{position:static;transform:none;width:auto}.app-content{margin-left:0}.mobile-bar{display:none}</style></noscript>

<a class="skip-link" href="#main">Skip to main content</a>

<aside class="sidebar" id="sidebar" aria-label="Sidebar">

    <div class="sidebar-head">

        <a class="brand" href="dashboard.php">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-text">CareerCraft<small>Portfolio Builder</small></span>
        </a>

        <button type="button" class="icon-btn sidebar-close" data-nav-close aria-label="Close menu"><?php echo icon('x'); ?></button>

    </div>

    <div class="sidebar-label">Menu</div>

    <nav class="sidebar-nav" aria-label="Main navigation">

        <?php foreach($navItems as $item){ ?>

        <a class="nav-link<?php if($activeNav === $item[0]){ ?> is-active<?php } ?>" href="<?php echo $item[1]; ?>"<?php if($activeNav === $item[0]){ ?> aria-current="page"<?php } ?>>
            <?php echo icon($item[3]); ?>
            <span><?php echo $item[2]; ?></span>
        </a>

        <?php } ?>

    </nav>

    <div class="sidebar-footer">

        <div class="user-card">

            <?php if($navAvatar !== ""){ ?>
            <img class="avatar" src="uploads/profiles/<?php echo e(rawurlencode($navAvatar)); ?>" alt="" width="44" height="44">
            <?php }else{ ?>
            <span class="avatar" aria-hidden="true"><?php echo e($navInitial); ?></span>
            <?php } ?>

            <div class="user-meta">
                <span class="user-name"><?php echo e($navName); ?></span>
                <span class="user-role">Signed in</span>
            </div>

        </div>

        <form method="POST" action="logout.php">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-ghost btn-sm btn-block"><?php echo icon('log-out'); ?> Logout</button>
        </form>

    </div>

</aside>

<div class="sidebar-overlay" data-nav-close></div>

<div class="app-content">

    <header class="mobile-bar">

        <button type="button" class="icon-btn" data-nav-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Open menu"><?php echo icon('menu'); ?></button>

        <a class="brand" href="dashboard.php">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-text">CareerCraft</span>
        </a>

    </header>

    <main class="app-main" id="main" tabindex="-1">
