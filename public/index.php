<?php
// MOSTRAR ERROS (só pra debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/functions.php';

$page = $_GET['p'] ?? 'home';
$allowed = ['home','catalog','product','cart','checkout','login','register'];

if (!in_array($page, $allowed)) { http_response_code(404); $page = 'home'; }

view($page);


// C:\xampp\htdocs\santo-jogatina\public\Index2.php