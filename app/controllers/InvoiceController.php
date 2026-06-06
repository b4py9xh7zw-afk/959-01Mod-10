<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/InvoiceInfo.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/InvoiceApplication.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/User.php';

class InvoiceController {
    private $authController;
    private $invoiceInfoModel;
    private $orderModel;
    private $invoiceApplicationModel;
    private $invoiceModel;
    private $userModel;
    
    public function __construct() {
        $this->authController = new AuthController();
        $this->invoiceInfoModel = new InvoiceInfo();
        $this->orderModel = new Order();
        $this->invoiceApplicationModel = new InvoiceApplication();
        $this->invoiceModel = new Invoice();
        $this->userModel = new User();
    }
    
    public function invoiceInfo() {
        $this->authController->requireAuth();
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyName = trim($_POST['company_name'] ?? '');
            $taxId = trim($_POST['tax_id'] ?? '');
            $invoiceEmail = trim($_POST['invoice_email'] ?? '');
            $invoicePreference = $_POST['invoice_preference'] ?? 'electronic';
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $bankName = trim($_POST['bank_name'] ?? '');
            $bankAccount = trim($_POST['bank_account'] ?? '');
            
            if (empty($companyName) || empty($taxId) || empty($invoiceEmail)) {
                $_SESSION['error'] = '发票抬头、税号和开票邮箱为必填项';
                header('Location: /invoice/info');
                exit;
            }
            
            if (!filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = '请输入有效的邮箱地址';
                header('Location: /invoice/info');
                exit;
            }
            
            if ($invoicePreference === 'special') {
                if (empty($address) || empty($phone) || empty($bankName) || empty($bankAccount)) {
                    $_SESSION['error'] = '开具增值税专用发票需填写完整信息：地址、电话、开户银行、银行账号';
                    header('Location: /invoice/info');
                    exit;
                }
            }
            
            $data = [
                'company_name' => $companyName,
                'tax_id' => $taxId,
                'invoice_email' => $invoiceEmail,
                'invoice_preference' => $invoicePreference,
                'address' => $address ?: null,
                'phone' => $phone ?: null,
                'bank_name' => $bankName ?: null,
                'bank_account' => $bankAccount ?: null
            ];
            
            try {
                $this->invoiceInfoModel->save($userId, $data);
                $_SESSION['success'] = '发票资料保存成功';
                header('Location: /invoice/info');
                exit;
            } catch (Exception $e) {
                error_log("Invoice info save error: " . $e->getMessage());
                $_SESSION['error'] = '保存失败，请重试';
                header('Location: /invoice/info');
                exit;
            }
        }
        
        $invoiceInfo = $this->invoiceInfoModel->findByUserId($userId);
        
