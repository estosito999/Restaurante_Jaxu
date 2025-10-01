<?php
try {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST','127.0.0.1'),
        env('DB_PORT','3306'),
        env('DB_NAME','jaxu_db')
    );
    $pdo = new PDO($dsn, env('DB_USER','root'), env('DB_PASS',''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die('DB Error: ' . $e->getMessage());
    }
    http_response_code(500);
    die('Database connection error');
}
