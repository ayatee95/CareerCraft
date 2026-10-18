<?php

include "includes/auth.php";

include "includes/database.php";

$user_id = (int)$_SESSION['user_id'];

$errors = array();

// Editing and deleting need an "id" column in the skills table (see migrations/phase3_migration.sql).
$canManage = db_has_column($conn, "skills", "id");

$editSkill = null;     // the skill being edited (always one of the user's own skills)

$skillValue = "";      // text shown in the input box


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(!csrf_check()){

        $errors[] = csrf_error_message();

    }elseif(isset($_POST['add_skill'])){

        // ---------- Add ----------

        $skillValue = clean_input(isset($_POST['skill_name']) ? $_POST['skill_name'] : '');

        check_length($skillValue, "Skill name", 1, 100, $errors);

        if(empty($errors)){

            $duplicate = db_one(
                $conn,
                "SELECT skill_name FROM skills WHERE user_id = ? AND LOWER(skill_name) = LOWER(?) LIMIT 1",
                "is",
                array($user_id, $skillValue)
            );

            if($duplicate){

                $errors[] = "You already added this skill.";

            }

        }

        if(empty($errors)){

            if(db_write($conn, "INSERT INTO skills (user_id, skill_name) VALUES (?, ?)", "is", array($user_id, $skillValue))){

                flash_add('success', 'Skill added successfully.');

                redirect_to("skills.php");

            }

            $errors[] = "Could not save the skill. Please try again.";

        }

    }elseif(isset($_POST['update_skill'])){

        // ---------- Edit ----------

        $skillId = positive_int(isset($_POST['skill_id']) ? $_POST['skill_id'] : '');

        // A user can only open his own skills (ownership is checked inside find_owned_row)
        $editSkill = $canManage ? find_owned_row($conn, "skills", $skillId, $user_id) : null;

        if(!$editSkill){

            flash_add('error', 'Skill not found.');

            redirect_to("skills.php");

        }

        $skillValue = clean_input(isset($_POST['skill_name']) ? $_POST['skill_name'] : '');

        check_length($skillValue, "Skill name", 1, 100, $errors);

        if(empty($errors)){

            $duplicate = db_one(
                $conn,
                "SELECT id FROM skills WHERE user_id = ? AND LOWER(skill_name) = LOWER(?) AND id <> ? LIMIT 1",
                "isi",
                array($user_id, $skillValue, $skillId)
            );

            if($duplicate){

                $errors[] = "You already added this skill.";

            }

        }

        if(empty($errors)){

            if(db_write($conn, "UPDATE skills SET skill_name = ? WHERE id = ? AND user_id = ?", "sii", array($skillValue, $skillId, $user_id))){

                flash_add('success', 'Skill updated successfully.');

                redirect_to("skills.php");

            }

            $errors[] = "Could not update the skill. Please try again.";

        }

    }elseif(isset($_POST['delete_skill'])){

        // ---------- Delete ----------

        $skillId = positive_int(isset($_POST['skill_id']) ? $_POST['skill_id'] : '');

        if($canManage && $skillId > 0
            && db_write($conn, "DELETE FROM skills WHERE id = ? AND user_id = ?", "ii", array($skillId, $user_id))
            && $GLOBALS['db_affected_rows'] > 0){

            flash_add('success', 'Skill deleted.');

        }else{

            flash_add('error', 'Skill not found or could not be deleted.');

        }

        redirect_to("skills.php");

    }

}


// Open the edit form: skills.php?edit=ID  (only for the user's own skill)

if($editSkill === null && $canManage && isset($_GET['edit'])){

    $skillId = positive_int($_GET['edit']);

    $editSkill = find_owned_row($conn, "skills", $skillId, $user_id);

    if(!$editSkill){

        flash_add('error', 'Skill not found.');

        redirect_to("skills.php");

    }

    $skillValue = $editSkill['skill_name'];

}


// Get Skills

$skills = db_all(
    $conn,
    "SELECT * FROM skills WHERE user_id = ?" . ($canManage ? " ORDER BY id" : ""),
    "i",
    array($user_id)
);

$pageTitle = "Skills | CareerCraft";
$activeNav = "skills";
include "includes/app_header.php";
?>

<header class="page-header">
    <div>
        <h1 class="page-title">Skills</h1>
        <p class="page-subtitle">Show the technologies and tools you work with.</p>
    </div>
</header>

<?php flash_render(); ?>

<div class="grid-form-list">

    <section class="card" aria-labelledby="skill-form-title">

        <h2 class="card-title" id="skill-form-title">
            <?php if($editSkill){ ?>
            <?php echo icon('edit'); ?> Edit skill
            <?php }else{ ?>
            <?php echo icon('plus'); ?> Add a skill
            <?php } ?>
        </h2>

        <p class="card-sub">One skill at a time, for example PHP, MySQL or Photoshop.</p>

        <?php render_errors($errors); ?>

        <form method="POST" class="form">

            <?php echo csrf_field(); ?>

            <?php if($editSkill){ ?>
            <input type="hidden" name="skill_id" value="<?php echo (int)$editSkill['id']; ?>">
            <?php } ?>

            <div class="field">
                <label class="label" for="skill_name">Skill name</label>
                <input class="input" type="text" id="skill_name" name="skill_name" placeholder="Example: PHP" value="<?php echo e($skillValue); ?>" maxlength="100" required>
            </div>

            <div class="form-actions">
                <?php if($editSkill){ ?>
                <button type="submit" name="update_skill" class="btn btn-primary">Save changes</button>
                <a class="btn btn-ghost" href="skills.php">Cancel</a>
                <?php }else{ ?>
                <button type="submit" name="add_skill" class="btn btn-primary"><?php echo icon('plus'); ?> Add skill</button>
                <?php } ?>
            </div>

        </form>

    </section>

    <section class="card" aria-labelledby="skill-list-title">

        <div class="card-head">
            <h2 class="card-title" id="skill-list-title"><?php echo icon('code'); ?> My skills</h2>
            <span class="count-pill" aria-label="<?php echo count($skills); ?> skills"><?php echo count($skills); ?></span>
        </div>

        <?php if(!$canManage){ ?>
        <?php render_alert('info', 'Editing and deleting skills is not available yet: the skills table needs an "id" column. Please run the migration script (migrations/phase3_migration.sql).'); ?>
        <?php } ?>

        <?php if(empty($skills)){ ?>

        <div class="empty-state">
            <?php echo icon('code'); ?>
            <strong>No skills yet</strong>
            <p>Add your first skill and it will appear on your public portfolio.</p>
        </div>

        <?php }else{ ?>

        <div class="item-list">

            <?php foreach($skills as $row){ ?>

            <div class="item">

                <div class="item-main">
                    <?php echo icon('check'); ?>
                    <span class="item-name"><?php echo e($row['skill_name']); ?></span>
                </div>

                <?php if($canManage && isset($row['id'])){ ?>

                <div class="item-actions">

                    <a class="btn btn-ghost btn-sm" href="skills.php?edit=<?php echo (int)$row['id']; ?>"><?php echo icon('edit'); ?> Edit</a>

                    <form method="POST" data-confirm="Delete this skill? This cannot be undone.">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="skill_id" value="<?php echo (int)$row['id']; ?>">
                        <button type="submit" name="delete_skill" class="btn btn-danger btn-sm" aria-label="Delete <?php echo e($row['skill_name']); ?>"><?php echo icon('trash'); ?> Delete</button>
                    </form>

                </div>

                <?php } ?>

            </div>

            <?php } ?>

        </div>

        <?php } ?>

    </section>

</div>

<?php
include "includes/app_footer.php";
?>
