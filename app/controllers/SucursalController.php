<?php
/** Administración de sucursales (solo rol admin). */
class SucursalController {
    private $sucursalModel;
    private $geoModel;

    public function __construct($db) {
        if (!es_admin()) {
            redirect('login', ['error' => 'unauthorized']);
        }
        $this->sucursalModel = new Sucursal($db);
        $this->geoModel = new Geografia($db);
    }

    // Endpoint JSON del tercer nivel de la cascada: ciudades de una provincia
    public function ciudades() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->geoModel->getCiudadesByProvincia((int)($_GET['provincia_id'] ?? 0)), JSON_UNESCAPED_UNICODE);
    }

    private function validar(array $in, ?int $id) {
        $errors = [];
        $data = [
            'nombre' => trim($in['nombre'] ?? ''),
            'region_id' => (int)($in['region_id'] ?? 0),
            'provincia_id' => (int)($in['provincia_id'] ?? 0),
            'ciudad_id' => (int)($in['ciudad_id'] ?? 0),
            'ciudad' => '',
            'direccion' => trim($in['direccion'] ?? ''),
            'activa' => (($in['activa'] ?? '1') === '0') ? 0 : 1,
        ];

        $len = preg_match_all('/./su', $data['nombre']);
        if ($len < 3 || $len > 80) {
            $errors['nombre'] = 'El nombre debe tener entre 3 y 80 caracteres.';
        } elseif ($this->sucursalModel->nombreExiste($data['nombre'], $id)) {
            $errors['nombre'] = 'Ya existe una sucursal con ese nombre.';
        }

        // La ciudad debe existir y pertenecer a la provincia y región elegidas
        $ciudad = $data['ciudad_id'] ? $this->geoModel->getCiudad($data['ciudad_id']) : false;
        if (!$ciudad) {
            $errors['ciudad_id'] = 'Selecciona una ciudad válida.';
        } elseif ($ciudad['provincia_id'] != $data['provincia_id'] || $ciudad['region_id'] != $data['region_id']) {
            $errors['ciudad_id'] = 'La ciudad no pertenece a la provincia o región seleccionada.';
        } else {
            $data['ciudad'] = $ciudad['nombre'];
        }

        $len = preg_match_all('/./su', $data['direccion']);
        if ($len < 5 || $len > 150) {
            $errors['direccion'] = 'La dirección debe tener entre 5 y 150 caracteres.';
        }

        return [$data, $errors];
    }

    public function index() {
        $errors = [];
        $form = null;
        $editId = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_sucursal'])) {
                $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
                [$data, $errors] = $this->validar($_POST, $id);
                if (!$errors) {
                    if ($id) {
                        $this->sucursalModel->update($id, $data);
                        flash('ok', 'Sucursal actualizada.');
                    } else {
                        $this->sucursalModel->create($data);
                        flash('ok', 'Sucursal registrada.');
                    }
                    redirect('sucursales');
                }
                $form = $data;
                $editId = $id;
            } elseif (isset($_POST['delete_sucursal'])) {
                $id = (int)$_POST['id'];
                if ($this->sucursalModel->tienePedidos($id)) {
                    // Integridad: una sucursal con ventas solo puede desactivarse
                    flash('error', 'No se puede eliminar: la sucursal tiene pedidos registrados. Márcala como inactiva.');
                } else {
                    $this->sucursalModel->delete($id);
                    flash('ok', 'Sucursal eliminada.');
                }
                redirect('sucursales');
            }
        }

        $editId = $editId ?? ($_GET['edit_id'] ?? null);
        $sucursalToEdit = $editId ? $this->sucursalModel->getById((int)$editId) : null;

        if ($form === null) {
            $form = $sucursalToEdit ?: ['nombre' => '', 'ciudad_id' => 0, 'direccion' => '', 'activa' => 1];
            $ciudad = !empty($form['ciudad_id']) ? $this->geoModel->getCiudad($form['ciudad_id']) : false;
            $form['provincia_id'] = $ciudad ? $ciudad['provincia_id'] : 0;
            $form['region_id'] = $ciudad ? $ciudad['region_id'] : 0;
        }

        $regiones = $this->geoModel->getRegiones();
        $provincias = $form['region_id'] ? $this->geoModel->getProvinciasByRegion($form['region_id']) : [];
        $ciudades = $form['provincia_id'] ? $this->geoModel->getCiudadesByProvincia($form['provincia_id']) : [];
        $sucursales = $this->sucursalModel->getAllAdmin();

        $flashMessage = flash('ok');
        $flashError = flash('error');

        require 'app/views/sucursal_form.php';
    }
}
?>
