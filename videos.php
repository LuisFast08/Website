<?php
session_start();
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unbekannt';
$user_id = $_SESSION['user_id'] ?? null;

try {
    $stmt = $pdo->query("SELECT id, youtube_id, lang, title FROM videos ORDER BY id DESC");
    $videos = $stmt->fetchAll();

    $logMsg = "Videos abgerufen, Anzahl: " . count($videos);
    $stmtLog = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
    $stmtLog->execute([
        ':log_type' => 'access',
        ':user_id' => $user_id,
        ':ip_address' => $ip,
        ':message' => $logMsg
    ]);

    echo json_encode($videos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    $logMsg = "Fehler beim Laden der Videos: " . $e->getMessage();
    $stmtLog = $pdo->prepare("INSERT INTO logs (log_type, user_id, ip_address, message) VALUES (:log_type, :user_id, :ip_address, :message)");
    $stmtLog->execute([
        ':log_type' => 'access',
        ':user_id' => $user_id,
        ':ip_address' => $ip,
        ':message' => $logMsg
    ]);

    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Laden der Videos.']);
}
