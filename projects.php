<?php

include "includes/auth.php";

include "includes/database.php";

$user_id = (int)$_SESSION['user_id'];

$errors = array();

// Editing and deleting need an "id" column. "technologies" is optional (see migrations/phase3_migration.sql).
$canManage = db_has_column($conn, "projects", "id");

$hasTech = db_has_column($conn, "projects", "technologies");

$editProject = null;   // the project being edited (always one of the user's own projects)

$form = array('project_name' => '', 'description' => '', 'technologies' => '', 'project_link' => '');


// Reads and validates the project form. Returns the clean values.

function read_project_form($hasTech, &$errors){

    $data = array(
        'project_name' => clean_input(isset($_POST['project_name']) ? $_POST['project_name'] : ''),
        'description'  => clean_input(isset($_POST['description']) ? $_POST['description'] : ''),
        'technologies' => '',
        'project_link' => clean_input(isset($_POST['project_link']) ? $_POST['project_link'] : '')
    );

    if($hasTech){

        $data['technologies'] = normalize_tags(clean_input(isset($_POST['technologies']) ? $_POST['technologies'] : ''));

    }

    check_length($data['project_name'], "Project name", 1, 100, $errors);

    check_length($data['description'], "Description", 1, 1000, $errors);

    if($hasTech){

        check_length($data['technologies'], "Technologies", 0, 255, $errors);

    }

    $cleanLink = normalize_url($data['project_link']);

    if($cleanLink === false){

        $errors[] = "Project link must be a valid web address (http or https).";

    }elseif(strlen($cleanLink) > 255){

        $errors[] = "Project link must be at most 255 characters.";

    }else{

        $data['project_link'] = $cleanLink;

    }

    return $data;

}


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(!csrf_check()){

        $errors[] = csrf_error_message();

    }elseif(isset($_POST['add_project'])){

        // ---------- Add ----------

        $form = read_project_form($hasTech, $errors);

        if(empty($errors)){

            if($hasTech){

                $saved = db_write(
                    $conn,
                    "INSERT INTO projects (user_id, project_name, description, technologies, project_link) VALUES (?, ?, ?, ?, ?)",
                    "issss",
                    array($user_id, $form['project_name'], $form['description'], $form['technologies'], $form['project_link'])
                );

            }else{

                $saved = db_write(
                    $conn,
                    "INSERT INTO projects (user_id, project_name, description, project_link) VALUES (?, ?, ?, ?)",
                    "isss",
                    array($user_id, $form['project_name'], $form['description'], $form['project_link'])
                );

            }

            if($saved){

                flash_add('success', 'Project added successfully.');

                redirect_to("projects.php");

            }

            $errors[] = "Could not save the project. Please try again.";

        }

    }elseif(isset($_POST['update_project'])){

        // ---------- Edit ----------

        $projectId = positive_int(isset($_POST['project_id']) ? $_POST['project_id'] : '');

        // A user can only open his own projects (ownership is checked inside find_owned_row)
        $editProject = $canManage ? find_owned_row($conn, "projects", $projectId, $user_id) : null;

        if(!$editProject){

            flash_add('error', 'Project not found.');

            redirect_to("projects.php");

        }

        $form = read_project_form($hasTech, $errors);

        if(empty($errors)){

            if($hasTech){

                $saved = db_write(
                    $conn,
                    "UPDATE projects SET project_name = ?, description = ?, technologies = ?, project_link = ? WHERE id = ? AND user_id = ?",
                    "ssssii",
                    array($form['project_name'], $form['description'], $form['technologies'], $form['project_link'], $projectId, $user_id)
                );

            }else{

                $saved = db_write(
                    $conn,
                    "UPDATE projects SET project_name = ?, description = ?, project_link = ? WHERE id = ? AND user_id = ?",
                    "sssii",
                    array($form['project_name'], $form['description'], $form['project_link'], $projectId, $user_id)
                );

            }

            if($saved){

                flash_add('success', 'Project updated successfully.');

                redirect_to("projects.php");

            }

            $errors[] = "Could not update the project. Please try again.";

        }

    }elseif(isset($_POST['delete_project'])){

        // ---------- Delete ----------

        $projectId = positive_int(isset($_POST['project_id']) ? $_POST['project_id'] : '');

        if($canManage && $projectId > 0
            && db_write($conn, "DELETE FROM projects WHERE id = ? AND user_id = ?", "ii", array($projectId, $user_id))
            && $GLOBALS['db_affected_rows'] > 0){

            flash_add('success', 'Project deleted.');

        }else{

            flash_add('error', 'Project not found or could not be deleted.');

        }

        redirect_to("projects.php");

    }

}


// Open the edit form: projects.php?edit=ID  (only for the user's own project)

if($editProject === null && $canManage && isset($_GET['edit'])){

    $projectId = positive_int($_GET['edit']);

    $editProject = find_owned_row($conn, "projects", $projectId, $user_id);

    if(!$editProject){

        flash_add('error', 'Project not found.');

        redirect_to("projects.php");

    }

    $form = array(
        'project_name' => (string)$editProject['project_name'],
        'description'  => (string)$editProject['description'],
        'technologies' => isset($editProject['technologies']) ? (string)$editProject['technologies'] : '',
        'project_link' => isset($editProject['project_link']) ? (string)$editProject['project_link'] : ''
    );

}


