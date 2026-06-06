<?php
require_once __DIR__ . '/../config/database.php';

class Invoice {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO invoices (
                    invoice_application_id, user_id, order_id, invoice_number, amount,
                    company_name, tax_id, invoice_email, invoice_preference,
                    address, phone, bank_name, bank_account, invoice_type, issued_by, issued_at
                ) VALUES (
                    :invoice_application_id, :user_id, :order_id, :invoice_number, :amount,
                    :company_name, :tax_id, :invoice_email, :invoice_preference,
                    :address, :phone, :bank_name, :bank_account, :invoice_type, :issued_by, NOW()
                )";
        
        $params = [
            ':invoice_application_id' => $data['invoice_application_id'],
            ':user_id' => $data['user_id'],
            ':order_id' => $data['order_id'],
            ':invoice_number' => $data['invoice_number'],
            ':amount' => $data['amount'],
            ':company_name' => $data['company_name'],
            ':tax_id' => $data['tax_id'],
            ':invoice_email' => $data['invoice_email'],
            ':invoice_preference' => $data['invoice_preference'],
            ':address' => $data['address'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':bank_name' => $data['bank_name'] ?? null,
            ':bank_account' => $data['bank_account'] ?? null,
            ':invoice_type' => $data['invoice_type'],
            ':issued_by' => $data['issued_by']
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT i.*, o.order_no, o.product_name, 
                u.username, u.email, iu.username as issuer_name
                FROM invoices i 
                LEFT JOIN orders o ON i.order_id = o.id 
                LEFT JOIN users u ON i.user_id = u.id 
                LEFT JOIN users iu ON i.issued_by = iu.id 
                WHERE i.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByInvoiceNumber($invoiceNumber) {
        $sql = "SELECT i.*, o.order_no, o.product_name, 
                u.username, u.email, iu.username as issuer_name
                FROM invoices i 
                LEFT JOIN orders o ON i.order_id = o.id 
                LEFT JOIN users u ON i.user_id = u.id 
                LEFT JOIN users iu ON i.issued_by = iu.id 
                WHERE i.invoice_number = :invoice_number";
        return $this->db->fetchOne($sql, [':invoice_number' => $invoiceNumber]);
    }
    
    public function findByUserId($userId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT i.*, o.order_no, o.product_name, 
                u.username, u.email, iu.username as issuer_name
                FROM invoices i 
                LEFT JOIN orders o ON i.order_id = o.id 
                LEFT JOIN users u ON i.user_id = u.id 
                LEFT JOIN users iu ON i.issued_by = iu.id 
                WHERE i.user_id = :user_id 
                ORDER BY i.issued_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function findAll($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT i.*, o.order_no, o.product_name, 
                u.username, u.email, iu.username as issuer_name
                FROM invoices i 
                LEFT JOIN orders o ON i.order_id = o.id 
                LEFT JOIN users u ON i.user_id = u.id 
                LEFT JOIN users iu ON i.issued_by = iu.id 
                ORDER BY i.issued_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM invoices";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }
    
    public function countByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM invoices WHERE user_id = :user_id";
        $result = $this->db->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'] ?? 0;
    }
    
    public function countByType($type) {
        $sql = "SELECT COUNT(*) as count FROM invoices WHERE invoice_type = :type";
        $result = $this->db->fetchOne($sql, [':type' => $type]);
        return $result['count'] ?? 0;
    }
    
    public function getTotalAmountByUserId($userId) {
        $sql = "SELECT SUM(amount) as total FROM invoices WHERE user_id = :user_id";
        $result = $this->db->fetchOne($sql, [':user_id' => $userId]);
        return $result['total'] ?? 0;
    }
}