        $pageTitle = '发票资料维护';
        require_once __DIR__ . '/../views/invoice/info.php';
    }
    
    public function orders() {
        $this->authController->requireAuth();
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if ($role === 'admin') {
            $orders = $this->orderModel->findAll($limit, $offset);
            $total = $this->orderModel->count();
        } else {
            $orders = $this->orderModel->findByUserId($userId, $limit, $offset);
            $total = $this->orderModel->countByUserId($userId);
        }
        
        $totalPages = ceil($total / $limit);
        
        $pageTitle = '我的订单';
        require_once __DIR__ . '/../views/invoice/orders.php';
    }
    
    public function orderCreate() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productName = trim($_POST['product_name'] ?? '');
            $orderType = $_POST['order_type'] ?? 'purchase';
            $amount = (float)($_POST['amount'] ?? 0);
            $licenseId = !empty($_POST['license_id']) ? (int)$_POST['license_id'] : null;
            $targetUserId = $_SESSION['role'] === 'admin' && !empty($_POST['user_id']) ? (int)$_POST['user_id'] : $_SESSION['user_id'];
            
            if (empty($productName) || $amount <= 0) {
                $_SESSION['error'] = '请填写有效的产品名称和金额';
                header('Location: /invoice/order/create');
                exit;
            }
            
            try {
                $orderId = $this->orderModel->create([
                    'user_id' => $targetUserId,
                    'license_id' => $licenseId,
                    'order_type' => $orderType,
                    'product_name' => $productName,
                    'amount' => $amount,
                    'status' => 'completed'
                ]);
                
                $_SESSION['success'] = '订单创建成功，已自动生成开票申请';
                header('Location: /invoice/orders');
                exit;
            } catch (Exception $e) {
                error_log("Order creation error: " . $e->getMessage());
                $_SESSION['error'] = '创建订单失败，请重试';
                header('Location: /invoice/order/create');
                exit;
            }
        }
        
        $users = [];
        if ($_SESSION['role'] === 'admin') {
            $users = $this->userModel->findAll(1000, 0);
        }
        
        $pageTitle = '创建订单';
        require_once __DIR__ . '/../views/invoice/order_create.php';
    }
    
    public function applications() {
        $this->authController->requireAuth();
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = $_GET['status'] ?? null;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if ($role === 'admin') {
            $applications = $this->invoiceApplicationModel->findAll($status, $limit, $offset);
            $total = $this->invoiceApplicationModel->count($status);
        } else {
            $applications = $this->invoiceApplicationModel->findByUserId($userId, $limit, $offset);
            $total = $this->invoiceApplicationModel->countByUserId($userId, $status);
        }
        
        $totalPages = ceil($total / $limit);
        
        $pendingCount = $this->invoiceApplicationModel->count('pending');
        $approvedCount = $this->invoiceApplicationModel->count('approved');
        $rejectedCount = $this->invoiceApplicationModel->count('rejected');
        $issuedCount = $this->invoiceApplicationModel->count('issued');
        
        $pageTitle = '开票申请';
        require_once __DIR__ . '/../views/invoice/applications.php';
    }
    
    public function applicationView() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '申请ID是必填项';
            header('Location: /invoice/applications');
            exit;
        }
        
        $application = $this->invoiceApplicationModel->findById($id);
        if (!$application) {
            $_SESSION['error'] = '开票申请不存在';
            header('Location: /invoice/applications');
            exit;
        }
        
        if ($application['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /invoice/applications');
            exit;
        }
        
        $invoiceInfo = $this->invoiceInfoModel->findByUserId($application['user_id']);
        $missingFields = $this->invoiceApplicationModel->getMissingFields($id);
        
        $pageTitle = '开票申请详情';
        require_once __DIR__ . '/../views/invoice/application_view.php';
    }
    
    public function applicationApprove() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /invoice/applications');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '申请ID是必填项';
            header('Location: /invoice/applications');
            exit;
        }
        
        try {
            $result = $this->invoiceApplicationModel->approve($id, $_SESSION['user_id']);
            if ($result) {
                $_SESSION['success'] = '开票申请已通过';
            } else {
                $_SESSION['error'] = '操作失败，申请状态可能已变更';
            }
        } catch (Exception $e) {
            error_log("Application approve error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败，请重试';
        }
        
        header('Location: /invoice/application/view?id=' . $id);
        exit;
    }
    
    public function applicationReject() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /invoice/applications');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        
        if (!$id) {
            $_SESSION['error'] = '申请ID是必填项';
            header('Location: /invoice/applications');
            exit;
        }
        
        if (empty($rejectionReason)) {
            $_SESSION['error'] = '请填写驳回原因，说明缺少什么资料';
            header('Location: /invoice/application/view?id=' . $id);
            exit;
        }
        
        try {
            $result = $this->invoiceApplicationModel->reject($id, $_SESSION['user_id'], $rejectionReason);
            if ($result) {
                $_SESSION['success'] = '已驳回开票申请';
            } else {
                $_SESSION['error'] = '操作失败，申请状态可能已变更';
            }
        } catch (Exception $e) {
            error_log("Application reject error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败，请重试';
        }
        
        header('Location: /invoice/application/view?id=' . $id);
        exit;
    }
    
    public function applicationResubmit() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /invoice/applications');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '申请ID是必填项';
            header('Location: /invoice/applications');
            exit;
        }
        
        $application = $this->invoiceApplicationModel->findById($id);
        if (!$application || $application['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /invoice/applications');
            exit;
        }
        
        try {
            $result = $this->invoiceApplicationModel->resubmit($id, $_SESSION['user_id']);
            if ($result) {
                $_SESSION['success'] = '已重新提交开票申请';
            } else {
                $_SESSION['error'] = '操作失败，申请状态可能已变更';
            }
        } catch (Exception $e) {
            error_log("Application resubmit error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败，请重试';
        }
        
        header('Location: /invoice/application/view?id=' . $id);
        exit;
    }
    
    public function applicationIssue() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /invoice/applications');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $invoiceNumber = trim($_POST['invoice_number'] ?? '');
        
        if (!$id) {
            $_SESSION['error'] = '申请ID是必填项';
            header('Location: /invoice/applications');
            exit;
        }
        
        if (empty($invoiceNumber)) {
            $_SESSION['error'] = '请填写发票号码';
            header('Location: /invoice/application/view?id=' . $id);
            exit;
        }
        
        try {
            $invoiceId = $this->invoiceApplicationModel->issueInvoice($id, $_SESSION['user_id'], $invoiceNumber);
            if ($invoiceId) {
                $_SESSION['success'] = '发票已开具';
                header('Location: /invoice/view?id=' . $invoiceId);
                exit;
            } else {
                $_SESSION['error'] = '开票失败，请检查申请状态和发票资料是否完整';
            }
        } catch (Exception $e) {
            error_log("Invoice issue error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败，请重试';
        }
        
        header('Location: /invoice/application/view?id=' . $id);
        exit;
    }
    
    public function invoices() {
        $this->authController->requireAuth();
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if ($role === 'admin') {
            $invoices = $this->invoiceModel->findAll($limit, $offset);
            $total = $this->invoiceModel->count();
        } else {
            $invoices = $this->invoiceModel->findByUserId($userId, $limit, $offset);
            $total = $this->invoiceModel->countByUserId($userId);
        }
        
        $totalPages = ceil($total / $limit);
        
        $pageTitle = '已开发票';
        require_once __DIR__ . '/../views/invoice/invoices.php';
    }
    
    public function invoiceView() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '发票ID是必填项';
            header('Location: /invoice/invoices');
            exit;
        }
        
        $invoice = $this->invoiceModel->findById($id);
        if (!$invoice) {
            $_SESSION['error'] = '发票不存在';
            header('Location: /invoice/invoices');
            exit;
        }
        
        if ($invoice['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /invoice/invoices');
            exit;
        }
        
        $currentInvoiceInfo = $this->invoiceInfoModel->findByUserId($invoice['user_id']);
        $hasChanges = $this->hasInvoiceInfoChanged($invoice, $currentInvoiceInfo);
        
        $pageTitle = '发票详情';
        require_once __DIR__ . '/../views/invoice/invoice_view.php';
    }
    
    private function hasInvoiceInfoChanged($invoice, $currentInfo) {
        if (!$currentInfo) return true;
        
        return $invoice['company_name'] !== $currentInfo['company_name'] ||
               $invoice['tax_id'] !== $currentInfo['tax_id'] ||
               $invoice['invoice_email'] !== $currentInfo['invoice_email'] ||
               $invoice['invoice_preference'] !== $currentInfo['invoice_preference'] ||
               $invoice['address'] !== $currentInfo['address'] ||
               $invoice['phone'] !== $currentInfo['phone'] ||
               $invoice['bank_name'] !== $currentInfo['bank_name'] ||
               $invoice['bank_account'] !== $currentInfo['bank_account'];
    }
    
    public function generateInvoiceForOrder() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /invoice/orders');
            exit;
        }
        
        $orderId = $_POST['order_id'] ?? null;
        if (!$orderId) {
            $_SESSION['error'] = '订单ID是必填项';
            header('Location: /invoice/orders');
            exit;
        }
        
        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            $_SESSION['error'] = '订单不存在';
            header('Location: /invoice/orders');
            exit;
        }
        
        if ($order['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /invoice/orders');
            exit;
        }
        
        if ($order['invoice_generated']) {
            $_SESSION['error'] = '该订单已生成开票申请';
            header('Location: /invoice/orders');
            exit;
        }
        
        try {
            $applicationId = $this->orderModel->generateInvoiceApplication($orderId, $order['user_id']);
            if ($applicationId) {
                $_SESSION['success'] = '开票申请已生成';
                header('Location: /invoice/application/view?id=' . $applicationId);
                exit;
            } else {
                $_SESSION['error'] = '生成开票申请失败';
            }
        } catch (Exception $e) {
            error_log("Generate invoice error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败，请重试';
        }
        
        header('Location: /invoice/orders');
        exit;
    }
}
