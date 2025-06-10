<?php
session_start();
require 'db.php';

$error = '';

function logEvent($pdo, $log_type, $user_id, $ip_address, $message) {
    try {
        $stmtLog = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
        $stmtLog->execute([
            ':log_type' => $log_type,
            ':user_id' => $user_id,
            ':ip_address' => $ip_address,
            ':message' => $message
        ]);
    } catch (Exception $e) {
        
        error_log('Logging error: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unbekannt';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            logEvent($pdo, 'user_action', $user['id'], $ip, "Erfolgreicher Login f�r Benutzer '$username'");

            header('Location: admin.php');
            exit;
        } else {
            $error = 'Benutzername oder Passwort falsch';
            $user_id = $user['id'] ?? null;
            logEvent($pdo, 'user_action', $user_id, $ip, "Fehlgeschlagener Login-Versuch f�r Benutzer '$username'");
        }
    } else {
        $error = 'Bitte Benutzername und Passwort eingeben';
        logEvent($pdo, 'user_action', null, $ip, "Fehlende Eingaben beim Login");
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Trucker f�r Christus</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen font-sans">
  <form method="post" class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
    <?php if ($error): ?>
      <div class="bg-red-600 text-white p-2 rounded mb-4"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <input name="username" type="text" placeholder="Benutzername" required
      class="w-full p-3 mb-4 rounded bg-gray-700 text-white" />
    <input name="password" type="password" placeholder="Passwort" required
      class="w-full p-3 mb-6 rounded bg-gray-700 text-white" />
    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded text-white font-semibold">Einloggen</button>

    <a href="index.html" class="block mt-4 text-center text-blue-400 hover:underline">? Zur�ck zur Startseite</a>
  </form>
</body>
</html>
