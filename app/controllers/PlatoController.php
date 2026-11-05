<?php
/** Administración del catálogo de platos (solo rol admin). */
class PlatoController {
    private $platoModel;
    private $geoModel;
    private $categoriaModel;

    public function __construct($db) {
        if (!es_admin()) {
            redirect('login', ['error' => 'unauthorized']);
        }
        $this->platoModel = new Plato($db);
        $this->geoModel = new Geografia($db);
        $this->categoriaModel = new Categoria($db);
    }

    // Endpoint JSON del dropdown dependiente: provincias de una región
    public function provincias() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->geoModel->getProvinciasByRegion((int)($_GET['region_id'] ?? 0)), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validación en Back-End de los datos sensibles del core (precios, costos y descuentos) y del origen.
     * Nunca se confía en lo validado por JavaScript. Devuelve [datos saneados, errores por campo].
     */
    private function validar(array $in, ?int $id): array {
        $errors = [];
        $data = [
            'nombre' => trim($in['nombre'] ?? ''),
            'provincia_id' => (int)($in['provincia_id'] ?? 0),
            'region_id' => (int)($in['region_id'] ?? 0),
            'categoria_id' => (int)($in['categoria_id'] ?? 0),
            'region' => '',
            'precio' => $in['precio'] ?? '',
            'costo' => $in['costo'] ?? '',
            'descuento_pro' => $in['descuento_pro'] ?? '',
            'activo' => (($in['activo'] ?? '1') === '0') ? 0 : 1,
            'descripcion' => trim($in['descripcion'] ?? ''),
            'ingredientes' => trim($in['ingredientes'] ?? ''),
            'tiempo_preparacion' => trim($in['tiempo_preparacion'] ?? '00:15 hrs'),
        ];

        $largo = preg_match_all('/./su', $data['nombre']); // caracteres UTF-8 sin requerir mbstring
        if ($largo < 3 || $largo > 80) {
            $errors['nombre'] = 'El nombre debe tener entre 3 y 80 caracteres.';
        } elseif ($this->platoModel->nombreExiste($data['nombre'], $id)) {
            $errors['nombre'] = 'Ya existe un plato con ese nombre.';
        }

        // Origen: la provincia debe existir y pertenecer a la región elegida; la región se deriva de la BD
        $provincia = $data['provincia_id'] ? $this->geoModel->getProvincia($data['provincia_id']) : false;
        if (!$provincia) {
            $errors['provincia_id'] = 'Selecciona una provincia de origen válida.';
        } elseif ($provincia['region_id'] != $data['region_id']) {
            $errors['provincia_id'] = 'La provincia no pertenece a la región seleccionada.';
        } else {
            $data['region'] = $provincia['region'];
        }

        if (!$this->categoriaModel->existe($data['categoria_id'])) {
            $errors['categoria_id'] = 'Selecciona una categoría válida.';
        }

        // Datos financieros sensibles
        $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);
        $costo = filter_var($data['costo'], FILTER_VALIDATE_FLOAT);
        $descuento = filter_var($data['descuento_pro'], FILTER_VALIDATE_FLOAT);

        if ($precio === false || $precio <= 0 || $precio > 500) {
            $errors['precio'] = 'El precio debe ser un número entre 0.01 y 500.';
        }
        if ($costo === false || $costo <= 0 || $costo > 500) {
            $errors['costo'] = 'El costo debe ser un número entre 0.01 y 500.';
        }
        if ($descuento === false || $descuento < 0 || $descuento > 50) {
            $errors['descuento_pro'] = 'El descuento Pro debe estar entre 0% y 50%.';
        }
        if (!isset($errors['precio']) && !isset($errors['costo'])) {
            if ($costo >= $precio) {
                $errors['costo'] = 'El costo no puede ser mayor o igual al precio (el plato daría pérdida).';
            } elseif (!isset($errors['descuento_pro']) && round($precio * (1 - $descuento / 100), 2) < $costo) {
                $errors['descuento_pro'] = 'Con este descuento el precio Pro queda por debajo del costo.';
            }
        }

