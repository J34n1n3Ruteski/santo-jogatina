<?php
// pages/register.php

require_once __DIR__ . '/../app/functions.php';

$register_error = null;
$next = $_GET['next'] ?? 'home'; // valor padrão

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pdo = db();

  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $next  = $_POST['next'] ?? ($_GET['next'] ?? 'home');

  if (!$name || !$email || !$pass) {
    $register_error = 'Preencha todos os campos.';
  } else {
    try {
      // Verifica se já existe email
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $register_error = 'Este email já está cadastrado.';
      } else {
        // Cria usuário
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?)");
        $stmt->execute([$name, $email, $hash]);

        // Pega o usuário recém-criado
        $id = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        // Loga automaticamente
        session_start();
        $_SESSION['user'] = $u;

        // Redireciona
        header('Location: ?p=' . urlencode($next));
        exit;
      }
    } catch (Throwable $e) {
      $register_error = "Erro ao registrar: " . $e->getMessage();
    }
  }
}
?>

<h2>Criar Conta</h2>

<form class="card" method="post" action="?p=register<?= isset($_GET['next']) ? '&next='.urlencode($_GET['next']) : '' ?>">
  <input name="name" placeholder="Nome completo" required>
  <input name="email" type="email" placeholder="Email" required>
  <input name="password" type="password" placeholder="Senha" required>

  <?php if (isset($_GET['next'])): ?>
    <input type="hidden" name="next" value="<?= htmlspecialchars($_GET['next']) ?>">
  <?php endif; ?>

  <button class="btn-primary">Cadastrar</button>
</form>

<?php if ($register_error): ?>
  <p style="color:#b00"><?= htmlspecialchars($register_error) ?></p>
<?php endif; ?>

<p>Já tem conta? <a href="?p=login<?= isset($_GET['next']) ? '&next='.urlencode($_GET['next']) : '' ?>">Entrar</a></p>

<!-- Se alguém for para o checkout sem login → redireciona para ?p=login&next=checkout.
No login → se der certo, volta para checkout.
No link “Não tem conta? Cadastre-se” → já vai para ?p=register&next=checkout.
Ao registrar → o usuário entra logado e volta direto para o checkout.
 Assim, ninguém fica travado: login e cadastro ambos respeitam o next. -->