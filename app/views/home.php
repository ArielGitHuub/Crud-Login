<?php
$pageTitle = 'Inicio';
$diapositivas = array_map(fn($p) => [
    'id' => $p['id'],
    'nombre' => $p['nombre'],
    'origen' => $p['region'] . ($p['provincia'] ? ' · ' . $p['provincia'] : ''),
    'imagen' => foto_plato($p['imagen']),
    'precio' => dinero($p['precio']),
    'precioPro' => dinero($p['precio'] * (1 - $p['descuento_pro'] / 100)),
], $destacados);
require 'app/views/partials/site_top.php';
?>

<section class="hero">
    <div>
        <p class="kicker">La cocina</p>
        <h1>Fuego Andino</h1>
        <p class="tagline">Prueba el sabor, únete al club</p>
        <div class="cta">
            <a class="btn primary" href="index.php?action=menu">Ver el menú</a>
            <a class="btn" href="index.php?action=nosotros#pro">Conoce el plan Pro</a>
        </div>
        <div class="thumbs" id="thumbs" role="tablist" aria-label="Platos destacados">
            <?php foreach ($diapositivas as $i => $d): ?>
                <button type="button" data-i="<?= $i ?>" class="<?= $i === 0 ? 'on' : '' ?>" aria-label="<?= e($d['nombre']) ?>">
                    <img src="<?= e($d['imagen']) ?>" alt="">
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="stage">
        <div class="plate">
            <img id="heroImg" src="<?= e($diapositivas[0]['imagen'] ?? foto_plato(null)) ?>" alt="<?= e($diapositivas[0]['nombre'] ?? '') ?>">
            <div class="badge"><small id="heroPriceLabel"><?= $isPro ? 'Precio Pro' : 'Desde' ?></small><b id="heroPrice"><?= e($diapositivas[0][$isPro ? 'precioPro' : 'precio'] ?? '') ?></b></div>
        </div>
        <div class="name"><h2 id="heroName"><?= e($diapositivas[0]['nombre'] ?? '') ?></h2><p id="heroOrigin"><?= e($diapositivas[0]['origen'] ?? '') ?></p></div>
    </div>
</section>

<div class="dots" id="dots">
    <?php foreach ($diapositivas as $i => $d): ?><button type="button" data-i="<?= $i ?>" class="<?= $i === 0 ? 'on' : '' ?>" aria-label="Plato <?= $i + 1 ?>"></button><?php endforeach; ?>
</div>

<script>
    // Carrusel de platos destacados: automático cada 5 s o con las miniaturas / puntos
    const slides = <?= json_encode($diapositivas, JSON_UNESCAPED_UNICODE) ?>;
    const esPro = <?= json_encode($isPro) ?>;
    const img = document.getElementById('heroImg');
    let actual = 0, timer;

    function mostrar(i) {
        if (!slides.length) return;
        actual = (i + slides.length) % slides.length;
        const s = slides[actual];
        img.classList.add('swap');
        setTimeout(() => {
            img.src = s.imagen; img.alt = s.nombre;
            document.getElementById('heroName').textContent = s.nombre;
            document.getElementById('heroOrigin').textContent = s.origen;
            document.getElementById('heroPrice').textContent = esPro ? s.precioPro : s.precio;
            img.classList.remove('swap');
        }, 280);
        document.querySelectorAll('#thumbs button, #dots button').forEach(b => b.classList.toggle('on', +b.dataset.i === actual));
    }
    function reiniciar() { clearInterval(timer); timer = setInterval(() => mostrar(actual + 1), 5000); }

    document.querySelectorAll('#thumbs button, #dots button').forEach(b => b.addEventListener('click', () => { mostrar(+b.dataset.i); reiniciar(); }));
    reiniciar();
</script>

<?php require 'app/views/partials/site_bottom.php'; ?>
