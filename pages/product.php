<?php
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$p){ echo "<p>Produto não encontrado.</p>"; return; }

$img = $p['image_url'] ? BASE_URL.$p['image_url'] : BASE_URL.'/assets/img/placeholder.jpg';
?>
<div class="card" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <img src="<?= htmlspecialchars($img) ?>" alt="">
  <div>
    <h2><?= htmlspecialchars($p['title']) ?></h2>
    <div class="price"><?= money($p['price']) ?></div>
    <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
    <form method="post" action="?p=cart&action=add&id=<?= (int)$p['id'] ?>">
      <input type="number" name="qty" min="1" value="1">
      <button class="btn-primary" type="submit">Adicionar ao carrinho</button>
    </form>
  </div>
</div>
