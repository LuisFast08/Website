<?php
session_start();
require 'db.php';  // Verbindung zur DB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unbekannt';
    $user_id = $_SESSION['user_id'] ?? null;

    if ($name && $email && $message) {
        // Prüfen ob E-Mail schon existiert (max 1 Nachricht pro E-Mail)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Log: E-Mail schon benutzt
            $logMsg = "Kontaktanfrage mit bereits verwendeter E-Mail: $email";
            $stmtLog = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
            $stmtLog->execute([
                ':log_type' => 'user_action',
                ':user_id' => $user_id,
                ':ip_address' => $ip,
                ':message' => $logMsg
            ]);

            // Fehler: E-Mail schon benutzt
            $msg = urlencode("Pro E-Mail kann nur eine Nachricht gesendet werden.");
            header("Location: index.php?error=1&error_msg=$msg#kontakt");
            exit;
        }

        // Nachricht speichern
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);

        // Log: Erfolgreiche Kontaktanfrage
        $logMsg = "Neue Kontaktanfrage von $name <$email>";
        $stmtLog = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
        $stmtLog->execute([
            ':log_type' => 'user_action',
            ':user_id' => $user_id,
            ':ip_address' => $ip,
            ':message' => $logMsg
        ]);

        // Erfolg: zurück mit Erfolgsmeldung und Anker
        header('Location: index.php?success=1#kontakt');
        exit;
    } else {
        // Log: Fehlende Eingaben
        $logMsg = "Fehlende Eingaben bei Kontaktanfrage";
        $stmtLog = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
        $stmtLog->execute([
            ':log_type' => 'user_action',
            ':user_id' => $user_id,
            ':ip_address' => $ip,
            ':message' => $logMsg
        ]);

        // Fehler: fehlende Eingaben
        $msg = urlencode("Bitte alle Felder ausfüllen.");
        header("Location: index.php?error=1&error_msg=$msg#kontakt");
        exit;
    }
} else {
    // Direktzugriff verhindern
    header('Location: index.php');
    exit;
}
