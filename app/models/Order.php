<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/InvoiceApplication.php';

class Order {
    private $db;
    private $invoiceApplicationModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->invoiceApplicationModel = new InvoiceApplication();
    }
    
    private function generateOrderNo() {
        return 'ORD' . date('YmdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    public function create($data) {
        $sql = "INSERT INTO orders (user_id, license_id, order_no, order_type, product_name, amount, status, created_at) 
                VALUES (:user_id, :license_id, :order_no, :order_type, :product_name, :amount, :status, NOW())";
        
        $params = [
            ':user_id' => $data['user_id'],
            ':license_id' => $data['license_id'] ?? null,
            ':order_no' => $this->generateOrderNo(),
            ':order_type' => $data['order_type'],
            ':product_name' => $data['product_name'],
            ':amount' => $data['amount'],
            ':status' => $data['status'] ?? 'completed'
        ];
        
        $this->db->execute($sql, $params);
        $orderId = $this->db->lastInsertId();
        
        if ($params[':status'] === 'completed') {
            $this->generateInvoiceApplication($orderId, $data['user_id']);
        }
        
        return $orderId;
    }
    
    public function generateInvoiceApplication($orderId, $userId) {
        $order = $this->findById($orderId);
        if (!$order || $order['invoice_generated']) {
            return false;
        }
        
        $applicationId = $this->invoiceApplicationModel->create([
            'order_id' => $orderId,
            'user_id' => $userId
        ]);
        
        if ($applicationId) {
            $sql = "UPDATE orders SET invoice_generated = 1 WHERE id = :id";
            $stmt = $this->db->execute($sql, [':id' => $orderId]);
            if (!$stmt) return false;
        }
        
        return $applicationId;
    }
    
    public function findById($id) {
        $sql = "SELECT o.*, u.username, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByOrderNo($orderNo) {
        $sql = "SELECT o.*, u.username, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.order_no = :order_no";
        return $this->db->fetchOne($sql, [':order_no' => $orderNo]);
    }
    
    public function findByUserId($userId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT o.*, u.username, u.email, 
                (SELECT status FROM invoice_applications ia WHERE ia.order_id = o.id ORDER BY ia.id DESC LIMIT 1) as invoice_status
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.user_id = :user_id 
                ORDER BY o.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function findAll($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT o.*, u.username, u.email,
                (SELECT status FROM invoice_applications ia WHERE ia.order_id = o.id ORDER BY ia.id DESC LIMIT 1) as invoice_status
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM orders";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }
    
    public function countByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id";
        $result = $this->db->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'] ?? 0;
    }
    
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = :status";
        $result = $this->db->fetchOne($sql, [':status' => $status]);
        return $result['count'] ?? 0;
    }
    
    public function findCompletedWithoutInvoice($userId = null, $limit = 100) {
        $limit = max(1, min(1000, (int)$limit));
        $sql = "SELECT o.*, u.username, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.status = 'completed' AND o.invoice_generated = 0";
        $params = [];
        if ($userId) {
            $sql .= " AND o.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        $sql .= " ORDER BY o.created_at DESC LIMIT {$limit}";
        return $this->db->fetchAll($sql, $params);
    }
}
