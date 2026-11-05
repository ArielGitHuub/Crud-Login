<?php
$pageTitle = 'Menú';
$ghosts = array_values(array_filter(array_column($platos ?: [], 'imagen')));
$params = ['action' => 'menu', 'sucursal_id' => $sucursalId];
$urlBase = fn(array $extra = []) => 'index.php?' . http_build_query($params + $extra) . '#platos';
require 'app/views/partials/site_top.php';
?>

<div class="wrap">
    <h1 class="page-title">Menú</h1>

    <div class="tiles">
        <?php foreach (Plato::REGIONES as $r): $d = $resumen['regiones'][$r] ?? null; ?>
            <a class="tile <?= $region === $r ? 'on' : '' ?>" href="<?= e($urlBase(['region' => $r])) ?>">
                <img src="<?= e(foto_plato($d['imagen'] ?? null)) ?>" alt="" loading="lazy">
                <span><?= e($r) ?><small><?= (int)($d['total'] ?? 0) ?> platos</small></span>
            </a>
        <?php endforeach; ?>
        <?php foreach ($resumen['categorias'] as $c): ?>
            <a class="tile <?= $categoriaId === (int)$c['id'] ? 'on' : '' ?>" href="<?= e($urlBase(['categoria' => $c['id']])) ?>">
                <img src="<?= e(foto_plato($c['imagen'])) ?>" alt="" loading="lazy">
                <span><?= e($c['nombre']) ?><small><?= (int)$c['total'] ?> platos</small></span>
            </a>
        <?php endforeach; ?>
        <a class="tile pro" href="#beneficios"><b>Pro</b><span>Descuentos y envío gratis</span></a>
    </div>

    <section id="platos">
        <div class="results-head">
            <div>
                <h2><?= e($titulo) ?></h2>
                <p><?= count($platos) ?> platos · <a href="<?= e($urlBase()) ?>">ver todo el menú</a></p>
            </div>
            <select class="select" aria-label="Sucursal"
                    onchange="location.href = <?= e(json_encode('index.php?' . http_build_query(array_filter(['action' => 'menu', 'region' => $region, 'categoria' => $categoriaId]) ) . '&sucursal_id=')) ?> + this.value + '#platos'">
                <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $sucursalId == $s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="pedidos"><?php if ($mensajePedido): ?><div class="alert ok"><?= e($mensajePedido) ?><?= es_admin() ? ' <a href="index.php?action=dashboard">Ver en el dashboard</a>' : '' ?></div><?php endif; ?></div>

        <div class="benefit" id="beneficios">
            <div>
                <b><?= $isPro ? 'Beneficios Pro activos' : 'Plan Pro: descuentos y envío gratis' ?></b>
                <span>
                    <?php if ($isPro): ?>
                        Pagas el precio Pro de cada plato y tu envío es gratis.
                    <?php else: ?>
                        Hoy pagas precio regular y $2.50 de envío. <a href="index.php?action=switch_user&role=pro">Activar plan Pro (simulado)</a>
                    <?php endif; ?>
                </span>
            </div>
            <b><?= $isPro ? 'Envío $0.00' : 'Envío $2.50' ?></b>
        </div>

        <?php if (!$platos): ?>
            <div class="empty">Aún no hay platos en esta selección.</div>
        <?php else: ?>
        <div class="dishes">
            <?php foreach ($platos as $pl):
                $precioPro = $pl['precio'] * (1 - $pl['descuento_pro'] / 100);
            ?>
                <article class="dish <?= e(slug_region($pl['region'])) ?>">
                    <img class="photo" src="<?= e(foto_plato($pl['imagen'])) ?>" alt="<?= e($pl['nombre']) ?>" loading="lazy">
                    <div class="body">
                        <div class="origin"><i></i><?= e($pl['region']) ?><?= $pl['provincia'] ? ' · ' . e($pl['provincia']) : '' ?></div>
                        <h3><?= e($pl['nombre']) ?></h3>
                        <p class="desc"><?= e($pl['descripcion']) ?></p>
                    </div>
                    <div class="foot">
                        <div class="price-row">
                            <div class="price">
                                <?php if ($isPro): ?><s><?= dinero($pl['precio']) ?></s><?= dinero($precioPro) ?>
                                <?php else: ?><?= dinero($pl['precio']) ?><?php endif; ?>
                            </div>
                            <small><?= $isPro ? '−' . (int)$pl['descuento_pro'] . '% Pro' : 'Con Pro: ' . dinero($precioPro) ?></small>
                        </div>
                        <?php if (empty($_SESSION['user_id'])): ?>
                            <a class="btn" href="index.php?action=login">Inicia sesión para pedir</a>
                        <?php else: ?>
                            <form method="POST" action="index.php?action=ordenar" class="order">
                                <?= csrf_field() ?>
                                <input type="hidden" name="plato_id" value="<?= $pl['id'] ?>">
                                <input type="hidden" name="sucursal_id" value="<?= $sucursalId ?>">
                                <input class="input qty" type="number" name="cantidad" value="1" min="1" max="20" aria-label="Cantidad">
                                <button type="submit" class="btn primary">Pedir</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php require 'app/views/partials/site_bottom.php'; ?>
