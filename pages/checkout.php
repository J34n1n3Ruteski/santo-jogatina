<?php
require_once __DIR__ . '/../app/functions.php';

$user = require_login('checkout');  // garante login
if (session_status() === PHP_SESSION_NONE) {
  session_start();                  // só inicia se ainda não houver sessão
}

$cart = $_SESSION['cart'] ?? [];
$pdo  = db();

// Se carrinho vazio, manda voltar
if (!$cart) {
  echo "<p>Seu carrinho está vazio. <a href='?p=catalog'>Ir ao catálogo</a></p>";
  return;
}

// Monta itens do carrinho a partir do banco
$ids = implode(',', array_map('intval', array_keys($cart)));
$rows = $pdo->query("SELECT id, title, price, image_url FROM products WHERE id IN ($ids)")
            ->fetchAll(PDO::FETCH_ASSOC);

// Calcula total
$total = 0.0;
$items = [];
foreach ($rows as $r) {
  $q = max(1, (int)$cart[$r['id']]);
  $sub = $q * (float)$r['price'];
  $total += $sub;
  $items[] = ['p'=>$r, 'qty'=>$q, 'sub'=>$sub];
}

// Se enviou o formulário, grava o pedido (MVP)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Dados básicos do comprador (opcionalmente valide melhor)
  $name      = trim($_POST['name'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $address   = trim($_POST['address'] ?? '');
  $document  = trim($_POST['document'] ?? '');
  $birth     = $_POST['birth'] ?: null;
  $phone     = trim($_POST['phone'] ?? '');

  try {
    $pdo->beginTransaction();

    // Cria pedido vinculado ao usuário logado
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'NEW')");
    $stmt->execute([$user['id'], $total]);
    $order_id = (int)$pdo->lastInsertId();

    // Insere itens
    $oi = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?,?,?,?)");
    foreach ($items as $it) {
      $oi->execute([$order_id, $it['p']['id'], $it['qty'], $it['p']['price']]);
    }

    $pdo->commit();

    // Limpa carrinho da sessão
    $_SESSION['cart'] = [];

    // Mostra confirmação simples
    echo "<h2>Pedido confirmado!</h2>";
    echo "<p>Seu pedido <strong>#{$order_id}</strong> foi criado com status <strong>NEW</strong>.</p>";
    echo "<p>Total: <strong>".money($total)."</strong></p>";
    echo "<p><a class='btn-primary' href='?p=home'>Voltar para a Home</a></p>";
    return;

  } catch (Throwable $e) {
    $pdo->rollBack();
    echo "<p style='color:#b00'>Erro ao finalizar: ".htmlspecialchars($e->getMessage())."</p>";
  }
}
?>

<h2>Checkout</h2>
<p>Para o MVP, vamos simular o pagamento e gerar um pedido “NEW”.</p>

<form class="card" method="post">
  <div class="form-row">
    <input name="name" placeholder="Nome completo">
    <input name="birth" type="date" placeholder="Data de Nascimento">
  </div>
  <div class="form-row">
    <input name="address" placeholder="Endereço">
    <input name="document" placeholder="Documento (CPF/RG)">
  </div>
  <div class="form-row">
    <input name="email" type="email" placeholder="Email">
    <input name="phone" placeholder="Telefone">
  </div>
  <button class="btn-primary">Confirmar pedido</button>
</form>

<div class="card" style="margin-top:16px">
  <h3>Resumo</h3>
  <?php foreach($items as $it):
        $img = $it['p']['image_url'] ? BASE_URL.$it['p']['image_url'] : BASE_URL.'/assets/img/placeholder.jpg'; ?>
    <div style="display:grid;grid-template-columns:80px 1fr auto;gap:12px;align-items:center;padding:8px 0;border-bottom:1px solid #eee">
      <img src="<?= htmlspecialchars($img) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:8px">
      <div><?= htmlspecialchars($it['p']['title']) ?> (x<?= $it['qty'] ?>)</div>
      <div><?= money($it['sub']) ?></div>
    </div>
  <?php endforeach; ?>
  <h3 style="text-align:right">Total: <?= money($total) ?></h3>
</div>
