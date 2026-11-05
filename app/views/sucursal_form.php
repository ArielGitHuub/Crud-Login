<?php
$pageTitle = 'Sucursales';
$active = 'sucursales';
$fe = fn($k) => isset($errors[$k]) ? ' has-error' : '';
$msg = fn($k) => isset($errors[$k]) ? '<div class="field-error">' . htmlspecialchars($errors[$k]) . '</div>' : '';
require 'app/views/partials/admin_top.php';
?>

<div class="page-head">
    <div>
        <h1>Sucursales</h1>
        <p>Locales de la franquicia. Cada pedido pertenece a una sucursal.</p>
    </div>
</div>

<?php if ($flashMessage): ?><div class="alert ok"><?= htmlspecialchars($flashMessage) ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert error">El servidor rechazó los datos. Revisa los campos marcados.</div><?php endif; ?>

<section class="card">
    <div class="card-head">
        <div>
            <h2><?= $editId ? 'Editar sucursal N.º ' . (int)$editId : 'Nueva sucursal' ?></h2>
            <p>La ubicación se elige en cascada: región, provincia y ciudad.</p>
        </div>
        <?php if ($editId): ?><a class="btn small" href="index.php?action=sucursales">Crear una nueva</a><?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?action=sucursales" novalidate>
            <input type="hidden" name="save_sucursal" value="1">
            <?php if ($editId): ?><input type="hidden" name="id" value="<?= (int)$editId ?>"><?php endif; ?>

            <div class="field<?= $fe('nombre') ?>">
                <label for="nombre">Nombre de la sucursal</label>
                <input class="input" type="text" id="nombre" name="nombre" maxlength="80" value="<?= htmlspecialchars($form['nombre']) ?>" placeholder="Ej.: Sucursal Quito - La Carolina">
                <?= $msg('nombre') ?>
            </div>

            <div class="grid-2" style="grid-template-columns: repeat(3, 1fr)">
                <div class="field<?= $fe('ciudad_id') ?>">
                    <label for="selRegion">Región</label>
                    <select class="select" name="region_id" id="selRegion">
                        <option value="">Selecciona…</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($form['region_id'] == $r['id']) ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field<?= $fe('ciudad_id') ?>">
                    <label for="selProvincia">Provincia</label>
                    <select class="select" name="provincia_id" id="selProvincia">
                        <option value=""><?= $provincias ? 'Selecciona…' : 'Primero elige una región' ?></option>
                        <?php foreach ($provincias as $pv): ?>
                            <option value="<?= $pv['id'] ?>" <?= ($form['provincia_id'] == $pv['id']) ? 'selected' : '' ?>><?= htmlspecialchars($pv['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field<?= $fe('ciudad_id') ?>">
                    <label for="selCiudad">Ciudad</label>
                    <select class="select" name="ciudad_id" id="selCiudad">
                        <option value=""><?= $ciudades ? 'Selecciona…' : 'Primero elige una provincia' ?></option>
                        <?php foreach ($ciudades as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($form['ciudad_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?= $msg('ciudad_id') ?>

            <div class="grid-2" style="margin-top:16px">
                <div class="field<?= $fe('direccion') ?>">
                    <label for="direccion">Dirección</label>
                    <input class="input" type="text" id="direccion" name="direccion" maxlength="150" value="<?= htmlspecialchars($form['direccion']) ?>" placeholder="Calle principal y secundaria">
                    <?= $msg('direccion') ?>
                </div>
                <div class="field">
                    <label for="activa">Estado</label>
                    <select class="select" id="activa" name="activa">
                        <option value="1" <?= $form['activa'] == 1 ? 'selected' : '' ?>>Activa (recibe pedidos)</option>
                        <option value="0" <?= $form['activa'] == 0 ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary"><?= $editId ? 'Guardar cambios' : 'Registrar sucursal' ?></button>
                <?php if ($editId): ?><a class="btn" href="index.php?action=sucursales">Cancelar</a><?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Sucursales registradas</h2>
            <p><?= count($sucursales) ?> locales</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>N.º</th><th>Sucursal</th><th>Ubicación</th><th>Dirección</th><th class="num">Pedidos</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($sucursales as $s): ?>
                    <tr>
                        <td class="muted"><?= $s['id'] ?></td>
                        <td><b><?= htmlspecialchars($s['nombre']) ?></b></td>
                        <td><?= htmlspecialchars($s['ciudad']) ?> <span class="muted">· <?= htmlspecialchars($s['provincia'] ?? '—') ?></span></td>
                        <td class="muted"><?= htmlspecialchars($s['direccion']) ?></td>
                        <td class="num"><?= $s['total_pedidos'] ?></td>
                        <td><span class="tag <?= $s['activa'] ? 'on' : 'off' ?>"><?= $s['activa'] ? 'Activa' : 'Inactiva' ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a href="index.php?action=sucursales&edit_id=<?= $s['id'] ?>">Editar</a>
                                <form method="POST" action="index.php?action=sucursales" onsubmit="return confirm('¿Eliminar esta sucursal?');">
                                    <input type="hidden" name="delete_sucursal" value="1">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="link-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
    // Cascada de tres niveles: región -> provincia -> ciudad
    const selRegion = document.getElementById('selRegion');
    const selProvincia = document.getElementById('selProvincia');
    const selCiudad = document.getElementById('selCiudad');

    async function cargar(url, select, vacio) {
        select.innerHTML = '<option value="">Cargando…</option>';
        const items = await (await fetch(url)).json();
        select.innerHTML = '<option value="">' + vacio + '</option>';
        items.forEach(i => select.add(new Option(i.nombre, i.id)));
    }

    selRegion.addEventListener('change', () => {
        selCiudad.innerHTML = '<option value="">Primero elige una provincia</option>';
        if (!selRegion.value) {
            selProvincia.innerHTML = '<option value="">Primero elige una región</option>';
            return;
        }
        cargar('index.php?action=provincias&region_id=' + encodeURIComponent(selRegion.value), selProvincia, 'Selecciona…');
    });
    selProvincia.addEventListener('change', () => {
        if (!selProvincia.value) {
            selCiudad.innerHTML = '<option value="">Primero elige una provincia</option>';
            return;
        }
        cargar('index.php?action=ciudades&provincia_id=' + encodeURIComponent(selProvincia.value), selCiudad, 'Selecciona…');
    });
</script>

<?php require 'app/views/partials/admin_bottom.php'; ?>
