<?php

session_start();

// Class untuk membaca .env dan koneksi PDO
class Database
{
    private $pdo;

    public function openConnection()
    {
        if ($this->pdo === null) {
            $env = $this->parseEnv(__DIR__ . '/.env');
            $host = $env['DB_HOST'] ?? 'localhost';
            $db   = $env['DB_NAME'] ?? '';
            $user = $env['DB_USER'] ?? '';
            $pass = $env['DB_PASS'] ?? '';
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        }
        return $this->pdo;
    }

    public function closeConnection()
    {
        $this->pdo = null;
    }

    private function parseEnv($file)
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
        return $env;
    }

    public function login($email, $password)
    {
        $pdo = $this->openConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Gunakan password_verify untuk hash password
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
