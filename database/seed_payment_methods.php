<?php
// Get database name from command line argument or use default
$dbName = $argv[1] ?? 'clan_production_test';

try {
    $pdo = new PDO(
        "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname={$dbName};charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "Connected to database: {$dbName}\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

try {
    // Check if table already has data
    $count = $pdo->query("SELECT COUNT(*) FROM contribution_payment_methods")->fetchColumn();
    
    if ($count > 0) {
        echo "Table already has {$count} payment methods. Skipping seed.\n";
        exit;
    }
    
    // Insert default payment methods
    $methods = [
        [
            'method_name' => 'M-Pesa',
            'account_name' => 'Clan M-Pesa Account',
            'account_number' => '255712345678',
            'status' => 'Active',
            'logo' => 'mpesa.png'
        ],
        [
            'method_name' => 'Tigo Pesa',
            'account_name' => 'Clan Tigo Pesa Account',
            'account_number' => '255712345679',
            'status' => 'Active',
            'logo' => 'tigo.png'
        ],
        [
            'method_name' => 'Airtel Money',
            'account_name' => 'Clan Airtel Money Account',
            'account_number' => '255712345680',
            'status' => 'Active',
            'logo' => 'airtel.png'
        ],
        [
            'method_name' => 'Bank Transfer - CRDB',
            'account_name' => 'Clan Bank Account',
            'account_number' => '0151234567890',
            'status' => 'Active',
            'logo' => 'crdb.png'
        ],
        [
            'method_name' => 'Bank Transfer - NMB',
            'account_name' => 'Clan NMB Account',
            'account_number' => '1234567890',
            'status' => 'Active',
            'logo' => 'nmb.png'
        ],
        [
            'method_name' => 'Cash',
            'account_name' => 'Cash Payment',
            'account_number' => '',
            'status' => 'Active',
            'logo' => 'cash.png'
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO contribution_payment_methods 
        (method_name, account_name, account_number, status, logo)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($methods as $method) {
        $stmt->execute([
            $method['method_name'],
            $method['account_name'],
            $method['account_number'],
            $method['status'],
            $method['logo']
        ]);
        echo "Added: {$method['method_name']}\n";
    }
    
    echo "\nSuccessfully seeded " . count($methods) . " payment methods to {$dbName}.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
