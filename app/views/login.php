<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Fuego Andino</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="login">
    <section class="art">
        <div class="brandline"><img src="assets/img/logo.svg" alt="" width="40" height="40"><span>Fuego Andino</span></div>
        <div>
            <h1>Cocina ecuatoriana, de la costa a la Amazonía.</h1>
            <p>Menú por región, cuentas con plan Pro y un panel para administrar platos, sucursales y ventas.</p>
            <div class="collage">
                <img src="assets/img/platos/encebollado.svg" alt="Encebollado">
                <img src="assets/img/platos/hornado-con-llapingachos.svg" alt="Hornado con llapingachos">
                <img src="assets/img/platos/maito-de-tilapia.svg" alt="Maito de tilapia">
            </div>
        </div>
        <small>Ingeniería Web · UDLA</small>
    </section>

    <section class="panel">
        <div class="box">
            <h2>Iniciar sesión</h2>
            <p class="sub">Ingresa con tu cuenta para continuar.</p>

            <?php if (isset($error) || isset($_GET['error'])): ?>
                <div class="alert error"><?= isset($error) ? htmlspecialchars($error) : 'Acceso restringido: inicia sesión como administrador.' ?></div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['nombre']) && !isset($error)): ?>
                <div class="alert ok">
                    Sesión activa como <b><?= htmlspecialchars($_SESSION['nombre']) ?></b>.
                    <a href="index.php?action=<?= ($_SESSION['rol'] ?? '') === 'admin' ? 'dashboard' : 'menu' ?>">Continuar</a>
                    · <a href="index.php?action=logout">Cerrar sesión</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=login">
                <div class="field">
                    <label for="username">Usuario</label>
                    <input class="input" type="text" id="username" name="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Contraseña</label>
                    <input class="input" type="password" id="password" name="password" required>
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
