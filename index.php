<?php
session_start();

header('Content-Type: text/html; charset=utf-8');

require_once 'db.php'; // Datenbank-Verbindung laden

// Logging des Seitenzugriffs
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unbekannt';
$message = 'Seitenzugriff auf index.php';

try {
    $stmt = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
    $stmt->execute([
        ':log_type' => 'access',
        ':user_id' => $_SESSION['user_id'] ?? null,
        ':ip_address' => $ip,
        ':message' => $message,
    ]);
} catch (Exception $e) {
}

$success = isset($_GET['success']);
$error = isset($_GET['error']);
$error_msg = $_GET['error_msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trucker f�r Christus</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
  </style>
</head>
<body class="bg-gray-900 text-white font-sans">
  <header class="fixed top-0 left-0 right-0 flex justify-end p-4 bg-black bg-opacity-60 z-50">
    <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-lg font-semibold">Login</a>
  </header>

  <section
    class="relative h-screen bg-cover bg-center"
    style="background-image: url('https://i.postimg.cc/qRLZmcQX/truck.png');"
  >
    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="text-center px-6">
        <h1 class="text-4xl md:text-6xl font-bold">Trucker f�r Christus</h1>
        <p class="mt-4 text-lg md:text-2xl">Glaube auf Achse � Gemeinschaft f�r LKW-Fahrer</p>

        <button id="scrollToKontakt" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-lg cursor-pointer">
          Kontakt aufnehmen
        </button>

        <a
          href="videos.html"
          class="mt-4 ml-2 inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl text-lg"
          >Predigten ansehen</a
        >
      </div>
    </div>
  </section>

  <section class="py-16 px-6 md:px-20 bg-gray-800">
    <h2 class="text-3xl font-bold mb-6">Unsere Mission</h2>
    <p class="text-lg leading-relaxed">
      Wir bringen Hoffnung und Glauben auf die Stra�en Europas. Unsere Mission ist es, LKW-Fahrer mit dem Evangelium zu erreichen, Gemeinschaft zu f�rdern und f�r sie da zu sein � rund um die Uhr.
    </p>
  </section>

  <section class="py-10 px-6 text-center bg-blue-800">
    <blockquote class="text-xl italic">
      �Denn der Herr beh�tet deinen Ausgang und Eingang von nun an bis in Ewigkeit.� � Psalm 121,8
    </blockquote>
  </section>

  <section id="kontakt" class="py-16 px-6 md:px-20 bg-gray-800">
    <h2 class="text-3xl font-bold mb-6">Kontakt</h2>
    <form action="contact_submit.php" method="post" class="grid gap-4 max-w-xl">
      <input
        type="text"
        name="name"
        placeholder="Name"
        required
        class="p-3 rounded bg-gray-700 text-white"
      />
      <input
        type="email"
        name="email"
        placeholder="E-Mail"
        required
        class="p-3 rounded bg-gray-700 text-white"
      />
      <textarea
        name="message"
        rows="4"
        placeholder="Nachricht"
        required
        class="p-3 rounded bg-gray-700 text-white"
      ></textarea>
      <button
        type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl"
      >
        Senden
      </button>

      <?php if ($success): ?>
        <p class="mt-2 text-green-500 font-semibold">Erfolgreich gesendet</p>
      <?php elseif ($error): ?>
        <p class="mt-2 text-red-500 font-semibold"><?= htmlspecialchars($error_msg) ?: "Bitte alle Felder korrekt ausf�llen oder E-Mail wurde bereits verwendet." ?></p>
      <?php endif; ?>
    </form>
  </section>

  <footer class="text-center py-6 bg-gray-900 text-sm text-gray-400">
    &copy; 2025 Trucker f�r Christus � Alle Rechte vorbehalten.
  </footer>

  <script>
    function isMobileDevice() {
      return /Mobi|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function smoothScrollTo(element, duration = 600) {
      const headerHeight = document.querySelector('header').offsetHeight;
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
      const targetPosition = element.getBoundingClientRect().top + scrollTop - headerHeight;

      if (isMobileDevice() && 'scrollBehavior' in document.documentElement.style) {
        
        window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        return;
      }

      const startPosition = scrollTop;
      const distance = targetPosition - startPosition;
      let startTime = null;

      function animation(currentTime) {
        if (!startTime) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);
        window.scrollTo(0, run);
        if (timeElapsed < duration) {
          requestAnimationFrame(animation);
        }
      }

      function easeInOutQuad(t, b, c, d) {
        t /= d / 2;
        if (t < 1) return c / 2 * t * t + b;
        t--;
        return -c / 2 * (t * (t - 2) - 1) + b;
      }

      requestAnimationFrame(animation);
    }

    document.getElementById('scrollToKontakt').addEventListener('click', () => {
      smoothScrollTo(document.getElementById('kontakt'));
    });

    window.addEventListener('load', () => {
      if (
        window.location.search.includes('success=1') ||
        window.location.search.includes('error=1')
      ) {
        setTimeout(() => {
          smoothScrollTo(document.getElementById('kontakt'));
        }, 100);
      }
    });
  </script>
</body>
</html>
