<?php
// Layout del panel de administración. Variables esperadas: $pageTitle, $active
$nav = [
    'dashboard' => ['Dashboard', 'index.php?action=dashboard'],
    'platos'    => ['Catálogo de platos', 'index.php?action=platos'],
    'sucursales' => ['Sucursales', 'index.php?action=sucursales'],
];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · Fuego Andino</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,700&family=Inter:wght@400;500;600&family=Unbounded:wght@500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-body">
<div class="admin">
    <aside class="sidebar">
        <div class="brand">
            <img src="assets/img/logo.svg" alt="" width="34" height="34">
            <div><strong>Fuego Andino</strong>
            <small>Administración</small></div>
        </div>
        <nav>
            <div class="group">Gestión</div>
            <?php foreach ($nav as $key => [$label, $href]): ?>
                <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
            <div class="group">Sitio</div>
            <a href="index.php?action=inicio" target="_blank">Ver el sitio público</a>
        </nav>
        <div class="user">
            <b><?= e($_SESSION['nombre'] ?? 'Administrador') ?></b>
            <span>Administrador general</span><br>
            <a href="index.php?action=logout">Cerrar sesión</a>
        </div>
    </aside>
    <main class="content">
