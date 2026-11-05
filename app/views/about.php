<?php
$pageTitle = 'Nosotros';
require 'app/views/partials/site_top.php';
?>

<div class="wrap">
    <h1 class="page-title">Nosotros</h1>

    <section class="about">
        <div>
            <h2>Una franquicia, tres regiones</h2>
            <p>Fuego Andino nació para reunir en un mismo lugar los sabores de todo el Ecuador: la frescura del mar en la Costa,
               la tradición del horno y la olla en la Sierra, y los envueltos de hoja de la Amazonía.</p>
            <p>Cada sucursal prepara los mismos platos con recetas tradicionales. Puedes pedir en el local que te quede más
               cerca y, si eres cliente Pro, pagar menos por tus favoritos.</p>
            <p><a class="btn primary" href="index.php?action=menu">Explorar el menú</a></p>
        </div>
        <img class="photo" src="assets/img/platos/hornado.jpg" alt="Hornado con llapingachos">
    </section>

    <section class="perks" id="pro">
        <div class="perk"><h3>Descuento Pro</h3><p>Cada plato tiene su propio porcentaje de descuento para los clientes con plan Pro.</p></div>
        <div class="perk"><h3>Envío gratis</h3><p>Los clientes Pro no pagan envío. En la cuenta Free el envío cuesta $2.50.</p></div>
        <div class="perk"><h3>Pago simulado</h3><p>El plan se activa con un pago de demostración; no se cobra dinero real.</p></div>
    </section>

    <section class="locales">
        <h2>Nuestras sucursales</h2>
        <ul>
            <?php foreach ($sucursales as $s): ?>
                <li><b><?= e($s['nombre']) ?></b><span><?= e($s['direccion']) ?> · <?= e($s['ciudad']) ?></span></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="credits" id="creditos">
        <h2>Créditos de las fotos</h2>
        <ul>
            <?php foreach ($creditos as $c): ?>
                <li><a href="<?= e($c['fuente']) ?>" target="_blank" rel="noopener"><?= e($c['titulo']) ?></a> — <?= e($c['autor']) ?>,
                    <a href="<?= e($c['licencia_url']) ?>" target="_blank" rel="noopener"><?= e($c['licencia']) ?></a>. Wikimedia Commons.</li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<?php require 'app/views/partials/site_bottom.php'; ?>
