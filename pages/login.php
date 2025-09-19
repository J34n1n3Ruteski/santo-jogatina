<?php
// pages/login.php

// Se enviou o formulário, trata o login antes de imprimir qualquer HTML
$login_error = null;
$next = $_GET['next'] ?? 'home'; // valor padrão se não vier "next" na URL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once __DIR__ . '/../app/functions.php';
  $pdo = db();

  // next pode vir do GET (na action) ou do POST (campo hidden); preferimos o POST
  $next = $_POST['next'] ?? ($_GET['next'] ?? 'home');

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
  $stmt->execute([$_POST['email']]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($u && password_verify($_POST['password'], $u['password_hash'])) {
    session_start();
    $_SESSION['user'] = $u;

    // Redireciona para a página desejada pós-login
    header('Location: ?p=' . urlencode($next));
    exit;
  } else {
    $login_error = 'Credenciais inválidas.';
  }
}
?>

<h2>Entrar</h2>

<form class="card" method="post" action="?p=login<?= isset($_GET['next']) ? '&next='.urlencode($_GET['next']) : '' ?>">
  <input name="email" type="email" placeholder="Email" required>
  <input name="password" type="password" placeholder="Senha" required>

  <?php if (isset($_GET['next'])): ?>
    <input type="hidden" name="next" value="<?= htmlspecialchars($_GET['next']) ?>">
  <?php endif; ?>

  <button class="btn-primary">Entrar</button>
</form>

<?php if ($login_error): ?>
  <p style="color:#b00"><?= htmlspecialchars($login_error) ?></p>
<?php endif; ?>

<p>Não tem conta? <a href="?p=register<?= isset($_GET['next']) ? '&next='.urlencode($_GET['next']) : '' ?>">Cadastre-se</a></p>
