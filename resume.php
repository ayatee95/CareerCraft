<?php

include "includes/auth.php";

include "includes/database.php";

$user_id = (int)$_SESSION['user_id'];

$error = "";

if(post_too_large()){

    $error = "The uploaded file is too large (maximum 5 MB).";

}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])){

    if(!csrf_check()){

        $error = csrf_error_message();

    }else{

        // The resumes this user already has. They are removed only AFTER the new one is saved.
        $oldResumes = db_all($conn, "SELECT id, file_name FROM resume WHERE user_id = ?", "i", array($user_id));

        $uploadError = "";

        $newName = save_uploaded_pdf(
            isset($_FILES['resume']) ? $_FILES['resume'] : null,
            upload_dir("resumes"),
            $uploadError
        );

        if($newName === null){

            $error = "Please choose a PDF file.";

        }elseif($newName === false){

            $error = $uploadError;

        }else{

            if(!db_write($conn, "INSERT INTO resume (user_id, file_name) VALUES (?, ?)", "is", array($user_id, $newName))){

                // Saving failed: remove the file we just stored. The previous resume stays untouched.
                delete_stored_file("resumes", $newName);

                $error = "The resume could not be saved. Your previous resume was not changed.";

            }else{

                $newId = (int)mysqli_insert_id($conn);

                // The new resume is saved safely. Now (and only now) retire the old one(s).
                foreach($oldResumes as $old){

                    if($newId > 0 && (int)$old['id'] === $newId){
                        continue;
                    }

                    if(db_write($conn, "DELETE FROM resume WHERE id = ? AND user_id = ?", "ii", array((int)$old['id'], $user_id))){

                        // Delete the old file only if no other row still uses it.
                        $stillUsed = db_one($conn, "SELECT id FROM resume WHERE file_name = ? LIMIT 1", "s", array($old['file_name']));

                        if(!$stillUsed){

                            delete_stored_file("resumes", $old['file_name']);

                        }

                    }

                }

                flash_add('success', count($oldResumes) > 0 ? 'Resume replaced successfully.' : 'Resume uploaded successfully.');

                redirect_to("resume.php");

            }

        }

    }

}

// Latest resume

$resume = db_one(
    $conn,
    "SELECT * FROM resume WHERE user_id = ? ORDER BY id DESC LIMIT 1",
    "i",
    array($user_id)
);

// A database row without a file on disk must not be shown as a working resume.
$resumeFileOk = ($resume && stored_file_exists("resumes", $resume['file_name']));

$pageTitle = "Resume | CareerCraft";
$activeNav = "resume";
include "includes/app_header.php";
?>

<header class="page-header">
    <div>
        <h1 class="page-title">Resume</h1>
        <p class="page-subtitle">Upload your CV so visitors can download it from your portfolio.</p>
    </div>
</header>

<?php flash_render(); ?>

<div class="grid-2">

    <section class="card" aria-labelledby="upload-title">

        <h2 class="card-title" id="upload-title">
            <?php echo icon('upload'); ?>
            <?php if($resume){ ?>Replace your CV<?php }else{ ?>Upload your CV<?php } ?>
        </h2>

        <p class="card-sub">PDF only, up to 5 MB. A new upload replaces your current resume.</p>

        <?php render_errors($error !== "" ? array($error) : array()); ?>

        <form method="POST" enctype="multipart/form-data" class="form">

            <?php echo csrf_field(); ?>

            <label class="file-drop">
                <?php echo icon('upload'); ?>
                <strong>Choose a PDF file</strong>
                <span class="hint">or drag it here</span>
                <input type="file" name="resume" accept=".pdf,application/pdf" data-max-mb="5" required>
                <span class="file-chosen" aria-live="polite"></span>
                <span class="file-error" role="alert"></span>
            </label>

            <div class="form-actions">
                <button type="submit" name="upload" class="btn btn-primary"><?php echo icon('upload'); ?> Upload resume</button>
            </div>

        </form>

    </section>

    <section class="card" aria-labelledby="current-title">

        <h2 class="card-title" id="current-title"><?php echo icon('file-text'); ?> Current resume</h2>

        <p class="card-sub">This is the file visitors download from your public portfolio.</p>

        <?php if($resumeFileOk){ ?>

        <div class="file-row">

            <div class="file-icon"><?php echo icon('file-text'); ?></div>

            <div class="file-meta">
                <strong>Resume (PDF)</strong>
                <span><?php echo e($resume['file_name']); ?></span>
            </div>

            <a class="btn btn-secondary btn-sm" href="uploads/resumes/<?php echo e(rawurlencode($resume['file_name'])); ?>" target="_blank" rel="noopener noreferrer">Open <?php echo icon('external-link'); ?></a>

        </div>

        <?php }elseif($resume){ ?>

        <?php render_alert('error', 'Your saved resume file could not be found on the server. Please upload it again.'); ?>

        <?php }else{ ?>

        <div class="empty-state">
            <?php echo icon('file-text'); ?>
            <strong>No resume uploaded</strong>
            <p>Upload a PDF and it will appear here and on your portfolio.</p>
        </div>

        <?php } ?>

    </section>

</div>

<?php
include "includes/app_footer.php";
?>
