<?php
/**
 * ============================================================
 *  دانش‌یار - API استریم ایجنت‌های چتی
 *  برای: tutor, advisor, major
 *  ورودی: agent key + پیام (متن یا تصویر)
 *  خروجی: SSE stream
 * ============================================================
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai.php';

@set_time_limit(300);
@ini_set('memory_limit', '256M');
@ignore_user_abort(false);

$user = current_user();
if (!$user) {
    sse_error('برای استفاده، اول وارد حساب کاربری خود شو.');
    exit;
}
if (is_banned($user)) {
    sse_error('حساب شما مسدود شده است.');
    exit;
}

// CSRF
$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) {
    sse_error('توکن امنیتی نامعتبر است. صفحه را رفرش کن.');
    exit;
}

// سهمیه
$quota = can_send_message($user);
if (!$quota['ok']) {
    sse_quota_block($quota);
    exit;
}

$agent = $_POST['agent'] ?? '';
$validAgents = ['tutor', 'advisor', 'major'];
if (!in_array($agent, $validAgents, true)) {
    sse_error('ایجنت نامعتبر.');
    exit;
}

$message = trim((string)($_POST['message'] ?? ''));
if ($message === '') {
    sse_error('پیام خالی است.');
    exit;
}

// ساخت پرامپت پایه + extension ایجنت
$basePrompt = DaneshyarAI::systemPrompt(null, $user, '', $message);
$agentExtension = agent_specific_prompt($agent, $user, '');
$systemPrompt = $basePrompt . $agentExtension;

// ساخت پیام‌ها
$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => $message],
];

// افزایش شمارنده
increment_message_counters($user['id']);

// استریم
DaneshyarAI::streamChat($messages, null, null, null, function($data) {
    echo $data;
    @ob_flush();
    @flush();
});

/* ============================================================
   توابع SSE کمکی
   ============================================================ */

function sse_error($msg) {
    echo "data: " . json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE) . "\n\n";
    @ob_flush();
    @flush();
}

function sse_quota_block($quota) {
    $msg = 'سهمیه‌ات تموم شده.';
    if (($quota['reason'] ?? '') === 'free_limit') {
        $msg = 'سهمیه رایگان امروزت تموم شد. برای استفاده نامحدود، اشتراک بخر.';
    }
    echo "data: " . json_encode(['error' => $msg, 'reason' => $quota['reason'] ?? ''], JSON_UNESCAPED_UNICODE) . "\n\n";
    @ob_flush();
    @flush();
}
