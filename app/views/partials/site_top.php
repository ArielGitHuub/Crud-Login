<?php
// Encabezado del sitio público. Variables: $pageTitle, $pagina ('inicio'|'menu'|'nosotros'), $ghosts (opcional)
$rol = $_SESSION['rol'] ?? 'invitado';
$logueado = !empty($_SESSION['user_id']);
$plan = $_SESSION['plan'] ?? 'free';
$enlaces = ['inicio' => 'Inicio', 'menu' => 'Menú', 'nosotros' => 'Nosotros'];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · Fuego Andino</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600;1,700;1,800;1,900&family=Inter:wght@400;500;600&family=Unbounded:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="site">
<?php if (!empty($ghosts)): ?>
    <div class="ghosts" aria-hidden="true">
        <?php foreach (array_slice($ghosts, 0, 4) as $g): ?><img src="<?= e($g) ?>" alt=""><?php endforeach; ?>
    </div>
<?php endif; ?>

<header class="site-nav" id="siteNav">
    <div class="in">
        <a class="logo" href="index.php?action=inicio"><img src="assets/img/logo.svg" alt="">Fuego Andino</a>
        <button class="nav-toggle" id="navToggle" type="button" aria-label="Abrir menú" aria-expanded="false">☰</button>
        <nav class="nav-links">
            <?php foreach ($enlaces as $clave => $texto): ?>
                <a href="index.php?action=<?= $clave ?>" class="<?= $pagina === $clave ? 'active' : '' ?>"><?= $texto ?></a>
            <?php endforeach; ?>
            <?php if ($rol === 'admin'): ?><a href="index.php?action=dashboard">Administración</a><?php endif; ?>
        </nav>
        <div class="nav-user">
            <span class="plan-chip <?= $plan === 'pro' ? 'pro' : '' ?>"><?= $logueado ? ($plan === 'pro' ? 'Plan Pro' : 'Plan Free') : 'Invitado' ?></span>
            <b><?= e($_SESSION['nombre'] ?? 'Visitante') ?></b>
            <?php if ($logueado): ?>
                <a href="index.php?action=logout">Salir</a>
            <?php else: ?>
                <a href="index.php?action=login">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<div class="page">
