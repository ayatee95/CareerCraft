<?php

include "includes/database.php";

// The id must be a positive whole number
$raw_id = (isset($_GET['id']) && is_string($_GET['id'])) ? $_GET['id'] : "";

$user_id = filter_var($raw_id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

$user = null;

if($user_id !== false){

    // Only the columns the public page needs (never the password hash)
    $user = db_one($conn, "SELECT id, fullname, profile_image FROM users WHERE id = ? LIMIT 1", "i", array($user_id));

}

if(!$user){

    http_response_code(404);

    $pageTitle = "Portfolio not found | CareerCraft";

    $bodyClass = "portfolio";

    include "includes/header.php";

    ?>

<main class="notfound" id="main">

    <div>

        <?php echo icon('search'); ?>

        <h1>Portfolio not found</h1>

        <p>The portfolio you are looking for does not exist or the link is incorrect.</p>

        <a class="btn btn-primary" href="index.php">Back to home</a>

    </div>

</main>

<?php

    include "includes/footer.php";

    exit();

}

$profile = db_one($conn, "SELECT job_title, bio FROM profiles WHERE user_id = ? LIMIT 1", "i", array($user_id));

$skills = db_all($conn, "SELECT skill_name FROM skills WHERE user_id = ?", "i", array($user_id));

$projects = db_all($conn, "SELECT * FROM projects WHERE user_id = ?", "i", array($user_id));

$resume = db_one(
    $conn,
    "SELECT file_name FROM resume WHERE user_id = ? ORDER BY id DESC LIMIT 1",
    "i",
    array($user_id)
);

// A resume row is only shown when its file really exists
$resumeOk = ($resume && stored_file_exists("resumes", $resume['file_name']));

// Public information shown on the page (private data such as the phone number is never read here)
$jobTitle = isset($profile['job_title']) ? trim((string)$profile['job_title']) : "";

$bioText = isset($profile['bio']) ? trim((string)$profile['bio']) : "";

$hasImage = (!empty($user['profile_image']) && stored_file_exists("profiles", $user['profile_image']));

$initial = initial_letter($user['fullname']);

$hasContent = ($jobTitle !== "" || $bioText !== "" || !empty($skills) || !empty($projects) || $resumeOk);

$metaDescription = ($bioText !== "")
    ? text_excerpt($bioText, 160)
    : $user['fullname'] . ($jobTitle !== "" ? " - " . $jobTitle : "") . " | Portfolio";

$pageTitle = $user['fullname'] . " | CareerCraft Portfolio";

$bodyClass = "portfolio";

include "includes/header.php";

?>

<a class="skip-link" href="#main">Skip to main content</a>

<header class="pf-nav">

    <div class="container">

        <a class="brand" href="index.php">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-text">CareerCraft</span>
        </a>

        <nav class="pf-nav-links" aria-label="Portfolio sections">
            <?php if(!empty($skills)){ ?><a href="#skills">Skills</a><?php } ?>
            <?php if(!empty($projects)){ ?><a href="#projects">Projects</a><?php } ?>
            <?php if($resumeOk){ ?><a href="#resume">Resume</a><?php } ?>
        </nav>

    </div>

</header>

<main id="main">

    <section class="pf-hero">

        <div class="container">

            <?php if($hasImage){ ?>
            <img class="pf-avatar" src="uploads/profiles/<?php echo e(rawurlencode($user['profile_image'])); ?>" alt="Profile picture of <?php echo e($user['fullname']); ?>" width="168" height="168">
            <?php }else{ ?>
            <div class="pf-avatar" aria-hidden="true"><?php echo e($initial); ?></div>
            <?php } ?>

            <h1 class="pf-name"><?php echo e($user['fullname']); ?></h1>

            <?php if($jobTitle !== ""){ ?>
            <p class="pf-role"><?php echo e($jobTitle); ?></p>
            <?php } ?>

            <?php if($bioText !== ""){ ?>
            <p class="pf-bio"><?php echo e($bioText); ?></p>
            <?php } ?>

            <?php if($resumeOk){ ?>
            <div class="pf-actions">
                <a class="btn btn-primary" href="uploads/resumes/<?php echo e(rawurlencode($resume['file_name'])); ?>" target="_blank" rel="noopener noreferrer"><?php echo icon('download'); ?> Download resume</a>
            </div>
            <?php } ?>

        </div>

    </section>

    <?php if(!empty($skills)){ ?>

    <section class="pf-section" id="skills" aria-labelledby="skills-title">

        <div class="container">

            <h2 class="pf-section-title" id="skills-title"><?php echo icon('code'); ?> Skills</h2>

            <div class="chip-list pf-skills">
                <?php foreach($skills as $skill){ ?>
                <span class="chip"><?php echo e($skill['skill_name']); ?></span>
                <?php } ?>
            </div>

        </div>

    </section>

    <?php } ?>

    <?php if(!empty($projects)){ ?>

    <section class="pf-section" id="projects" aria-labelledby="projects-title">

        <div class="container">

            <h2 class="pf-section-title" id="projects-title"><?php echo icon('briefcase'); ?> Projects</h2>

            <div class="pf-projects">

                <?php foreach($projects as $project){ ?>

                <article class="pf-project">

                    <h3><?php echo e($project['project_name']); ?></h3>

                    <p><?php echo e($project['description']); ?></p>

                    <?php if(!empty($project['technologies'])){ ?>
                    <div class="chip-list">
                        <?php foreach(tech_list($project['technologies']) as $tech){ ?>
                        <span class="chip chip-muted"><?php echo e($tech); ?></span>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php $projectLink = isset($project['project_link']) ? safe_url($project['project_link']) : ""; ?>

                    <?php if($projectLink !== ""){ ?>
                    <a class="btn btn-secondary btn-sm" href="<?php echo e($projectLink); ?>" target="_blank" rel="noopener noreferrer">View project <?php echo icon('external-link'); ?></a>
                    <?php } ?>

                </article>

                <?php } ?>

            </div>

        </div>

    </section>

    <?php } ?>

    <?php if($resumeOk){ ?>

    <section class="pf-section" id="resume" aria-labelledby="resume-title">

        <div class="container">

            <h2 class="pf-section-title" id="resume-title"><?php echo icon('file-text'); ?> Resume</h2>

            <div class="pf-resume">

                <div>
                    <h3>Download my resume</h3>
                    <p>PDF document</p>
                </div>

                <a class="btn btn-primary" href="uploads/resumes/<?php echo e(rawurlencode($resume['file_name'])); ?>" target="_blank" rel="noopener noreferrer"><?php echo icon('download'); ?> Download resume</a>

            </div>

        </div>

    </section>

    <?php } ?>

    <?php if(!$hasContent){ ?>

    <section class="pf-section">

        <div class="container">

            <div class="empty-state">
                <?php echo icon('layers'); ?>
                <strong>This portfolio is still being built</strong>
                <p>Check back soon to see skills, projects and more.</p>
            </div>

        </div>

    </section>

    <?php } ?>

</main>

<footer class="pf-footer">

    <div class="container">
        Built with <a href="index.php">CareerCraft</a>
    </div>

</footer>

<?php
include "includes/footer.php";
?>
