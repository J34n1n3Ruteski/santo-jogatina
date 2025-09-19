<?php
require_once __DIR__ . '/db.php';

function view(string $page, array $data = []) {
  extract($data);
  include __DIR__ . '/../public/partials/header.php';
  include __DIR__ . '/../pages/' . $page . '.php';
  include __DIR__ . '/../public/partials/footer.php';
}

function money($n){ return 'R$ ' . number_format($n, 2, ',', '.'); }

/** Retorna o usuário logado (ou null) */
function current_user(): ?array {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  return $_SESSION['user'] ?? null;
}

/** Exige login; se não tiver, redireciona para ?p=login&next=<destino> */
function require_login(string $next = 'home'): array {
  $u = current_user();
  if (!$u) {
    header('Location: ?p=login&next=' . urlencode($next));
    exit;
  }
  return $u;
}


// view() para montar header + página + footer

// money() para formatar preço

// current_user() para ler o usuário da sessão

// require_login() para redirecionar quem não estiver logado