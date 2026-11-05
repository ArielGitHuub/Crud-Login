# 🔥 Fuego Andino — Administración MVC (PHP + SQLite)

Panel de administración del sistema **core "Fuego Andino"**: gestión gastronómica ecuatoriana multisucursal. Esta administración alimenta el menú público y el dashboard de ventas/ganancias.

Stack: **PHP 8 nativo (patrón MVC)** · **SQLite (PDO)** · **Tailwind CSS (CDN)**.

---

## ✨ Qué administra

El administrador gestiona el **Catálogo de Platos** (CRUD completo), que es la base del core:

| Dato | Uso en el core |
| --- | --- |
| Nombre, descripción, ingredientes | Menú público |
| Región → Provincia de origen | Filtros del menú y ranking por región |
| Precio (PVP) y costo de elaboración | Cálculo de ganancia en pedidos y dashboard |
| Descuento Plan Pro (%) | Precio final para clientes Pro |

---

## ✅ Requisitos de la tarea

### 1. Validación en Back-End de datos sensibles
Los datos sensibles del core son **precios, costos y descuentos** (determinan la ganancia). Se validan en el servidor en `PlatoController::validar()` **antes de grabar** en SQLite — el HTML/JS no es confiable:

- Precio y costo: numéricos, entre 0.01 y 500.
- `costo < precio` (no se permiten platos con pérdida).
- Descuento Pro entre 0% y 50% y el precio Pro nunca por debajo del costo.
- Nombre único (sin distinguir mayúsculas) y de 3 a 80 caracteres.
- Formato de tiempo `00:15 hrs`; descripción e ingredientes obligatorios.
- Si hay errores, se re-muestra el formulario con lo digitado y el mensaje bajo cada campo.
- Todo el acceso a BD usa sentencias preparadas (PDO).

### 2. Relaciones con dropdowns dependientes (sin claves foráneas manuales)
Tablas `regiones` → `provincias` → `platos.provincia_id`.

- El formulario tiene **Región** y **Provincia**: al escoger una región, el dropdown de provincias se carga vía `fetch` desde `index.php?action=provincias&region_id=N` (JSON).
- El back-end **vuelve a verificar** que la provincia exista y pertenezca a la región elegida; la columna `platos.region` se deriva de la BD, no del formulario.

### 3. Git y deploy
Código versionado en Git. Deploy: ver la sección *Deploy*.

---

## 📂 Estructura (MVC)

```text
tarea3_mvc/
├── index.php                      # Front controller / router
├── data.sqlite                    # Base SQLite (se crea y migra sola)
├── config/database.php            # PDO, esquema, migraciones y datos semilla
└── app/
    ├── controllers/               # Auth, Dashboard, Menu, Plato (admin + validación)
    ├── models/                    # User, Plato, Pedido, Sucursal, Dashboard, Geografia
    └── views/                     # login, menu, dashboard, plato_form
```

## 🚀 Ejecución local

```bash
git clone https://github.com/ArielGitHuub/tarea3_mvc.git
cd tarea3_mvc
php -S localhost:8000
```

Abrir `http://localhost:8000`. No requiere dependencias (solo `pdo_sqlite`, incluido en PHP).

## 🔑 Credenciales de prueba

| Usuario | Contraseña | Rol |
| --- | --- | --- |
| `admin` | `1234` | Administrador (accede a `?action=platos` y `?action=dashboard`) |
| `cliente_pro` | `1234` | Cliente plan Pro |
| `cliente_free` | `1234` | Cliente plan Free |

> Las contraseñas se guardan con MD5 por requerimiento académico de la tarea; no es adecuado para producción.

## 🌐 Deploy

PHP necesita un hosting con soporte PHP (el servidor interno `php -S` es solo para desarrollo). Subir el repositorio y asegurar que la carpeta del proyecto tenga permisos de escritura para `data.sqlite`.

- **URL desplegada:** _(pendiente de completar)_
- **Video explicativo:** [Ver en YouTube](https://youtu.be/acrYkLYtchI)

## 👤 Autor

**Ariel Tejada** — Ingeniería de Software, UDLA.

## 📄 Licencia

MIT.
