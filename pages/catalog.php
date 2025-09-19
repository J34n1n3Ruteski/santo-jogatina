<?php
$pdo = db();
$cat = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

if ($cat_id) {
  $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id=? ORDER BY title");
  $stmt->execute([$cat_id]);
  $prod = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $prod = $pdo->query("SELECT * FROM products ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<h2>Catálogo</h2>

<form method="get" class="form-row" style="margin-bottom:12px">
  <input type="hidden" name="p" value="catalog">
  <select name="cat" onchange="this.form.submit()">
    <option value="0">Todas as categorias</option>
    <?php foreach($cat as $c): ?>
      <option value="<?= $c['id'] ?>" <?= $cat_id==$c['id']?'selected':'' ?>>
        <?= htmlspecialchars($c['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</form>

<div class="grid">
  <?php foreach($prod as $p): 
        $img = $p['image_url'] ? BASE_URL.$p['image_url'] : BASE_URL.'/assets/img/placeholder.jpg'; ?>
    <div class="card">
      <img src="<?= htmlspecialchars($img) ?>" alt="">
      <h3><?= htmlspecialchars($p['title']) ?></h3>
      <div class="price"><?= money($p['price']) ?></div>
      <a class="btn-primary" href="?p=product&id=<?= (int)$p['id'] ?>">Ver</a>
    </div>
  <?php endforeach; ?>
</div>
