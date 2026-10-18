<?php

include "includes/database.php";

secure_session_start();

$message = "";

// A valid bcrypt hash that is only used to keep the response time similar when the email does not exist.
$dummyHash = '$2y$10$815tn5HiHDW9X7caRmvGBOOxKV/ErKnJzlyf6fhg1716ZQvNGXYci';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])){

    if(!csrf_check()){

        $message = csrf_error_message();

    }else{

        $email = clean_input(isset($_POST['email']) ? $_POST['email'] : '');

        $password = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : '';

        $user = null;

        if($email !== "" && $password !== ""){

            $user = db_one(
                $conn,
                "SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1",
                "s",
                array($email)
            );

        }

        $hash = $user ? $user['password'] : $dummyHash;

        $passwordOk = password_verify($password, (string)$hash);

        if($user && $passwordOk){

            // New session id after login (prevents session fixation)
            session_regenerate_id(true);

            unset($_SESSION['csrf_token']);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['fullname'] = $user['fullname'];

            redirect_to("dashboard.php");

        }

        // Same message for wrong email and wrong password
        $message = "Invalid email or password.";

    }

}

$pageTitle = "Login | CareerCraft";
$bodyClass = "auth";
include "includes/header.php";
?>

<main class="auth-page" id="main">

    <div class="auth-card">

        <a class="auth-brand" href="index.php">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span>CareerCraft</span>
        </a>

        <h1 class="auth-title">Welcome back</h1>

        <p class="auth-sub">Sign in to manage your portfolio.</p>

        <?php if($message !== ""){ ?>
        <?php render_alert('error', $message); ?>
        <?php } ?>

        <form method="POST" class="form">

            <?php echo csrf_field(); ?>

            <div class="field">
                <label class="label" for="email">Email address</label>
                <input class="input" type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
            </div>

            <div class="field">
                <label class="label" for="password">Password</label>
                <input class="input" type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary btn-block">Sign in</button>

        </form>

        <p class="auth-foot">New to CareerCraft? <a href="register.php">Create an account</a></p>

    </div>

</main>

<?php
include "includes/footer.php";
?>
