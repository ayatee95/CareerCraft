<?php

include "includes/auth.php";

include "includes/database.php";

$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';

$user_id = (int)$_SESSION['user_id'];

// Profile Status

$profileRow = db_one($conn, "SELECT * FROM profiles WHERE user_id = ? LIMIT 1", "i", array($user_id));

// "Completed" only when the important profile fields are really filled in
$profileDone = false;

if($profileRow){

    $jobTitle = isset($profileRow['job_title']) ? trim((string)$profileRow['job_title']) : "";

    $bioText = isset($profileRow['bio']) ? trim((string)$profileRow['bio']) : "";

    $profileDone = ($jobTitle !== "" && $bioText !== "");

}

// Profile picture (only if the file really exists)

$me = db_one($conn, "SELECT profile_image FROM users WHERE id = ? LIMIT 1", "i", array($user_id));

$avatar = ($me && !empty($me['profile_image']) && stored_file_exists("profiles", $me['profile_image'])) ? $me['profile_image'] : "";

// Count Skills

$row = db_one($conn, "SELECT COUNT(*) AS total FROM skills WHERE user_id = ?", "i", array($user_id));

$skillCount = $row ? (int)$row['total'] : 0;

// Count Projects

$row = db_one($conn, "SELECT COUNT(*) AS total FROM projects WHERE user_id = ?", "i", array($user_id));

$projectCount = $row ? (int)$row['total'] : 0;

// Resume Status

$resumeRow = db_one($conn, "SELECT * FROM resume WHERE user_id = ? ORDER BY id DESC LIMIT 1", "i", array($user_id));

$resumeDone = ($resumeRow && stored_file_exists("resumes", $resumeRow['file_name']));

// Career progress: 4 real steps (profile, at least one skill, at least one project, resume)
$steps = array($profileDone, $skillCount > 0, $projectCount > 0, $resumeDone);

$stepsDone = 0;

foreach($steps as $step){

    if($step){
        $stepsDone++;
    }

}

$progress = (int)round($stepsDone / count($steps) * 100);

$pageTitle = "Dashboard | CareerCraft";
$activeNav = "dashboard";
include "includes/app_header.php";
?>

<header class="page-header">

    <div class="page-header-main">

        <?php if($avatar !== ""){ ?>
        <img class="avatar avatar-lg" src="uploads/profiles/<?php echo e(rawurlencode($avatar)); ?>" alt="Your profile picture" width="84" height="84">
        <?php } ?>

        <div>
            <h1 class="page-title">Welcome back, <span class="accent"><?php echo e($fullname); ?></span></h1>
            <p class="page-subtitle">Manage your professional portfolio from one place.</p>
        </div>

    </div>

    <a class="btn btn-primary" href="portfolio.php?id=<?php echo $user_id; ?>" target="_blank" rel="noopener">
        View my portfolio <?php echo icon('external-link'); ?>
    </a>

</header>

<?php flash_render(); ?>

<section class="stats-grid" aria-label="Portfolio overview">

    <a class="stat-card" href="profile.php">
        <div class="stat-icon"><?php echo icon('user'); ?></div>
        <div class="stat-label">Profile</div>
        <?php if($profileDone){ ?>
        <div class="stat-value">Complete</div>
        <div class="stat-note"><span class="badge badge-success"><?php echo icon('check'); ?> Looking good</span></div>
        <?php }else{ ?>
        <div class="stat-value">Incomplete</div>
        <div class="stat-note"><span class="badge">Add your title and bio</span></div>
        <?php } ?>
    </a>

    <a class="stat-card" href="skills.php">
        <div class="stat-icon"><?php echo icon('code'); ?></div>
        <div class="stat-label">Skills</div>
        <div class="stat-value"><?php echo $skillCount; ?><small><?php echo ($skillCount === 1) ? "skill" : "skills"; ?></small></div>
        <div class="stat-note">
            <?php if($skillCount > 0){ ?>
            <span class="badge badge-gold">Manage skills</span>
            <?php }else{ ?>
            <span class="badge">Add your first skill</span>
            <?php } ?>
        </div>
    </a>

    <a class="stat-card" href="projects.php">
        <div class="stat-icon"><?php echo icon('briefcase'); ?></div>
        <div class="stat-label">Projects</div>
        <div class="stat-value"><?php echo $projectCount; ?><small><?php echo ($projectCount === 1) ? "project" : "projects"; ?></small></div>
        <div class="stat-note">
            <?php if($projectCount > 0){ ?>
            <span class="badge badge-gold">Manage projects</span>
            <?php }else{ ?>
            <span class="badge">Showcase your work</span>
            <?php } ?>
        </div>
    </a>

    <a class="stat-card" href="resume.php">
        <div class="stat-icon"><?php echo icon('file-text'); ?></div>
        <div class="stat-label">Resume</div>
        <?php if($resumeDone){ ?>
        <div class="stat-value">Uploaded</div>
        <div class="stat-note"><span class="badge badge-success"><?php echo icon('check'); ?> PDF ready</span></div>
        <?php }else{ ?>
        <div class="stat-value">Missing</div>
        <div class="stat-note"><span class="badge">Upload your CV</span></div>
        <?php } ?>
    </a>

