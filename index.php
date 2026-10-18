<?php

include "includes/functions.php";

$pageTitle = "CareerCraft | Build Your Portfolio";
$metaDescription = "Build your personal brand and showcase your skills, projects and resume in one professional online portfolio.";
$bodyClass = "site";

include "includes/header.php";

?>

<a class="skip-link" href="#main">Skip to main content</a>

<header class="site-header">

    <div class="container">

        <a class="brand" href="index.php">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-text">CareerCraft</span>
        </a>

        <nav class="site-nav" aria-label="Main navigation">
            <a href="index.php">Home</a>
            <a class="nav-cta" href="login.php">Login</a>
            <a class="btn btn-primary btn-sm" href="register.php">Register</a>
        </nav>

    </div>

</header>

<main id="main">

    <section class="hero">

        <div class="container hero-grid">

            <div>

                <span class="eyebrow"><?php echo icon('layers'); ?> Portfolio builder</span>

                <h1>Create your professional portfolio <span class="accent">easily</span></h1>

                <p class="lead">Build your personal brand and showcase your skills, projects and experience in one professional place.</p>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="register.php">Get started <?php echo icon('arrow-right'); ?></a>
                    <a class="btn btn-ghost" href="login.php">I already have an account</a>
                </div>

            </div>

            <div class="preview-card" aria-hidden="true">

                <div class="preview-top">
                    <div class="preview-avatar"><?php echo icon('user'); ?></div>
                    <div class="preview-lines">
                        <div class="skeleton w-60"></div>
                        <div class="skeleton gold w-40"></div>
                    </div>
                </div>

                <div class="preview-section">
                    <span class="eyebrow">Skills</span>
                    <div class="chip-list">
                        <span class="chip">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="chip">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="chip">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    </div>
                </div>

                <div class="preview-section">
                    <span class="eyebrow">Projects</span>
                    <div class="skeleton w-80"></div>
                    <div class="skeleton w-60" style="margin-top:8px"></div>
                </div>

                <p class="preview-caption">Your Future Portfolio</p>

            </div>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="section-head">
                <h2>Why CareerCraft?</h2>
                <p>Everything you need to present yourself professionally online.</p>
            </div>

            <div class="feature-grid">

                <div class="feature-card">
                    <div class="feature-icon"><?php echo icon('user'); ?></div>
                    <h3>Profile</h3>
                    <p>Create your professional profile.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><?php echo icon('briefcase'); ?></div>
                    <h3>Projects</h3>
                    <p>Showcase your best work.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><?php echo icon('code'); ?></div>
                    <h3>Skills</h3>
                    <p>Highlight your technical skills.</p>
                </div>

            </div>

        </div>

    </section>

    <section class="section" style="padding-top:0">

        <div class="container">

            <div class="section-head">
                <h2>How it works</h2>
                <p>From sign-up to a shareable portfolio in three simple steps.</p>
            </div>

            <div class="steps">

                <div class="step">
                    <h3>Create your account</h3>
                    <p>Register with your email and sign in.</p>
                </div>

                <div class="step">
                    <h3>Add your information</h3>
                    <p>Fill in your profile, skills, projects and upload your resume.</p>
                </div>

                <div class="step">
                    <h3>Share your portfolio</h3>
                    <p>Send your personal portfolio link to recruiters and employers.</p>
                </div>

            </div>

        </div>

    </section>

    <div class="container">

        <div class="cta-band">
            <h2>Ready to build yours?</h2>
            <p>Create your CareerCraft portfolio today.</p>
            <a class="btn btn-primary" href="register.php">Get started <?php echo icon('arrow-right'); ?></a>
        </div>

    </div>

</main>

<footer class="site-footer">

    <div class="container">
        <span>CareerCraft &copy; <?php echo date('Y'); ?></span>
        <span>Portfolio Builder</span>
    </div>

</footer>

<?php
include "includes/footer.php";
?>
