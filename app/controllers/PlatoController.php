<?php
require_once 'app/models/Plato.php';
require_once 'app/models/Sucursal.php';
require_once 'app/models/Geografia.php';

class PlatoController {
    private $platoModel;
    private $sucursalModel;
    private $geoModel;

    public function __construct($db) {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: index.php?action=login&error=unauthorized');
            exit;
        }
        $this->platoModel = new Plato($db);
        $this->sucursalModel = new Sucursal($db);
        $this->geoModel = new Geografia($db);
    }

    // Endpoint JSON para el dropdown dependiente: provincias de una región
    public function provincias() {
        header('Content-Type: application/json; charset=utf-8');
        $regionId = (int)($_GET['region_id'] ?? 0);
        echo json_encode($this->geoModel->getProvinciasByRegion($regionId), JSON_UNESCAPED_UNICODE);
    }

    // Validación en Back-End de los datos sensibles del core (precios, costos, descuentos y origen).
    // Devuelve [datos saneados, errores]; nunca confía en lo validado por JavaScript.
    private function validar(array $in, ?int $id) {
        $errors = [];
        $data = [
            'nombre' => trim($in['nombre'] ?? ''),
            'provincia_id' => (int)($in['provincia_id'] ?? 0),
            'region_id' => (int)($in['region_id'] ?? 0),
            'region' => '',
            'precio' => $in['precio'] ?? '',
            'costo' => $in['costo'] ?? '',
            'descuento_pro' => $in['descuento_pro'] ?? '',
            'activo' => (($in['activo'] ?? '1') === '0') ? 0 : 1,
            'descripcion' => trim($in['descripcion'] ?? ''),
            'ingredientes' => trim($in['ingredientes'] ?? ''),
            'tiempo_preparacion' => trim($in['tiempo_preparacion'] ?? '00:15 hrs'),
        ];

        // Nombre
        $len = preg_match_all('/./su', $data['nombre']); // longitud en caracteres UTF-8 sin requerir mbstring
        if ($len < 3 || $len > 80) {
            $errors['nombre'] = 'El nombre debe tener entre 3 y 80 caracteres.';
        } elseif ($this->platoModel->nombreExiste($data['nombre'], $id)) {
            $errors['nombre'] = 'Ya existe un plato con ese nombre.';
        }

        // Origen: la provincia debe existir y pertenecer a la región elegida (no se confía en el formulario)
        $prov = $data['provincia_id'] ? $this->geoModel->getProvincia($data['provincia_id']) : false;
        if (!$prov) {
            $errors['provincia_id'] = 'Selecciona una provincia de origen válida.';
        } elseif ($prov['region_id'] != $data['region_id']) {
            $errors['provincia_id'] = 'La provincia no pertenece a la región seleccionada.';
        } else {
            $data['region'] = $prov['region']; // se deriva siempre de la BD
        }

        // Datos financieros sensibles
        $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);
        $costo = filter_var($data['costo'], FILTER_VALIDATE_FLOAT);
        $desc = filter_var($data['descuento_pro'], FILTER_VALIDATE_FLOAT);

        if ($precio === false || $precio <= 0 || $precio > 500) {
            $errors['precio'] = 'El precio debe ser un número entre 0.01 y 500.';
        }
        if ($costo === false || $costo <= 0 || $costo > 500) {
            $errors['costo'] = 'El costo debe ser un número entre 0.01 y 500.';
        }
        if ($desc === false || $desc < 0 || $desc > 50) {
            $errors['descuento_pro'] = 'El descuento Pro debe estar entre 0% y 50%.';
        }
        if (!isset($errors['precio']) && !isset($errors['costo'])) {
            if ($costo >= $precio) {
                $errors['costo'] = 'El costo no puede ser mayor o igual al precio (el plato daría pérdida).';
            } elseif (!isset($errors['descuento_pro']) && round($precio * (1 - $desc / 100), 2) < $costo) {
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
            $data['descuento_pro'] = round($desc, 2);
        }
        return [$data, $errors];
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
                    if ($id) {
                        $this->platoModel->update($id, $data);
                        $_SESSION['flash_message'] = "Plato actualizado con éxito.";
                    } else {
                        $this->platoModel->create($data);
                        $_SESSION['flash_message'] = "Plato creado exitosamente en el catálogo.";
                    }
                    header('Location: index.php?action=platos');
                    exit;
                }
                // Con errores: se vuelve a mostrar el formulario con lo digitado
                $form = $data;
                $editId = $id;
            } elseif (isset($_POST['delete_plato'])) {
                $this->platoModel->delete((int)$_POST['id']);
                $_SESSION['flash_message'] = "Plato eliminado del catálogo.";
                header('Location: index.php?action=platos');
                exit;
            }
        }

        $editId = $editId ?? ($_GET['edit_id'] ?? null);
        $platoToEdit = $editId ? $this->platoModel->getById((int)$editId) : null;

        if ($form === null) {
            $form = $platoToEdit ?: [
                'nombre' => '', 'provincia_id' => 0, 'precio' => '6.50', 'costo' => '2.90',
                'descuento_pro' => '20', 'activo' => 1, 'descripcion' => '', 'ingredientes' => '',
                'tiempo_preparacion' => '00:15 hrs',
            ];
            $prov = !empty($form['provincia_id']) ? $this->geoModel->getProvincia($form['provincia_id']) : false;
            $form['region_id'] = $prov ? $prov['region_id'] : 0;
        }

        $regiones = $this->geoModel->getRegiones();
        $provincias = $form['region_id'] ? $this->geoModel->getProvinciasByRegion($form['region_id']) : [];
        $platos = $this->platoModel->getAll(false);
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        require 'app/views/plato_form.php';
    }
}
?>
