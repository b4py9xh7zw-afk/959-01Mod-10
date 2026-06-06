<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/InvoiceInfo.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/InvoiceApplication.php';
require_once __DIR__ . '/../models/Invoice.php';

try {
    $userModel = new User();
    $invoiceInfoModel = new InvoiceInfo();
    $orderModel = new Order();
    $invoiceApplicationModel = new InvoiceApplication();
    $invoiceModel = new Invoice();
    
    $admin = $userModel->findByEmail('admin@license-platform.com');
    if (!$admin) {
        echo "Admin user not found. Please run seed_users.php first.\n";
        exit(1);
    }
    
    $testUser = $userModel->findByEmail('user@license-platform.com');
    if (!$testUser) {
        echo "Test user not found. Please run seed_users.php first.\n";
        exit(1);
    }
    
    $existingInvoices = $invoiceModel->findAll(1, 0);
    if (count($existingInvoices) > 0) {
        echo "Invoice data already exists. Skipping seed.\n";
        exit(0);
    }
    
    $invoiceInfoModel->save($testUser['id'], [
        'company_name' => '示例科技有限公司',
        'tax_id' => '91110108MA01ABCD12',
        'invoice_email' => 'finance@example.com',
        'invoice_preference' => 'electronic'
    ]);
    echo "Invoice info created for test user.\n";
    
    $invoiceInfoModel->save($admin['id'], [
        'company_name' => '示范企业集团股份有限公司',
        'tax_id' => '91310000MA1G2H3F45',
        'invoice_email' => 'accounting@demo-enterprise.com',
        'invoice_preference' => 'special',
        'address' => '上海市浦东新区张江高科技园区博云路2号',
        'phone' => '021-58888888',
        'bank_name' => '中国工商银行上海市浦东分行',
        'bank_account' => '1001234567890123456'
    ]);
    echo "Invoice info created for admin user.\n";
    
    $orderId1 = $orderModel->create([
        'user_id' => $testUser['id'],
        'order_type' => 'purchase',
        'product_name' => '基础版软件许可证 - 年付',
        'amount' => 999.00,
        'status' => 'completed'
    ]);
    echo "Order 1 created for test user.\n";
    
    $orderId2 = $orderModel->create([
        'user_id' => $testUser['id'],
        'order_type' => 'additional',
        'product_name' => '增加用户数 x 5',
        'amount' => 500.00,
        'status' => 'completed'
    ]);
    echo "Order 2 created for test user.\n";
    
    $orderId3 = $orderModel->create([
        'user_id' => $admin['id'],
        'order_type' => 'renewal',
        'product_name' => '企业版软件许可证 - 续费2年',
        'amount' => 19800.00,
        'status' => 'completed'
    ]);
    echo "Order 3 created for admin user.\n";
    
    $applications = $invoiceApplicationModel->findAll(null, 10, 0);
    foreach ($applications as $app) {
        if ($app['status'] === 'pending') {
            $invoiceApplicationModel->approve($app['id'], $admin['id']);
            echo "Application {$app['id']} approved.\n";
            
            $invoiceNumber = 'INV' . date('Ymd') . str_pad($app['id'], 6, '0', STR_PAD_LEFT);
            $invoiceId = $invoiceApplicationModel->issueInvoice($app['id'], $admin['id'], $invoiceNumber);
            if ($invoiceId) {
                echo "Invoice {$invoiceNumber} issued for application {$app['id']}.\n";
            }
        }
    }
    
    echo "Invoice data seeding completed!\n";
} catch (Exception $e) {
    error_log("Invoice data seeding failed: " . $e->getMessage());
    echo "Invoice data seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
