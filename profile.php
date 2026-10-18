<?php

include "includes/auth.php";

include "includes/database.php";

$user_id = (int)$_SESSION['user_id'];

$errors = array();

if(post_too_large()){

    $errors[] = "The uploaded file is too large.";

}

$user = db_one($conn, "SELECT * FROM users WHERE id = ? LIMIT 1", "i", array($user_id));

$profile = db_one($conn, "SELECT * FROM profiles WHERE user_id = ? LIMIT 1", "i", array($user_id));

// Values shown in the form (kept when validation fails)
$form = array(
    'job_title' => isset($profile['job_title']) ? $profile['job_title'] : '',
    'phone'     => isset($profile['phone']) ? $profile['phone'] : '',
    'bio'       => isset($profile['bio']) ? $profile['bio'] : ''
);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])){

    if(!csrf_check()){

        $errors[] = csrf_error_message();

    }else{

        $job_title = clean_input(isset($_POST['job_title']) ? $_POST['job_title'] : '');

        $phone = clean_input(isset($_POST['phone']) ? $_POST['phone'] : '');

        $bio = clean_input(isset($_POST['bio']) ? $_POST['bio'] : '');

        $form = array('job_title' => $job_title, 'phone' => $phone, 'bio' => $bio);

        check_length($job_title, "Job title", 0, 100, $errors);

        if($phone !== "" && preg_match('/^[\p{Nd}+\-\s()]{5,20}$/u', $phone) !== 1){

            $errors[] = "Phone number can contain digits, spaces and + - ( ) only (5 to 20 characters).";

        }

        check_length($bio, "Bio", 0, 2000, $errors);

        $newImage = null;

        if(empty($errors)){

            $uploadError = "";

            $newImage = save_uploaded_image(
                isset($_FILES['profile_image']) ? $_FILES['profile_image'] : null,
                __DIR__ . "/uploads/profiles",
                $uploadError
            );

            if($newImage === false){

                $errors[] = $uploadError;

            }

        }

        if(empty($errors)){

            if($profile){

                $saved = db_write(
                    $conn,
                    "UPDATE profiles SET bio = ?, phone = ?, job_title = ? WHERE user_id = ?",
                    "sssi",
                    array($bio, $phone, $job_title, $user_id)
                );

            }else{

                $saved = db_write(
                    $conn,
                    "INSERT INTO profiles (user_id, bio, phone, job_title) VALUES (?, ?, ?, ?)",
                    "isss",
                    array($user_id, $bio, $phone, $job_title)
                );

            }

            if(!$saved){

                $errors[] = "Could not save your profile. Please try again.";

            }elseif($newImage){

                if(!db_write($conn, "UPDATE users SET profile_image = ? WHERE id = ?", "si", array($newImage, $user_id))){

                    $errors[] = "Your profile was saved, but the picture could not be updated.";

                }

            }

            if(empty($errors)){

                flash_add('success', 'Profile saved successfully.');

                redirect_to("profile.php");

            }

        }

    }

}

$pageTitle = "Profile | CareerCraft";
$activeNav = "profile";
include "includes/app_header.php";
?>

<header class="page-header">
    <div>
        <h1 class="page-title">Profile</h1>
        <p class="page-subtitle">This information appears at the top of your public portfolio.</p>
    </div>
</header>

<?php flash_render(); ?>

<?php render_errors($errors); ?>

<?php $hasImage = (!empty($user['profile_image']) && stored_file_exists('profiles', $user['profile_image'])); ?>

<form method="POST" enctype="multipart/form-data" class="form">

    <?php echo csrf_field(); ?>

    <div class="grid-form-list">

        <section class="card" aria-labelledby="picture-title">

            <h2 class="card-title" id="picture-title"><?php echo icon('image'); ?> Profile picture</h2>

            <p class="card-sub">JPG, PNG, GIF or WEBP, up to 2 MB.</p>

            <div class="avatar-edit">

                <div class="avatar-preview" id="avatar-preview">
                    <?php if($hasImage){ ?>
                    <img src="uploads/profiles/<?php echo e(rawurlencode($user['profile_image'])); ?>" alt="Your current profile picture" width="112" height="112" style="width:100%;height:100%;object-fit:cover;">
                    <?php }else{ ?>
                    <?php echo icon('user'); ?>
                    <?php } ?>
                </div>

                <label class="file-drop">
                    <?php echo icon('upload'); ?>
                    <strong>Choose a picture</strong>
                    <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" data-max-mb="2" data-preview="#avatar-preview">
                    <span class="file-chosen" aria-live="polite"></span>
                    <span class="file-error" role="alert"></span>
                </label>

            </div>

        </section>

        <section class="card" aria-labelledby="details-title">

            <h2 class="card-title" id="details-title"><?php echo icon('user'); ?> About you</h2>

            <p class="card-sub">A clear title and a short bio help recruiters understand you quickly.</p>

            <div class="form">

                <div class="field">
                    <label class="label" for="job_title">Job title <span class="optional">(optional)</span></label>
                    <input class="input" type="text" id="job_title" name="job_title" placeholder="Example: Junior Web Developer" value="<?php echo e($form['job_title']); ?>" maxlength="100">
                </div>

                <div class="field">
                    <label class="label" for="phone">Phone number <span class="optional">(optional)</span></label>
                    <input class="input" type="text" id="phone" name="phone" inputmode="tel" placeholder="Phone Number" value="<?php echo e($form['phone']); ?>" maxlength="20">
                    <span class="hint">Private: your phone number is not shown on your public portfolio.</span>
                </div>

                <div class="field">
                    <label class="label" for="bio">Bio <span class="optional">(optional)</span></label>
                    <textarea class="textarea" id="bio" name="bio" placeholder="Write about yourself" maxlength="2000"><?php echo e($form['bio']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save" class="btn btn-primary"><?php echo icon('check'); ?> Save profile</button>
                </div>

            </div>

        </section>

    </div>

</form>

<?php
include "includes/app_footer.php";
?>
