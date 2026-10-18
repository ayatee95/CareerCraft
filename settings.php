<?php

include "includes/auth.php";

include "includes/database.php";

$user_id = (int)$_SESSION['user_id'];

// Get User Data

$user = db_one($conn, "SELECT * FROM users WHERE id = ? LIMIT 1", "i", array($user_id));

if(!$user){

    // The account no longer exists: end the session.
    end_session();

    redirect_to("login.php");

}

$nameErrors = array();
$passwordErrors = array();
$passwordMessage = "";
$fullnameValue = $user['fullname'];

// Update Settings

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])){

    if(!csrf_check()){

        $nameErrors[] = csrf_error_message();

    }else{

        $fullname = clean_input(isset($_POST['fullname']) ? $_POST['fullname'] : '');

        $fullnameValue = $fullname;

        check_length($fullname, "Full name", 2, 100, $nameErrors);

        if(empty($nameErrors)){

            if(db_write($conn, "UPDATE users SET fullname = ? WHERE id = ?", "si", array($fullname, $user_id))){

                $_SESSION['fullname'] = $fullname;

                flash_add('success', 'Name updated successfully.');

                redirect_to("settings.php");

            }

            $nameErrors[] = "Could not save the name. Please try again.";

        }

    }

}

// Change Password

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])){

    if(!csrf_check()){

        $passwordErrors[] = csrf_error_message();

    }else{

        $currentPassword = (isset($_POST['current_password']) && is_string($_POST['current_password'])) ? $_POST['current_password'] : '';

        $newPassword = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : '';

        if($currentPassword === "" || !password_verify($currentPassword, (string)$user['password'])){

            $passwordErrors[] = "Current password is incorrect.";

        }

        if(strlen($newPassword) < 8){

            $passwordErrors[] = "New password must be at least 8 characters.";

        }elseif(strlen($newPassword) > 72){

            $passwordErrors[] = "New password must be at most 72 characters.";

        }elseif($newPassword === $currentPassword){

            $passwordErrors[] = "New password must be different from the current password.";

        }

        if(empty($passwordErrors)){

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            if(db_write($conn, "UPDATE users SET password = ? WHERE id = ?", "si", array($hashedPassword, $user_id))){

                session_regenerate_id(true);

                $passwordMessage = "Password changed successfully";

            }else{

                $passwordErrors[] = "Could not change the password. Please try again.";

            }

        }

    }

}

$pageTitle = "Settings | CareerCraft";
$activeNav = "settings";
include "includes/app_header.php";
?>

<header class="page-header">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage your account name and password.</p>
    </div>
</header>

<?php flash_render(); ?>

<div class="grid-2">

    <section class="card" aria-labelledby="name-title">

        <h2 class="card-title" id="name-title"><?php echo icon('user'); ?> Display name</h2>

        <p class="card-sub">This name is shown on your dashboard and public portfolio.</p>

        <?php render_errors($nameErrors); ?>

        <form method="POST" class="form">

            <?php echo csrf_field(); ?>

            <div class="field">
                <label class="label" for="fullname">Full name</label>
                <input class="input" type="text" id="fullname" name="fullname" value="<?php echo e($fullnameValue); ?>" maxlength="100" autocomplete="name" required>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">Save name</button>
            </div>

        </form>

    </section>

    <section class="card" aria-labelledby="password-title">

        <h2 class="card-title" id="password-title"><?php echo icon('settings'); ?> Change password</h2>

        <p class="card-sub">Use at least 8 characters. You will stay signed in.</p>

        <?php if($passwordMessage != ""){ ?>
        <?php render_alert('success', $passwordMessage); ?>
        <?php } ?>

        <?php render_errors($passwordErrors); ?>

        <form method="POST" class="form">

            <?php echo csrf_field(); ?>

            <div class="field">
                <label class="label" for="current_password">Current password</label>
                <input class="input" type="password" id="current_password" name="current_password" placeholder="Current Password" autocomplete="current-password" required>
            </div>

            <div class="field">
                <label class="label" for="password">New password</label>
                <input class="input" type="password" id="password" name="password" placeholder="New Password" minlength="8" maxlength="72" autocomplete="new-password" required>
                <span class="hint">8 to 72 characters.</span>
            </div>

            <div class="form-actions">
                <button type="submit" name="change_password" class="btn btn-primary">Change password</button>
            </div>

        </form>

    </section>

</div>

<?php
include "includes/app_footer.php";
?>
