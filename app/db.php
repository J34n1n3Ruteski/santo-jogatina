<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
  static $pdo;
  if (!$pdo) {
    try {
      $pdo = new PDO(
        "mysql:host=".DB_HOST.";port=3306;dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, DB_PASS,
        [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
      );
    } catch (PDOException $e) {
      die("Erro de conexão: " . $e->getMessage());
    }
  }
  return $pdo;
}
