<?php
/**
 * دانش‌یار - متادیتای چت (تغییر کتاب، حذف چت)
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF']);
    exit;
}

$action = $_POST['action'] ?? '';
$chatId = (int)($_POST['chat_id'] ?? 0);

if ($action === 'update_meta' && $chatId > 0) {
    $bookId = $_POST['book_id'] ?? '';
    $bookId = ($bookId === '' || $bookId === '0') ? null : (int)$bookId;
    try {
        $st = db()->prepare("UPDATE chats SET book_id=? WHERE id=? AND user_id=?");
        $st->execute([$bookId, $chatId, $user['id']]);
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'DB error']);
    }
    exit;
}

if ($action === 'delete' && $chatId > 0) {
    try {
        $st = db()->prepare("DELETE FROM chats WHERE id=? AND user_id=?");
        $st->execute([$chatId, $user['id']]);
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'DB error']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Bad request']);
