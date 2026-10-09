<h1 align="center">Sistema Web MVC: Autenticación y CRUD con SQLite</h1>

<p align="center">
  <img src="https://img.shields.io/badge/STATUS-COMPLETADO-green?style=for-the-badge" alt="Estado">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/TailwindCSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="Licencia">
</p>

---

## 📑 Índice

* [Descripción del Proyecto](#-descripción-del-proyecto)
* [Estado del Proyecto](#-estado-del-proyecto)
* [Características y Demostración](#️-características-y-demostración)
* [Estructura del Proyecto (MVC)](#-estructura-del-proyecto-mvc)
* [Tecnologías Utilizadas](#️-tecnologías-utilizadas)
* [Acceso y Ejecución](#-acceso-y-ejecución)
* [Credenciales por Defecto](#-credenciales-por-defecto)
* [Demostración en Video](#-demostración-en-video)
* [Desarrollador](#-desarrollador)
* [Licencia](#-licencia)

---

## 📖 Descripción del Proyecto

Aplicación web desarrollada como solución a la **Tarea 3** académica. Implementa el patrón arquitectónico **Modelo-Vista-Controlador (MVC)** en PHP nativo acoplado a un motor local **SQLite** mediante la interfaz PDO.

El sistema aísla completamente las rutas protegidas a través de variables de sesión (`$_SESSION`), exigiendo credenciales válidas antes de permitir operaciones sobre los datos, y gestiona las contraseñas mediante hashing **MD5** para cumplir con las directrices de seguridad de la asignación.

---

## 🚦 Estado del Proyecto

<h4 align="center">
  ✅ Proyecto Finalizado y Funcional ✅
</h4>

---

## ⚙️ Características y Demostración

* 🔐 **Autenticación Robusta:** Inicio de sesión con verificación de credenciales y encriptación de claves vía MD5.
* 🛡️ **Guarda de Seguridad (Route Guard):** Restricción de acceso a la vista protegida (`index.php?action=crud`). Cualquier petición no autorizada redirige forzosamente al login.
* 📊 **Operaciones CRUD Completas:**
  * **Crear:** Registro de nuevos elementos en la base de datos mediante sentencias preparadas.
  * **Leer:** Renderizado dinámico de la tabla con identificadores visuales secuenciales.
  * **Actualizar:** Modificación de registros preexistentes manteniendo el contexto en el formulario.
  * **Eliminar:** Borrado seguro de tuplas con confirmación en el navegador.
* 🎨 **Interfaz Responsiva:** Diseño unificado en tonos claros y azules usando Tailwind CSS.

---

## 📂 Estructura del Proyecto (MVC)

```text
tarea3_mvc/
├── index.php                 # Front Controller y enrutador principal
├── data.sqlite               # Base de datos SQLite (autogenerada en ejecución)
├── config/
│   └── database.php          # Configuración PDO y creación de tablas
└── app/
    ├── controllers/
    │   ├── AuthController.php # Lógica de inicio y cierre de sesión
    │   └── ItemController.php # Control del CRUD y validación de autenticación
    ├── models/
    │   ├── Item.php           # Operaciones CRUD sobre la tabla 'items'
    │   └── User.php           # Verificación de credenciales con hash MD5
    └── views/
        ├── login.php          # Vista del formulario de acceso
        └── crud.php           # Vista protegida del panel de datos
```

---

## 🛠️ Tecnologías Utilizadas

- **Lenguaje:** [PHP 8.x](https://www.php.net/) (Programación modular y PDO)
- **Base de Datos:** [SQLite](https://www.sqlite.org/) (Gestión local de datos relacionales)
- **Estilos:** [Tailwind CSS](https://tailwindcss.com/) (Vía CDN)
- **Control de Versiones:** [Git](https://git-scm.com/) y [GitHub](https://github.com/)

---

## 🚀 Acceso y Ejecución

### 1. Clonar el repositorio

```bash
git clone https://github.com/ArielGitHuub/tarea3_mvc.git
cd tarea3_mvc
```

### 2. Levantar el servidor local

No se requieren dependencias externas ni configuración de Apache. Ejecuta el servidor interno de PHP en la raíz del proyecto:

```bash
php -S localhost:8000
```

### 3. Abrir en el navegador

Ingresa a la siguiente dirección:

```plaintext
http://localhost:8000/index.php
```

> **Nota:** La base de datos `data.sqlite` y el usuario inicial se generarán automáticamente la primera vez que se ejecute la aplicación.

---

## 🔑 Credenciales por Defecto

| Parámetro             | Valor                              |
| --------------------- | ---------------------------------- |
| **Usuario**           | `admin`                            |
| **Contraseña**        | `1234`                             |
| **Hash MD5 guardado** | `81dc9bdb52d04dc20036dbd8313ed055` |

---

## 📹 Demostración en Video

Visualiza la explicación del código, la protección de rutas y la funcionalidad completa del CRUD en el siguiente enlace:

- 🎥 **Enlace al video explicativo:** [Ver en YouTube](https://youtu.be/acrYkLYtchI)

---

## 👤 Desarrollador

**Ariel Tejada**  
Estudiante de Ingeniería de Software — UDLA

---

## 📄 Licencia

Este proyecto se distribuye bajo la licencia **MIT**. Para más detalles, consulta el archivo `LICENSE`.