// Get Projects

$projects = db_all(
    $conn,
    "SELECT * FROM projects WHERE user_id = ?" . ($canManage ? " ORDER BY id" : ""),
    "i",
    array($user_id)
);

$pageTitle = "Projects | CareerCraft";
$activeNav = "projects";
include "includes/app_header.php";
?>

<header class="page-header">
    <div>
        <h1 class="page-title">Projects</h1>
        <p class="page-subtitle">Show your best work to recruiters and employers.</p>
    </div>
</header>

<?php flash_render(); ?>

<div class="grid-form-list">

    <section class="card" aria-labelledby="project-form-title">

        <h2 class="card-title" id="project-form-title">
            <?php if($editProject){ ?>
            <?php echo icon('edit'); ?> Edit project
            <?php }else{ ?>
            <?php echo icon('plus'); ?> Add a project
            <?php } ?>
        </h2>

        <p class="card-sub">Describe what you built and which technologies you used.</p>

        <?php render_errors($errors); ?>

        <form method="POST" class="form">

            <?php echo csrf_field(); ?>

            <?php if($editProject){ ?>
            <input type="hidden" name="project_id" value="<?php echo (int)$editProject['id']; ?>">
            <?php } ?>

            <div class="field">
                <label class="label" for="project_name">Project name</label>
                <input class="input" type="text" id="project_name" name="project_name" placeholder="Project Name" value="<?php echo e($form['project_name']); ?>" maxlength="100" required>
            </div>

            <div class="field">
                <label class="label" for="description">Description</label>
                <textarea class="textarea" id="description" name="description" placeholder="Project Description" maxlength="1000" required><?php echo e($form['description']); ?></textarea>
            </div>

            <?php if($hasTech){ ?>
            <div class="field">
                <label class="label" for="technologies">Technologies used <span class="optional">(optional)</span></label>
                <input class="input" type="text" id="technologies" name="technologies" placeholder="PHP, MySQL, JavaScript" value="<?php echo e($form['technologies']); ?>" maxlength="255">
                <span class="hint">Separate them with commas.</span>
            </div>
            <?php } ?>

            <div class="field">
                <label class="label" for="project_link">Project link <span class="optional">(optional)</span></label>
                <input class="input" type="text" id="project_link" name="project_link" placeholder="GitHub Link or Website" value="<?php echo e($form['project_link']); ?>" maxlength="255">
            </div>

            <div class="form-actions">
                <?php if($editProject){ ?>
                <button type="submit" name="update_project" class="btn btn-primary">Save changes</button>
                <a class="btn btn-ghost" href="projects.php">Cancel</a>
                <?php }else{ ?>
                <button type="submit" name="add_project" class="btn btn-primary"><?php echo icon('plus'); ?> Add project</button>
                <?php } ?>
            </div>

        </form>

    </section>

    <section class="card" aria-labelledby="project-list-title">

        <div class="card-head">
            <h2 class="card-title" id="project-list-title"><?php echo icon('briefcase'); ?> My projects</h2>
            <span class="count-pill" aria-label="<?php echo count($projects); ?> projects"><?php echo count($projects); ?></span>
        </div>

        <?php if(!$canManage){ ?>
        <?php render_alert('info', 'Editing and deleting projects is not available yet: the projects table needs an "id" column. Please run the migration script (migrations/phase3_migration.sql).'); ?>
        <?php } ?>

        <?php if(empty($projects)){ ?>

        <div class="empty-state">
            <?php echo icon('briefcase'); ?>
            <strong>No projects yet</strong>
            <p>Add a project and it will appear on your public portfolio.</p>
        </div>

        <?php }else{ ?>

        <div class="project-grid">

            <?php foreach($projects as $row){ ?>

            <article class="project-card">

                <h3><?php echo e($row['project_name']); ?></h3>

                <p><?php echo e($row['description']); ?></p>

                <?php if($hasTech && !empty($row['technologies'])){ ?>
                <div class="chip-list">
                    <?php foreach(tech_list($row['technologies']) as $tech){ ?>
                    <span class="chip"><?php echo e($tech); ?></span>
                    <?php } ?>
                </div>
                <?php } ?>

                <?php $link = safe_url($row['project_link']); ?>

                <?php if($link !== ""){ ?>
                <div class="project-links">
                    <a class="btn btn-secondary btn-sm" href="<?php echo e($link); ?>" target="_blank" rel="noopener noreferrer">View project <?php echo icon('external-link'); ?></a>
                </div>
                <?php } ?>

                <?php if($canManage && isset($row['id'])){ ?>

                <div class="project-actions">

                    <a class="btn btn-ghost btn-sm" href="projects.php?edit=<?php echo (int)$row['id']; ?>"><?php echo icon('edit'); ?> Edit</a>

                    <form method="POST" data-confirm="Delete this project? This cannot be undone.">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="project_id" value="<?php echo (int)$row['id']; ?>">
                        <button type="submit" name="delete_project" class="btn btn-danger btn-sm" aria-label="Delete <?php echo e($row['project_name']); ?>"><?php echo icon('trash'); ?> Delete</button>
                    </form>

                </div>

                <?php } ?>

            </article>

            <?php } ?>

        </div>

        <?php } ?>

    </section>

</div>

<?php
include "includes/app_footer.php";
?>
