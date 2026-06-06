<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Invoice.php';
require_once __DIR__ . '/InvoiceInfo.php';

class InvoiceApplication {
    private $db;
    private $invoiceModel;
    private $invoiceInfoModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->invoiceModel = new Invoice();
        $this->invoiceInfoModel = new InvoiceInfo();
    }
    
    public function create($data) {
        $sql = "INSERT INTO invoice_applications (order_id, user_id, status, created_at) 
                VALUES (:order_id, :user_id, 'pending', NOW())";
        
        $params = [
            ':order_id' => $data['order_id'],
            ':user_id' => $data['user_id']
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT ia.*, o.order_no, o.order_type, o.product_name, o.amount, 
                u.username, u.email, ru.username as reviewer_name
                FROM invoice_applications ia 
                LEFT JOIN orders o ON ia.order_id = o.id 
                LEFT JOIN users u ON ia.user_id = u.id 
                LEFT JOIN users ru ON ia.reviewed_by = ru.id 
                WHERE ia.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByUserId($userId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT ia.*, o.order_no, o.order_type, o.product_name, o.amount, 
                u.username, u.email
                FROM invoice_applications ia 
                LEFT JOIN orders o ON ia.order_id = o.id 
                LEFT JOIN users u ON ia.user_id = u.id 
                WHERE ia.user_id = :user_id 
                ORDER BY ia.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function findAll($status = null, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT ia.*, o.order_no, o.order_type, o.product_name, o.amount, 
                u.username, u.email, ru.username as reviewer_name
                FROM invoice_applications ia 
                LEFT JOIN orders o ON ia.order_id = o.id 
                LEFT JOIN users u ON ia.user_id = u.id 
                LEFT JOIN users ru ON ia.reviewed_by = ru.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE ia.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY ia.created_at DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function count($status = null) {
        $sql = "SELECT COUNT(*) as count FROM invoice_applications";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function countByUserId($userId, $status = null) {
        $sql = "SELECT COUNT(*) as count FROM invoice_applications WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function approve($id, $reviewerId) {
        $sql = "UPDATE invoice_applications SET status = 'approved', reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id AND status = 'pending'";
        $stmt = $this->db->execute($sql, [':id' => $id, ':reviewed_by' => $reviewerId]);
        return $stmt && $stmt->rowCount() > 0;
    }
    
    public function reject($id, $reviewerId, $rejectionReason) {
        $sql = "UPDATE invoice_applications SET status = 'rejected', rejection_reason = :rejection_reason, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id AND status = 'pending'";
        $stmt = $this->db->execute($sql, [
            ':id' => $id,
            ':rejection_reason' => $rejectionReason,
            ':reviewed_by' => $reviewerId
        ]);
        return $stmt && $stmt->rowCount() > 0;
    }
    
    public function issueInvoice($applicationId, $issuerId, $invoiceNumber) {
        $application = $this->findById($applicationId);
        if (!$application || $application['status'] !== 'approved') {
            return false;
        }
        
        $invoiceInfo = $this->invoiceInfoModel->findByUserId($application['user_id']);
        if (!$invoiceInfo) {
            return false;
        }
        
        $invoiceId = $this->invoiceModel->create([
            'invoice_application_id' => $applicationId,
            'user_id' => $application['user_id'],
            'order_id' => $application['order_id'],
            'invoice_number' => $invoiceNumber,
            'amount' => $application['amount'],
            'company_name' => $invoiceInfo['company_name'],
            'tax_id' => $invoiceInfo['tax_id'],
            'invoice_email' => $invoiceInfo['invoice_email'],
            'invoice_preference' => $invoiceInfo['invoice_preference'],
            'address' => $invoiceInfo['address'],
            'phone' => $invoiceInfo['phone'],
            'bank_name' => $invoiceInfo['bank_name'],
            'bank_account' => $invoiceInfo['bank_account'],
            'invoice_type' => $invoiceInfo['invoice_preference'],
            'issued_by' => $issuerId
        ]);
        
        if ($invoiceId) {
            $sql = "UPDATE invoice_applications SET status = 'issued' WHERE id = :id";
            $this->db->execute($sql, [':id' => $applicationId]);
        }
        
        return $invoiceId;
    }
    
    public function resubmit($id, $userId) {
        $sql = "UPDATE invoice_applications SET status = 'pending', rejection_reason = NULL, reviewed_by = NULL, reviewed_at = NULL WHERE id = :id AND user_id = :user_id AND status = 'rejected'";
        $stmt = $this->db->execute($sql, [':id' => $id, ':user_id' => $userId]);
        return $stmt && $stmt->rowCount() > 0;
    }
    
    public function getMissingFields($applicationId) {
        $application = $this->findById($applicationId);
        if (!$application) return [];
        
        $invoiceInfo = $this->invoiceInfoModel->findByUserId($application['user_id']);
        if (!$invoiceInfo) {
            return ['发票资料未填写'];
        }
        
        if ($invoiceInfo['invoice_preference'] === 'special') {
            return $this->invoiceInfoModel->getMissingFieldsForSpecialInvoice($application['user_id']);
        }
        
        $missing = [];
        if (empty($invoiceInfo['company_name'])) $missing[] = '发票抬头';
        if (empty($invoiceInfo['tax_id'])) $missing[] = '税号';
        if (empty($invoiceInfo['invoice_email'])) $missing[] = '开票邮箱';
        return $missing;
    }
}
