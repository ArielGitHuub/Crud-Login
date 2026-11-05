<?php
/** Páginas públicas: inicio (carrusel de platos destacados) e información del restaurante. */
class HomeController {
    private $platoModel;
    private $sucursalModel;

    public function __construct($db) {
        $this->platoModel = new Plato($db);
        $this->sucursalModel = new Sucursal($db);
    }

    public function inicio() {
        $destacados = $this->platoModel->getDestacados(4);
        $isPro = (($_SESSION['plan'] ?? 'free') === 'pro');
        $pagina = 'inicio';
        require 'app/views/home.php';
    }

    public function nosotros() {
        $sucursales = $this->sucursalModel->getAll();
        $creditos = json_decode(file_get_contents('assets/img/creditos.json'), true) ?: [];
        $pagina = 'nosotros';
        require 'app/views/about.php';
    }
}
