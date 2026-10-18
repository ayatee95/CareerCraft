<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="theme-color" content="#0a0a0c">

<title><?php echo htmlspecialchars(isset($pageTitle) ? $pageTitle : 'CareerCraft'); ?></title>

<?php if(!empty($metaDescription)){ ?>
<meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
<?php } ?>

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%23d4af37'/%3E%3Ctext x='32' y='44' font-size='36' font-family='Arial' font-weight='700' text-anchor='middle' fill='%2314110a'%3EC%3C/text%3E%3C/svg%3E">

<link rel="stylesheet" href="assets/css/style.css">

<!-- Poppins is bundled locally: see the @font-face rules in assets/css/style.css -->

<script src="assets/js/script.js" defer></script>

</head>

<body class="<?php echo htmlspecialchars(isset($bodyClass) ? $bodyClass : ''); ?>">
