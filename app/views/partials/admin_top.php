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
    <title><?= htmlspecialchars($pageTitle) ?> · Fuego Andino</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="admin">
    <aside class="sidebar">
        <div class="brand">
            <strong>Fuego Andino</strong>
            <small>Administración</small>
        </div>
        <nav>
            <div class="group">Gestión</div>
            <?php foreach ($nav as $key => [$label, $href]): ?>
                <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
            <div class="group">Sitio</div>
            <a href="index.php?action=menu" target="_blank">Ver menú público</a>
        </nav>
        <div class="user">
            <b><?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador') ?></b>
            <span>Administrador general</span><br>
            <a href="index.php?action=logout">Cerrar sesión</a>
        </div>
    </aside>
    <main class="content">
