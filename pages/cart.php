<?php
session_start();
$cart = $_SESSION['cart'] ?? [];

$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'add' && $id) {
  $qty = max(1, (int)($_POST['qty'] ?? 1));
  $cart[$id] = ($cart[$id] ?? 0) + $qty;
  $_SESSION['cart'] = $cart;
  header("Location: ?p=cart"); exit;
}
if ($action === 'remove' && $id) {
  unset($cart[$id]); $_SESSION['cart'] = $cart;
  header("Location: ?p=cart"); exit;
}
if ($action === 'update' && $_SERVER['REQUEST_METHOD']==='POST') {
  foreach ($_POST['qty'] as $pid => $q) {
    $q = max(1, (int)$q);
    if (isset($cart[$pid])) {
      $cart[$pid] = $q;
    }
  }
  $_SESSION['cart'] = $cart;
  header("Location: ?p=cart"); exit;
}

$pdo = db();
$items = []; $total = 0.0;

if ($cart) {
  $ids = implode(',', array_map('intval', array_keys($cart)));
  $rows = $pdo->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
  foreach ($rows as $r) {
    $q = $cart[$r['id']];
    $sub = $q * (float)$r['price'];
    $total += $sub;
    $items[] = ['p'=>$r,'qty'=>$q,'sub'=>$sub];
  }
}
?>
<h2>Carrinho</h2>
<?php if (!$items): ?>
  <p>Seu carrinho está vazio.</p>
<?php else: ?>
  <div class="card">
<form method="post" action="?p=cart&action=update">
  <?php foreach($items as $it):
        $img = $it['p']['image_url'] ? BASE_URL.$it['p']['image_url'] : BASE_URL.'/assets/img/placeholder.jpg'; ?>
    <div style="display:grid;grid-template-columns:80px 1fr 100px auto auto;gap:12px;align-items:center;padding:8px 0;border-bottom:1px solid #eee">
      <img src="<?= htmlspecialchars($img) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:8px">
      <div><?= htmlspecialchars($it['p']['title']) ?></div>
      <input type="number" name="qty[<?= (int)$it['p']['id'] ?>]" min="1" value="<?= (int)$it['qty'] ?>" style="width:70px">
      <div><?= money($it['sub']) ?></div>
      <a href="?p=cart&action=remove&id=<?= (int)$it['p']['id'] ?>">remover</a>
    </div>
  <?php endforeach; ?>
  <h3 style="text-align:right">Total: <?= money($total) ?></h3>
  <div style="text-align:right;margin-top:8px">
    <button class="btn-primary" type="submit">Atualizar quantidades</button>
    <a class="btn-primary" href="?p=checkout">Finalizar compra</a>
  </div>
</form>
  </div>
<?php endif; ?>
