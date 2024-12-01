
<?php
require_once 'models/UserModel.php';

class AdminController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function index() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit();
        }
        
        $users = $this->userModel->getAllUsers();
        require_once 'views/admin/dashboard.php';
    }

    public function editUser() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ];
            $this->userModel->updateUser($userId, $userData);
            header('Location: index.php?action=admin');
            exit();
        }

        $userId = $_GET['id'];
        $user = $this->userModel->getUserById($userId);
        require_once 'views/admin/edit_user.php';
    }

    public function deleteUser() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit();
        }

        $userId = $_GET['id'];
        $this->userModel->deleteUser($userId);
        header('Location: index.php?action=admin');
        exit();
    }
}