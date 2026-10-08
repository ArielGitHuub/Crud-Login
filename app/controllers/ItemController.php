<?php
require_once 'app/models/Item.php';

class ItemController {
    private $itemModel;

    public function __construct($db) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $this->itemModel = new Item($db);
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add'])) {
                $this->itemModel->create($_POST['name'], $_POST['description']);
            } elseif (isset($_POST['delete'])) {
                $this->itemModel->delete($_POST['id']);
            } elseif (isset($_POST['update'])) {

                $this->itemModel->update($_POST['id'], $_POST['name'], $_POST['description']);
            }
            header('Location: index.php?action=crud');
            exit;
        }
        

        $itemToEdit = null;
        if (isset($_GET['edit_id'])) {
            $itemToEdit = $this->itemModel->getById($_GET['edit_id']);
        }
        
        $items = $this->itemModel->getAll();
        require 'app/views/crud.php';
    }
}
?>