        if (!preg_match('/^\d{2}:\d{2} hrs$/', $data['tiempo_preparacion'])) {
            $errors['tiempo_preparacion'] = 'Formato esperado: 00:15 hrs.';
        }
        if ($data['descripcion'] === '') {
            $errors['descripcion'] = 'La descripción es obligatoria.';
        }
        if ($data['ingredientes'] === '') {
            $errors['ingredientes'] = 'Los ingredientes son obligatorios.';
        }

        if (!$errors) {
            $data['precio'] = round($precio, 2);
            $data['costo'] = round($costo, 2);
            $data['descuento_pro'] = round($descuento, 2);
        }
        return [$data, $errors];
    }

    /**
     * Valida y guarda la foto subida: tamaño, tipo real del contenido (no la extensión) y dimensiones.
     * Devuelve [ruta|null, error|null]; sin archivo devuelve [null, null] y se conserva la imagen actual.
     */
    private function guardarImagen(?array $archivo): array {
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return [null, 'No se pudo subir la imagen (error ' . $archivo['error'] . ').'];
        }
        if ($archivo['size'] > 2 * 1024 * 1024) {
            return [null, 'La imagen no puede superar los 2 MB.'];
        }
        $info = @getimagesize($archivo['tmp_name']);
        $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($tipos[$info[2]])) {
            return [null, 'Formato no permitido: usa JPG, PNG o WebP.'];
        }
        if ($info[0] < 200 || $info[1] < 150 || $info[0] > 4000 || $info[1] > 4000) {
            return [null, 'Dimensiones no válidas: mínimo 200×150 px y máximo 4000×4000 px.'];
        }

        $dir = 'assets/img/platos/subidas';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ruta = $dir . '/' . bin2hex(random_bytes(8)) . '.' . $tipos[$info[2]]; // nombre aleatorio, nunca el del cliente
        if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
            return [null, 'No se pudo guardar la imagen en el servidor.'];
        }
        return [$ruta, null];
    }

    public function index() {
        $errors = [];
        $form = null;
        $editId = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_plato'])) {
                $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
                [$data, $errors] = $this->validar($_POST, $id);

                if (!$errors) {
                    [$ruta, $errorImagen] = $this->guardarImagen($_FILES['imagen'] ?? null);
                    if ($errorImagen) {
                        $errors['imagen'] = $errorImagen;
                    }
                    $data['imagen'] = $ruta;
                }

                if (!$errors) {
                    if ($id) {
                        $this->platoModel->update($id, $data);
                        flash('ok', 'Plato actualizado con éxito.');
                    } else {
                        $this->platoModel->create($data);
                        flash('ok', 'Plato creado exitosamente en el catálogo.');
                    }
                    redirect('platos');
                }
                // Con errores se vuelve a mostrar el formulario con lo digitado
                $form = $data;
                $editId = $id;
            } elseif (isset($_POST['delete_plato'])) {
                if ($this->platoModel->delete((int)$_POST['id'])) {
                    flash('ok', 'Plato eliminado del catálogo.');
                } else {
                    flash('ok', 'El plato tiene ventas registradas: se ocultó del menú en lugar de borrarse.');
                }
                redirect('platos');
            }
        }

        $editId = $editId ?? ($_GET['edit_id'] ?? null);
        $platoToEdit = $editId ? $this->platoModel->getById((int)$editId) : null;

        if ($form === null) {
            $form = $platoToEdit ?: [
                'nombre' => '', 'provincia_id' => 0, 'categoria_id' => 0, 'precio' => '6.50', 'costo' => '2.90',
                'descuento_pro' => '20', 'activo' => 1, 'descripcion' => '', 'ingredientes' => '',
                'tiempo_preparacion' => '00:15 hrs', 'imagen' => null,
            ];
            $provincia = !empty($form['provincia_id']) ? $this->geoModel->getProvincia($form['provincia_id']) : false;
            $form['region_id'] = $provincia ? $provincia['region_id'] : 0;
        }

        $regiones = $this->geoModel->getRegiones();
        $provincias = $form['region_id'] ? $this->geoModel->getProvinciasByRegion($form['region_id']) : [];
        $categorias = $this->categoriaModel->getAll();
        $platos = $this->platoModel->getAll();
        $flashMessage = flash('ok');

        require 'app/views/plato_form.php';
    }
}
