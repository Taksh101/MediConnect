<?php
require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../config/auth.php';
class AdminPaymentsController {
    protected $db;
    protected $paymentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->paymentModel = new PaymentModel($db);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 4;
        $total = $this->paymentModel->countAll();
        $totalPages = (int)max(1, ceil($total / max(1, $perPage)));
        $offset = ($page - 1) * $perPage;

        $payments = $this->paymentModel->paginateAll($perPage, $offset);
        
        $startIndex = ($page - 1) * $perPage + 1;

        include __DIR__ . '/../views/admin/payments/index.php';
    }

    public function view() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_admin_login();
        
        $id = (int)($_GET['id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        if (!$id) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/payments");
            exit;
        }

        $payment = $this->paymentModel->findByIdWithDetails($id);
        
        if (!$payment) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '') . "/index.php?route=admin/payments");
            exit;
        }

        include __DIR__ . '/../views/admin/payments/view.php';
    }
}
