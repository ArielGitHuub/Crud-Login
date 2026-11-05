<?php
/** Funciones de apoyo compartidas por controladores y vistas. */

// Escapa texto para HTML (evita XSS al imprimir datos del usuario o de la base)
function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// ---- CSRF: cada formulario POST lleva un token de sesión que el servidor verifica ----
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

// ---- Navegación y mensajes ----
function redirect(string $accion, array $params = [], string $ancla = ''): void {
    header('Location: index.php?' . http_build_query(['action' => $accion] + $params) . ($ancla ? "#$ancla" : ''));
    exit;
}

// flash('ok', 'texto') guarda un mensaje; flash('ok') lo lee y lo borra
function flash(string $tipo, ?string $mensaje = null): ?string {
    if ($mensaje !== null) {
        $_SESSION['flash'][$tipo] = $mensaje;
        return null;
    }
    $texto = $_SESSION['flash'][$tipo] ?? null;
    unset($_SESSION['flash'][$tipo]);
    return $texto;
}

// ---- Presentación ----
function foto_plato(?string $ruta): string {
    return $ruta ?: 'assets/img/platos/default.svg';
}

// "Amazonía" -> "amazonia" (clase CSS)
function slug_region(string $region): string {
    return strtolower(strtr($region, ['í' => 'i', 'Í' => 'I']));
}

function dinero($valor): string {
    return '$' . number_format((float)$valor, 2);
}

function es_admin(): bool {
    return ($_SESSION['rol'] ?? '') === 'admin';
}
