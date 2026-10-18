<?php

include "includes/database.php";

secure_session_start();

$errors = array();
$success = "";
$old_fullname = "";
$old_email = "";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])){

    if(!csrf_check()){

        $errors[] = csrf_error_message();

    }else{

        $fullname = clean_input(isset($_POST['fullname']) ? $_POST['fullname'] : '');

        $email = to_lower(clean_input(isset($_POST['email']) ? $_POST['email'] : ''));

        $password = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : '';

        $old_fullname = $fullname;
        $old_email = $email;

        check_length($fullname, "Full name", 2, 100, $errors);

        if($email === ""){

            $errors[] = "Email is required.";

        }elseif(strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false){

            $errors[] = "Please enter a valid email address.";

        }

        if(strlen($password) < 8){

            $errors[] = "Password must be at least 8 characters.";

        }elseif(strlen($password) > 72){

            $errors[] = "Password must be at most 72 characters.";

        }

        if(empty($errors)){

            $exists = db_one(
                $conn,
                "SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1",
                "s",
                array($email)
            );

            if($exists){

                $errors[] = "This email is already registered.";

            }else{

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $saved = db_write(
                    $conn,
                    "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)",
                    "sss",
                    array($fullname, $email, $hash)
                );

                if($saved){

                    $success = "Account created successfully!";

                    $old_fullname = "";
                    $old_email = "";

                }elseif($GLOBALS['db_last_errno'] == 1062){

                    $errors[] = "This email is already registered.";

                }else{

                    $errors[] = "Could not create the account. Please try again.";

                }

            }

        }

    }

}

?>

<?php
$pageTitle = "Register | CareerCraft";
$bodyClass = "auth";
include "includes/header.php";
?>

<main class="auth-page" id="main">

    <div class="auth-card">

        <a class="auth-brand" href="index.php">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span>CareerCraft</span>
        </a>

        <h1 class="auth-title">Create your account</h1>

        <p class="auth-sub">Build a professional portfolio in minutes.</p>

        <?php if($success !== ""){ ?>
        <?php render_alert('success', $success); ?>
        <p><a class="btn btn-secondary btn-block" href="login.php">Go to login</a></p>
        <?php } ?>

        <?php render_errors($errors); ?>

        <form method="POST" class="form">

            <?php echo csrf_field(); ?>

            <div class="field">
                <label class="label" for="fullname">Full name</label>
                <input class="input" type="text" id="fullname" name="fullname" placeholder="Full Name" value="<?php echo e($old_fullname); ?>" maxlength="100" autocomplete="name" required>
            </div>

            <div class="field">
                <label class="label" for="email">Email address</label>
                <input class="input" type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo e($old_email); ?>" maxlength="100" autocomplete="email" required>
            </div>

            <div class="field">
                <label class="label" for="password">Password</label>
                <input class="input" type="password" id="password" name="password" placeholder="At least 8 characters" minlength="8" maxlength="72" autocomplete="new-password" required>
                <span class="hint">8 to 72 characters.</span>
            </div>

            <button type="submit" name="register" class="btn btn-primary btn-block">Create account</button>

        </form>

        <p class="auth-foot">Already have an account? <a href="login.php">Sign in</a></p>

    </div>

</main>

<?php
include "includes/footer.php";
?>
