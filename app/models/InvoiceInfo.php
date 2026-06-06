<?php
require_once __DIR__ . '/../config/database.php';

class InvoiceInfo {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO invoice_info (user_id, company_name, tax_id, invoice_email, invoice_preference, address, phone, bank_name, bank_account) 
                VALUES (:user_id, :company_name, :tax_id, :invoice_email, :invoice_preference, :address, :phone, :bank_name, :bank_account)";
        
        $params = [
            ':user_id' => $data['user_id'],
            ':company_name' => $data['company_name'],
            ':tax_id' => $data['tax_id'],
            ':invoice_email' => $data['invoice_email'],
            ':invoice_preference' => $data['invoice_preference'] ?? 'electronic',
            ':address' => $data['address'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':bank_name' => $data['bank_name'] ?? null,
            ':bank_account' => $data['bank_account'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByUserId($userId) {
        $sql = "SELECT * FROM invoice_info WHERE user_id = :user_id";
        return $this->db->fetchOne($sql, [':user_id' => $userId]);
    }
    
    public function update($userId, $data) {
        $fields = [];
        $params = [':user_id' => $userId];
        
        if (isset($data['company_name'])) {
            $fields[] = "company_name = :company_name";
            $params[':company_name'] = $data['company_name'];
        }
        if (isset($data['tax_id'])) {
            $fields[] = "tax_id = :tax_id";
            $params[':tax_id'] = $data['tax_id'];
        }
        if (isset($data['invoice_email'])) {
            $fields[] = "invoice_email = :invoice_email";
            $params[':invoice_email'] = $data['invoice_email'];
        }
        if (isset($data['invoice_preference'])) {
            $fields[] = "invoice_preference = :invoice_preference";
            $params[':invoice_preference'] = $data['invoice_preference'];
        }
        if (array_key_exists('address', $data)) {
            $fields[] = "address = :address";
            $params[':address'] = $data['address'] ?? null;
        }
        if (array_key_exists('phone', $data)) {
            $fields[] = "phone = :phone";
            $params[':phone'] = $data['phone'] ?? null;
        }
        if (array_key_exists('bank_name', $data)) {
            $fields[] = "bank_name = :bank_name";
            $params[':bank_name'] = $data['bank_name'] ?? null;
        }
        if (array_key_exists('bank_account', $data)) {
            $fields[] = "bank_account = :bank_account";
            $params[':bank_account'] = $data['bank_account'] ?? null;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE invoice_info SET " . implode(', ', $fields) . " WHERE user_id = :user_id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function save($userId, $data) {
        $existing = $this->findByUserId($userId);
        if ($existing) {
            return $this->update($userId, $data);
        }
        $data['user_id'] = $userId;
        return $this->create($data);
    }
    
    public function isCompleteForSpecialInvoice($userId) {
        $info = $this->findByUserId($userId);
        if (!$info) return false;
        return !empty($info['company_name']) && !empty($info['tax_id']) && 
               !empty($info['invoice_email']) && !empty($info['address']) && 
               !empty($info['phone']) && !empty($info['bank_name']) && 
               !empty($info['bank_account']);
    }
    
    public function getMissingFieldsForSpecialInvoice($userId) {
        $info = $this->findByUserId($userId);
        $missing = [];
        if (empty($info['company_name'])) $missing[] = '发票抬头';
        if (empty($info['tax_id'])) $missing[] = '税号';
        if (empty($info['invoice_email'])) $missing[] = '开票邮箱';
        if (empty($info['address'])) $missing[] = '公司地址';
        if (empty($info['phone'])) $missing[] = '公司电话';
        if (empty($info['bank_name'])) $missing[] = '开户银行';
        if (empty($info['bank_account'])) $missing[] = '银行账号';
        return $missing;
    }
}