</section>

<section class="card" aria-labelledby="progress-title">

    <div class="progress-wrap">

        <div>

            <h2 class="card-title" id="progress-title"><?php echo icon('trending-up'); ?> Career progress</h2>

            <p class="card-sub">Complete these steps to publish a polished portfolio.</p>

            <div class="progress-top">
                <span class="progress-percent"><?php echo $progress; ?>%</span>
                <span class="muted"><?php echo $stepsDone; ?> of 4 steps done</span>
            </div>

            <div class="progress" role="progressbar" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Portfolio completion">
                <div class="progress-bar" style="width:<?php echo $progress; ?>%"></div>
            </div>

        </div>

        <div class="checklist">

            <a class="check-item<?php if($profileDone){ ?> is-done<?php } ?>" href="profile.php">
                <span class="check-mark"><?php echo icon('check'); ?></span>
                <span>Complete your profile</span>
                <span class="check-go"><?php echo icon('arrow-right'); ?></span>
            </a>

            <a class="check-item<?php if($skillCount > 0){ ?> is-done<?php } ?>" href="skills.php">
                <span class="check-mark"><?php echo icon('check'); ?></span>
                <span>Add at least one skill</span>
                <span class="check-go"><?php echo icon('arrow-right'); ?></span>
            </a>

            <a class="check-item<?php if($projectCount > 0){ ?> is-done<?php } ?>" href="projects.php">
                <span class="check-mark"><?php echo icon('check'); ?></span>
                <span>Add a project</span>
                <span class="check-go"><?php echo icon('arrow-right'); ?></span>
            </a>

            <a class="check-item<?php if($resumeDone){ ?> is-done<?php } ?>" href="resume.php">
                <span class="check-mark"><?php echo icon('check'); ?></span>
                <span>Upload your resume</span>
                <span class="check-go"><?php echo icon('arrow-right'); ?></span>
            </a>

        </div>

    </div>

</section>

<section class="action-grid" aria-label="Quick actions">

    <a class="action-card" href="profile.php">
        <span class="icon-wrap"><?php echo icon('user'); ?></span>
        <h3>Profile</h3>
        <p>Edit your personal information and picture.</p>
        <span class="go">Open <?php echo icon('arrow-right'); ?></span>
    </a>

    <a class="action-card" href="skills.php">
        <span class="icon-wrap"><?php echo icon('code'); ?></span>
        <h3>Skills</h3>
        <p>Add, edit and remove your technical skills.</p>
        <span class="go">Open <?php echo icon('arrow-right'); ?></span>
    </a>

    <a class="action-card" href="projects.php">
        <span class="icon-wrap"><?php echo icon('briefcase'); ?></span>
        <h3>Projects</h3>
        <p>Show your best work with technologies and links.</p>
        <span class="go">Open <?php echo icon('arrow-right'); ?></span>
    </a>

    <a class="action-card" href="resume.php">
        <span class="icon-wrap"><?php echo icon('file-text'); ?></span>
        <h3>Resume</h3>
        <p>Upload or replace your CV (PDF).</p>
        <span class="go">Open <?php echo icon('arrow-right'); ?></span>
    </a>

</section>

<?php
include "includes/app_footer.php";
?>
