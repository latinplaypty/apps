<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';

start_session();

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = verify_user($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        }
        $error = 'Credenciales inválidas o cuenta vencida.';
    } elseif ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $expiresAt = $_POST['expires_at'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Debe completar usuario y contraseña.';
        } elseif ($password !== $confirm) {
            $error = 'Las contraseñas no coinciden.';
        } elseif (!$expiresAt) {
            $error = 'Debe indicar una fecha de vencimiento.';
        } elseif (find_user_by_username($username)) {
            $error = 'El usuario ya existe.';
        } else {
            try {
                create_user($username, $password, $expiresAt . ' 23:59:59');
                $success = 'Usuario creado. Ahora puede iniciar sesión.';
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        }
    }
}

if (current_user()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyProjector - Acceso</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <h1>EasyProjector</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="tabs">
            <button type="button" class="tab-button active" data-tab="login">Iniciar sesión</button>
            <button type="button" class="tab-button" data-tab="register">Crear usuario</button>
        </div>
        <div class="tab-content" id="tab-login">
            <form method="post">
                <input type="hidden" name="action" value="login">
                <label>Usuario
                    <input type="text" name="username" required>
                </label>
                <label>Contraseña
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="primary">Entrar</button>
            </form>
        </div>
        <div class="tab-content hidden" id="tab-register">
            <form method="post">
                <input type="hidden" name="action" value="register">
                <label>Usuario
                    <input type="text" name="username" required>
                </label>
                <label>Contraseña
                    <input type="password" name="password" required>
                </label>
                <label>Confirmar contraseña
                    <input type="password" name="confirm_password" required>
                </label>
                <label>Vence el
                    <input type="date" name="expires_at" required>
                </label>
                <button type="submit" class="primary">Crear usuario</button>
            </form>
        </div>
    </div>
    <script>
    const tabButtons = document.querySelectorAll('.tab-button');
    const contents = {
        login: document.getElementById('tab-login'),
        register: document.getElementById('tab-register')
    };
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            Object.values(contents).forEach(content => content.classList.add('hidden'));
            contents[button.dataset.tab].classList.remove('hidden');
        });
    });
    </script>
</body>
</html>
