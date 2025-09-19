<section class="hero">
  <h1>Bem-vindos ao Santo da Jogatina</h1>
  <p>Jogos de cartas, tabuleiro e eletrônicos — tudo num só lugar.</p>
  <a class="btn-primary" href="?p=catalog">Ver catálogo</a>
</section>

<?php
$pdo  = db();
$prod = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="grid">
  <?php foreach ($prod as $p): ?>
    <div class="card">
      <?php
$img = $p['image_url']
  ? BASE_URL . $p['image_url']                 // ex: /assets/img/baralho.jpg -> http://localhost/santo-jogatina/public/...
  : BASE_URL . '/assets/img/placeholder.jpg';
?>
<img src="<?= htmlspecialchars($img) ?>" alt="">
      <h3><?= htmlspecialchars($p['title']) ?></h3>
      <div class="price"><?= money($p['price']) ?></div>
      <a class="btn-primary" href="?p=product&id=<?= (int)$p['id'] ?>">Ver</a>
    </div>
  <?php endforeach; ?>
</div>
