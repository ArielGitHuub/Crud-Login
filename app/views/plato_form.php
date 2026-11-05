<?php
$pageTitle = 'Catálogo de platos';
$active = 'platos';
$fe = fn($k) => isset($errors[$k]) ? ' has-error' : '';
$msg = fn($k) => isset($errors[$k]) ? '<div class="field-error">' . htmlspecialchars($errors[$k]) . '</div>' : '';
require 'app/views/partials/admin_top.php';
?>

<div class="page-head">
    <div>
        <h1>Catálogo de platos</h1>
        <p>Alta, edición y baja de los platos que alimentan el menú público y el cálculo de ganancias.</p>
    </div>
</div>

<?php if ($flashMessage): ?>
    <div class="alert ok"><?= htmlspecialchars($flashMessage) ?></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert error">El servidor rechazó los datos. Revisa los campos marcados.</div>
<?php endif; ?>

<section class="card">
    <div class="card-head">
        <div>
            <h2><?= $editId ? 'Editar plato N.º ' . (int)$editId : 'Nuevo plato' ?></h2>
            <p>El costo de elaboración es un dato interno: no se muestra al público.</p>
        </div>
        <?php if ($editId): ?><a class="btn small" href="index.php?action=platos">Crear uno nuevo</a><?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?action=platos" id="platoForm" novalidate>
            <input type="hidden" name="save_plato" value="1">
            <?php if ($editId): ?><input type="hidden" name="id" value="<?= (int)$editId ?>"><?php endif; ?>

            <div class="grid-2">
                <div>
                    <div class="field<?= $fe('nombre') ?>">
                        <label for="nombre">Nombre del plato</label>
                        <input class="input" type="text" id="nombre" name="nombre" maxlength="80" value="<?= htmlspecialchars($form['nombre']) ?>" placeholder="Ej.: Encebollado mixto">
                        <?= $msg('nombre') ?>
                    </div>

                    <div class="grid-2">
                        <div class="field<?= $fe('provincia_id') ?>">
                            <label for="selRegion">Región de origen</label>
                            <select class="select" name="region_id" id="selRegion">
                                <option value="">Selecciona…</option>
                                <?php foreach ($regiones as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($form['region_id'] == $r['id']) ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field<?= $fe('provincia_id') ?>">
                            <label for="selProvincia">Provincia de origen</label>
                            <select class="select" name="provincia_id" id="selProvincia">
                                <option value=""><?= $provincias ? 'Selecciona…' : 'Primero elige una región' ?></option>
                                <?php foreach ($provincias as $pv): ?>
                                    <option value="<?= $pv['id'] ?>" <?= ($form['provincia_id'] == $pv['id']) ? 'selected' : '' ?>><?= htmlspecialchars($pv['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?= $msg('provincia_id') ?>

                    <div class="field<?= $fe('descripcion') ?>" style="margin-top:16px">
                        <label for="descripcion">Descripción</label>
                        <textarea class="textarea" id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($form['descripcion']) ?></textarea>
                        <?= $msg('descripcion') ?>
                    </div>

                    <div class="field<?= $fe('ingredientes') ?>">
                        <label for="ingredientes">Ingredientes principales</label>
                        <input class="input" type="text" id="ingredientes" name="ingredientes" value="<?= htmlspecialchars($form['ingredientes']) ?>" placeholder="Separados por coma">
                        <?= $msg('ingredientes') ?>
                    </div>
                </div>

                <div>
                    <div class="grid-2">
                        <div class="field">
                            <label for="activo">Estado en el menú</label>
                            <select class="select" id="activo" name="activo">
                                <option value="1" <?= $form['activo'] == 1 ? 'selected' : '' ?>>Activo (visible)</option>
                                <option value="0" <?= $form['activo'] == 0 ? 'selected' : '' ?>>Inactivo (oculto)</option>
                            </select>
                        </div>
                        <div class="field<?= $fe('tiempo_preparacion') ?>">
                            <label for="tiempo">Tiempo de preparación</label>
                            <input class="input" type="text" id="tiempo" name="tiempo_preparacion" value="<?= htmlspecialchars($form['tiempo_preparacion']) ?>">
                            <?= $msg('tiempo_preparacion') ?>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="field<?= $fe('precio') ?>">
                            <label for="inputPrecio">Precio de venta</label>
                            <div class="prefix"><span>$</span><input class="input" type="text" inputmode="decimal" id="inputPrecio" name="precio" value="<?= htmlspecialchars($form['precio']) ?>"></div>
                            <?= $msg('precio') ?>
                        </div>
                        <div class="field<?= $fe('costo') ?>">
                            <label for="inputCosto">Costo de elaboración</label>
                            <div class="prefix"><span>$</span><input class="input" type="text" inputmode="decimal" id="inputCosto" name="costo" value="<?= htmlspecialchars($form['costo']) ?>"></div>
                            <?= $msg('costo') ?>
                        </div>
                    </div>

                    <div class="field<?= $fe('descuento_pro') ?>">
                        <label for="inputDescPro">Descuento para plan Pro (%)</label>
                        <input class="input" type="text" inputmode="decimal" id="inputDescPro" name="descuento_pro" value="<?= htmlspecialchars($form['descuento_pro']) ?>">
                        <?= $msg('descuento_pro') ?>
                    </div>

                    <div class="margin-box">
                        <div><span>Precio final Pro</span><b id="lblPro">—</b></div>
                        <div><span>Ganancia por unidad</span><b id="lblNormal">—</b></div>
                        <div><span>Ganancia por unidad (Pro)</span><b id="lblMargenPro">—</b></div>
                        <small>Ganancia = precio de venta − costo de elaboración. Es solo una vista previa: el servidor valida de nuevo al guardar.</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary"><?= $editId ? 'Guardar cambios' : 'Agregar al catálogo' ?></button>
                <?php if ($editId): ?><a class="btn" href="index.php?action=platos">Cancelar</a><?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Platos registrados</h2>
            <p><?= count($platos) ?> platos en la base de datos</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>N.º</th><th>Plato</th><th>Origen</th>
                    <th class="num">Precio</th><th class="num">Costo</th><th class="num">Precio Pro</th>
                    <th>Estado</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($platos as $pl): ?>
                    <tr>
                        <td class="muted"><?= $pl['id'] ?></td>
                        <td><b><?= htmlspecialchars($pl['nombre']) ?></b></td>
                        <td>
                            <span class="tag <?= strtolower(strtr($pl['region'], ['í' => 'i'])) ?>"><?= htmlspecialchars($pl['region']) ?></span>
                            <span class="muted"> · <?= htmlspecialchars($pl['provincia'] ?? 'Sin provincia') ?></span>
                        </td>
                        <td class="num">$<?= number_format($pl['precio'], 2) ?></td>
                        <td class="num muted">$<?= number_format($pl['costo'], 2) ?></td>
                        <td class="num">$<?= number_format($pl['precio'] * (1 - $pl['descuento_pro'] / 100), 2) ?></td>
                        <td><span class="tag <?= $pl['activo'] ? 'on' : 'off' ?>"><?= $pl['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a href="index.php?action=platos&edit_id=<?= $pl['id'] ?>">Editar</a>
                                <form method="POST" action="index.php?action=platos" onsubmit="return confirm('¿Eliminar este plato del catálogo?');">
                                    <input type="hidden" name="delete_plato" value="1">
                                    <input type="hidden" name="id" value="<?= $pl['id'] ?>">
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
    const money = n => '$' + n.toFixed(2);
    const num = id => parseFloat(String(document.getElementById(id).value).replace(',', '.')) || 0;

    // Vista previa de márgenes (la validación real ocurre en el servidor)
    function calcular() {
        const precio = num('inputPrecio'), costo = num('inputCosto'), desc = num('inputDescPro');
        const pro = precio * (1 - desc / 100);
        document.getElementById('lblPro').textContent = money(pro);
        document.getElementById('lblNormal').textContent = money(precio - costo);
        document.getElementById('lblMargenPro').textContent = money(pro - costo);
    }
    ['inputPrecio', 'inputCosto', 'inputDescPro'].forEach(id => document.getElementById(id).addEventListener('input', calcular));
    calcular();

    // Lista dependiente: al elegir región se cargan las provincias de esa región
    const selRegion = document.getElementById('selRegion');
    const selProvincia = document.getElementById('selProvincia');
    selRegion.addEventListener('change', async () => {
        if (!selRegion.value) {
            selProvincia.innerHTML = '<option value="">Primero elige una región</option>';
            return;
        }
        selProvincia.innerHTML = '<option value="">Cargando…</option>';
        const res = await fetch('index.php?action=provincias&region_id=' + encodeURIComponent(selRegion.value));
        const provincias = await res.json();
        selProvincia.innerHTML = '<option value="">Selecciona…</option>';
        provincias.forEach(p => selProvincia.add(new Option(p.nombre, p.id)));
    });
</script>

<?php require 'app/views/partials/admin_bottom.php'; ?>
