<?php
$regionClass = fn($r) => strtolower(strtr($r, ['í' => 'i']));
$regiones = [
    '' => 'Todas',
    'Costa' => 'Costa',
    'Sierra' => 'Sierra',
    'Amazonía' => 'Amazonía',
];
$q = fn(array $extra) => 'index.php?' . http_build_query(array_filter(['action' => 'menu', 'sucursal_id' => $sucursalId] + $extra));
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú por región · Fuego Andino</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>

<header class="site-nav">
    <div class="in">
        <a class="logo" href="index.php?action=menu">Fuego <em>Andino</em></a>
        <nav class="links">
            <a class="active" href="index.php?action=menu">Menú</a>
            <?php if (($userRol ?? '') === 'admin'): ?>
                <a href="index.php?action=dashboard">Panel de administración</a>
            <?php endif; ?>
        </nav>
        <div class="who">
            <span class="plan-chip <?= $isPro ? 'pro' : '' ?>"><?= $isPro ? 'Plan Pro' : 'Plan Free' ?></span>
            &nbsp;<b><?= htmlspecialchars($_SESSION['nombre'] ?? 'Invitado') ?></b>
            &nbsp;·&nbsp;<a href="index.php?action=login">Cambiar de cuenta</a>
        </div>
    </div>
</header>

<section class="hero">
    <div class="in">
        <h1>Cocina ecuatoriana, de la costa a la Amazonía.</h1>
        <p>Platos tradicionales de cada región, preparados en nuestras sucursales. Elige tu local y pide.</p>
    </div>
</section>

<div class="wrap">

    <?php if ($mensajePedido): ?>
        <div class="alert ok">
            <?= htmlspecialchars($mensajePedido['texto']) ?>
            <?php if (($userRol ?? '') === 'admin'): ?> <a href="index.php?action=dashboard">Ver en el dashboard</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="benefit">
        <div>
            <b><?= $isPro ? 'Beneficios Pro activos' : 'Plan Pro: descuento y envío gratis' ?></b>
            <span>
                <?php if ($isPro): ?>
                    Descuento en platos seleccionados y envío gratis en todos tus pedidos.
                <?php else: ?>
                    Pagas precio regular y $2.50 de envío. <a href="index.php?action=switch_user&role=pro">Activar plan Pro (simulado)</a>
                <?php endif; ?>
            </span>
        </div>
        <b><?= $isPro ? 'Envío $0.00' : 'Envío $2.50' ?></b>
    </div>

    <div class="toolbar">
        <nav class="tabs">
            <?php foreach ($regiones as $valor => $etiqueta): ?>
                <a class="<?= ((string)$region === (string)$valor) ? 'active' : '' ?>" href="<?= $q($valor ? ['region' => $valor] : []) ?>">
                    <?= $etiqueta ?><?php if ($valor): ?><small><?= $conteoRegiones[$valor] ?? 0 ?></small><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="field" style="margin:0;min-width:240px">
            <select class="select" aria-label="Sucursal"
                    onchange="window.location.href='index.php?action=menu<?= $region ? '&region=' . urlencode($region) : '' ?>&sucursal_id=' + this.value">
                <?php foreach ($sucursales as $suc): ?>
                    <option value="<?= $suc['id'] ?>" <?= ($sucursalId == $suc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($suc['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (!$platos): ?>
        <div class="empty">No hay platos disponibles en esta región por ahora.</div>
    <?php else: ?>
    <div class="dishes">
        <?php foreach ($platos as $pl):
            $precioRegular = (float)$pl['precio'];
            $descuento = (float)$pl['descuento_pro'];
            $precioPro = $precioRegular * (1 - $descuento / 100);
        ?>
            <article class="dish <?= $regionClass($pl['region']) ?>">
                <div class="stripe"></div>
                <div class="body">
                    <div class="origin"><?= htmlspecialchars($pl['region']) ?><?= !empty($pl['provincia']) ? ' · ' . htmlspecialchars($pl['provincia']) : '' ?></div>
                    <h3><?= htmlspecialchars($pl['nombre']) ?></h3>
                    <p class="desc"><?= htmlspecialchars($pl['descripcion']) ?></p>
                    <p class="ing"><?= htmlspecialchars(implode(', ', array_slice(array_map('trim', explode(',', $pl['ingredientes'])), 0, 4))) ?></p>
                </div>
                <div class="foot">
                    <div class="price-row">
                        <div class="price">
                            <?php if ($isPro): ?>
                                <s>$<?= number_format($precioRegular, 2) ?></s>$<?= number_format($precioPro, 2) ?>
                            <?php else: ?>
                                $<?= number_format($precioRegular, 2) ?>
                            <?php endif; ?>
                        </div>
                        <small><?= $isPro ? '−' . (int)$descuento . '% Pro' : 'Con Pro: $' . number_format($precioPro, 2) ?></small>
                    </div>
                    <form method="POST" action="index.php?action=ordenar">
                        <input type="hidden" name="plato_id" value="<?= $pl['id'] ?>">
                        <input type="hidden" name="sucursal_id" value="<?= $sucursalId ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn primary">Pedir</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<footer class="site-foot">Fuego Andino · Proyecto académico de Ingeniería Web, UDLA</footer>
</body>
</html>
