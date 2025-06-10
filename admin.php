<?php
session_start();
require 'db.php';

// Login Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

// --- Aktionen ---

if ($action === 'add_video') {
    $youtube_link = trim($_POST['youtube_id'] ?? '');
    $lang = trim($_POST['lang'] ?? '');
    $title = trim($_POST['title'] ?? '');

    // YouTube-ID extrahieren (aus URL oder ID)
    preg_match('/(?:v=|\/)([0-9A-Za-z_-]{11})/', $youtube_link, $matches);
    $youtube_id = $matches[1] ?? '';

    if ($youtube_id && $lang && $title) {
        $stmt = $pdo->prepare("INSERT INTO videos (youtube_id, lang, title) VALUES (?, ?, ?)");
        $stmt->execute([$youtube_id, $lang, $title]);
    }
    header('Location: admin.php');
    exit;
}

if ($action === 'delete_video') {
    $id = (int)($_POST['video_id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin.php');
    exit;
}

if ($action === 'add_admin') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);
    }
    header('Location: admin.php');
    exit;
}

if ($action === 'delete_admin') {
    $id = (int)($_POST['admin_id'] ?? 0);
    if ($id && $id !== $userId) { // Nicht selbst löschen
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin.php');
    exit;
}

if ($action === 'delete_message') {
    $id = (int)($_POST['message_id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin.php');
    exit;
}

// --- Daten laden ---

$videos = $pdo->query("SELECT * FROM videos ORDER BY id DESC")->fetchAll();
$admins = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Bereich – Trucker für Christus</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function openTab(evt, tabName) {
      const tabs = document.querySelectorAll('.tab-content');
      tabs.forEach(t => t.classList.add('hidden'));

      const buttons = document.querySelectorAll('.tab-button');
      buttons.forEach(b => b.classList.remove('border-blue-600', 'text-blue-600', 'font-semibold'));

      document.getElementById(tabName).classList.remove('hidden');
      evt.currentTarget.classList.add('border-blue-600', 'text-blue-600', 'font-semibold');
    }
    window.addEventListener('DOMContentLoaded', () => {
      document.querySelector('.tab-button').click();
    });
  </script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col">

<header class="bg-gray-800 p-4 flex justify-between items-center">
  <h1 class="text-2xl font-bold">Admin Bereich</h1>
  <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-semibold">Logout</a>
</header>

<nav class="bg-gray-800 p-2 flex space-x-4 border-b border-gray-700">
  <button class="tab-button px-3 py-2 border-b-2 border-transparent hover:border-blue-600 hover:text-blue-600 font-medium" onclick="openTab(event, 'videos')">Videos</button>
  <button class="tab-button px-3 py-2 border-b-2 border-transparent hover:border-blue-600 hover:text-blue-600 font-medium" onclick="openTab(event, 'admins')">Admins</button>
  <button class="tab-button px-3 py-2 border-b-2 border-transparent hover:border-blue-600 hover:text-blue-600 font-medium" onclick="openTab(event, 'messages')">Kontakt Nachrichten</button>
</nav>

<main class="flex-grow p-6 max-w-7xl mx-auto">

  <!-- Videos Tab -->
  <section id="videos" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">Videos verwalten</h2>
    <form method="post" class="mb-6 max-w-lg bg-gray-800 p-4 rounded space-y-4">
      <input type="hidden" name="action" value="add_video" />
      <input name="youtube_id" placeholder="YouTube Video-Link einfügen" required
        class="w-full p-2 rounded bg-gray-700 text-white" />
      <input name="title" placeholder="Titel" required
        class="w-full p-2 rounded bg-gray-700 text-white" />
      <select name="lang" required class="w-full p-2 rounded bg-gray-700 text-white">
        <option value="" disabled selected>Sprache wählen</option>
        <option value="de">Deutsch</option>
        <option value="en">Englisch</option>
        <option value="ru">Russisch</option>
        <option value="pl">Polnisch</option>
      </select>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 py-2 rounded w-full font-semibold">Video hinzufügen</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full text-left border border-gray-700 rounded">
        <thead class="bg-gray-700">
          <tr>
            <th class="p-2 border border-gray-600">ID</th>
            <th class="p-2 border border-gray-600">YouTube ID</th>
            <th class="p-2 border border-gray-600">Titel</th>
            <th class="p-2 border border-gray-600">Sprache</th>
            <th class="p-2 border border-gray-600">Aktion</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($videos as $video): ?>
          <tr class="border border-gray-700 hover:bg-gray-800">
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($video['id'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($video['youtube_id'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($video['title'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($video['lang'])?></td>
            <td class="p-2 border border-gray-600">
              <form method="post" onsubmit="return confirm('Video löschen?');" class="inline">
                <input type="hidden" name="action" value="delete_video" />
                <input type="hidden" name="video_id" value="<?=htmlspecialchars($video['id'])?>" />
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm">Löschen</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(count($videos) === 0): ?>
          <tr><td colspan="5" class="p-4 text-center text-gray-400">Keine Videos gefunden.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Admins Tab -->
  <section id="admins" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">Admins verwalten</h2>
    <form method="post" class="mb-6 max-w-md bg-gray-800 p-4 rounded space-y-4">
      <input type="hidden" name="action" value="add_admin" />
      <input name="username" placeholder="Benutzername" required
        class="w-full p-2 rounded bg-gray-700 text-white" />
      <input name="password" type="password" placeholder="Passwort" required
        class="w-full p-2 rounded bg-gray-700 text-white" />
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 py-2 rounded w-full font-semibold">Admin hinzufügen</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full text-left border border-gray-700 rounded">
        <thead class="bg-gray-700">
          <tr>
            <th class="p-2 border border-gray-600">ID</th>
            <th class="p-2 border border-gray-600">Benutzername</th>
            <th class="p-2 border border-gray-600">Aktion</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $admin): ?>
          <tr class="border border-gray-700 hover:bg-gray-800">
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($admin['id'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($admin['username'])?></td>
            <td class="p-2 border border-gray-600">
              <?php if ($admin['id'] !== $userId): ?>
              <form method="post" onsubmit="return confirm('Admin löschen?');" class="inline">
                <input type="hidden" name="action" value="delete_admin" />
                <input type="hidden" name="admin_id" value="<?=htmlspecialchars($admin['id'])?>" />
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm">Löschen</button>
              </form>
              <?php else: ?>
              <span class="text-gray-400 text-sm">Selbst</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(count($admins) === 0): ?>
          <tr><td colspan="3" class="p-4 text-center text-gray-400">Keine Admins gefunden.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Kontakt Nachrichten Tab -->
  <section id="messages" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">Kontakt Nachrichten</h2>
    <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
      <table class="w-full text-left border border-gray-700 rounded table-auto">
        <thead class="bg-gray-700">
          <tr>
            <th class="p-2 border border-gray-600">ID</th>
            <th class="p-2 border border-gray-600">Name</th>
            <th class="p-2 border border-gray-600">E-Mail</th>
            <th class="p-2 border border-gray-600">Nachricht</th>
            <th class="p-2 border border-gray-600">Zeitpunkt</th>
            <th class="p-2 border border-gray-600">Aktion</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($messages as $message): ?>
          <tr class="border border-gray-700 hover:bg-gray-800 align-top">
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($message['id'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($message['name'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($message['email'])?></td>
            <td class="p-2 border border-gray-600 whitespace-pre-line max-w-xl"><?=htmlspecialchars($message['message'])?></td>
            <td class="p-2 border border-gray-600"><?=htmlspecialchars($message['created_at'])?></td>
            <td class="p-2 border border-gray-600">
              <form method="post" onsubmit="return confirm('Nachricht löschen?');" class="inline">
                <input type="hidden" name="action" value="delete_message" />
                <input type="hidden" name="message_id" value="<?=htmlspecialchars($message['id'])?>" />
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm">Löschen</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(count($messages) === 0): ?>
          <tr><td colspan="6" class="p-4 text-center text-gray-400">Keine Nachrichten gefunden.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

</main>

<footer class="bg-gray-800 p-4 text-center text-gray-400 text-sm">
  &copy; <?=date('Y')?> Trucker für Christus
</footer>

</body>
</html>
