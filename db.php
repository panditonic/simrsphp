<?php
session_start();

// Class untuk membaca .env dan koneksi PDO
class Database {
    private $pdo;

    public function __construct() {
        $env = $this->parseEnv(__DIR__ . '/.env');
        $host = $env['DB_HOST'] ?? 'localhost';
        $db   = $env['DB_NAME'] ?? '';
        $user = $env['DB_USER'] ?? '';
        $pass = $env['DB_PASS'] ?? '';
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    private function parseEnv($file) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
        return $env;
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Password plaintext, untuk produksi gunakan password_hash!
        if ($user && $user['password'] === $password) {
            return $user;
        }
        return false;
    }
}