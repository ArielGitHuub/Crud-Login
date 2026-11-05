<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Fuego Andino</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600;1,700;1,800;1,900&family=Inter:wght@400;500;600&family=Unbounded:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="site">
<div class="login">
    <section class="art">
        <a class="logo" href="index.php?action=inicio"><img src="assets/img/logo.svg" alt="">Fuego Andino</a>
        <div>
            <h1>Sabores de la Costa, la Sierra y la Amazonía.</h1>
            <p>Pide tus platos tradicionales, activa el plan Pro y, si eres administrador, gestiona el menú, las sucursales y las ventas.</p>
            <div class="collage">
                <img src="assets/img/platos/encebollado.jpg" alt="Encebollado">
                <img src="assets/img/platos/hornado.jpg" alt="Hornado con llapingachos">
                <img src="assets/img/platos/maito-de-tilapia.jpg" alt="Maito de tilapia">
            </div>
        </div>
        <small>Ingeniería Web · UDLA</small>
    </section>

    <section class="panel">
        <div class="box">
            <h2>Iniciar sesión</h2>
            <p class="sub">Ingresa con tu cuenta para continuar.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert error">Acceso restringido: inicia sesión como administrador.</div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <div class="alert ok">
                    Sesión activa como <b><?= e($_SESSION['nombre']) ?></b>.
                    <a href="index.php?action=<?= es_admin() ? 'dashboard' : 'inicio' ?>">Continuar</a> ·
                    <a href="index.php?action=logout">Cerrar sesión</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=login">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="username">Usuario</label>
                    <input class="input" type="text" id="username" name="username" required autofocus autocomplete="username">
                </div>
                <div class="field">
                    <label for="password">Contraseña</label>
                    <input class="input" type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn primary">Entrar</button>
            </form>

            <div class="demo">
                <p>Acceso rápido para la demostración</p>
                <div class="opts">
                    <a class="btn small" href="index.php?action=switch_user&role=admin">Administrador</a>
                    <a class="btn small" href="index.php?action=switch_user&role=pro">Cliente Pro</a>
                    <a class="btn small" href="index.php?action=switch_user&role=free">Cliente Free</a>
                    <a class="btn small" href="index.php?action=switch_user&role=invitado">Invitado</a>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
</html>
