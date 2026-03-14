<?php
$host = 'localhost';
$dbname = 'inf1005';
$username = 'rootUser';
$password = 'Inf1005Web!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create users table
    $createTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user','admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($createTable);

    // Ensure default admin credentials exist and remain usable.
    $adminPasswordHash = password_hash('admin', PASSWORD_BCRYPT);

    $checkAdmin = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = 'admin' LIMIT 1");
    $checkAdmin->execute();
    $adminRow = $checkAdmin->fetch();

    if ($adminRow) {
        if (!password_verify('admin', (string) $adminRow['password_hash'])) {
            $updateAdmin = $pdo->prepare("UPDATE users SET password_hash = :hash, role = 'admin' WHERE id = :id");
            $updateAdmin->execute([
                ':hash' => $adminPasswordHash,
                ':id' => $adminRow['id'],
            ]);
        }
    } else {
        $insertAdmin = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, role) VALUES ('System', 'Administrator', 'admin', 'admin@s3.local', :hash, 'admin')");
        $insertAdmin->execute([':hash' => $adminPasswordHash]);
    }

    //$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pdo = null;
}

?>