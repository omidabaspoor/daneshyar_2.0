<?php
/**
 * دانش‌یار - چت درسی (معلم سریع)
 * طراحی جدید، شیشه‌ای، موبایل‌فرست
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/chat.php'));
}
if (is_banned($user)) {
    redirect(BASE_URL . '/logout.php');
}

$page = 'chat';
$pageTitle = 'معلم سریع';
$bodyClass = 'has-chat';
$hideSiteChrome = true;
$fullWidthMain = true;

// کتاب‌های قابل دسترس
$allBooks = function_exists('get_accessible_books_for_user') ? get_accessible_books_for_user($user) : [];

// چت‌های کاربر
$chats = function_exists('get_user_chats') ? get_user_chats($user['id']) : [];

// چت فعال
$activeChatId = (int)($_GET['c'] ?? 0);
$activeChat = null;
$messages = [];
if ($activeChatId > 0 && function_exists('get_chat')) {
    $activeChat = get_chat($activeChatId, $user['id']);
    if ($activeChat) {
        $messages = function_exists('chat_messages') ? chat_messages($activeChatId) : [];
    } else {
        $activeChatId = 0;
    }
}

$check = can_send_message($user);

include __DIR__ . '/includes/header.php';
?>

<div class="chat-shell">
  <!-- ══════ سایدبار (لیست چت‌ها) ══════ -->
  <aside class="chat-sidebar glass" id="chatSidebar">
    <div class="chat-sidebar-head">
      <a href="<?= BASE_URL ?>/dashboard.php" class="chat-back"><?= icon('arrow-right') ?> <span>بازگشت</span></a>
      <button class="chat-new-btn" id="chatNewBtn" title="چت جدید"><?= icon('plus') ?> <span>چت جدید</span></button>
    </div>

    <div class="chat-sidebar-search">
      <input type="text" id="chatSearch" placeholder="جست‌وجو در گفت‌وگوها..." autocomplete="off">
    </div>

    <div class="chat-sidebar-list" id="chatList">
      <?php if (empty($chats)): ?>
        <div class="chat-empty-state">
          <div class="chat-empty-icon"><?= icon('chat') ?></div>
          <div class="chat-empty-title">هنوز گفت‌وگویی نداری</div>
          <div class="chat-empty-desc">اولین سؤال درسی‌ات رو بپرس تا شروع کنیم</div>
        </div>
      <?php else: ?>
        <?php foreach ($chats as $c): ?>
          <a href="<?= BASE_URL ?>/chat.php?c=<?= (int)$c['id'] ?>" class="chat-list-item <?= $activeChatId === (int)$c['id'] ? 'is-active' : '' ?>">
            <div class="chat-list-item-ico"><?= icon('chat') ?></div>
            <div class="chat-list-item-body">
              <div class="chat-list-item-title"><?= e($c['title'] ?: 'گفت‌وگوی جدید') ?></div>
              <div class="chat-list-item-meta">
                <span class="chat-list-item-time"><?= e(date_fa_short($c['updated_at'])) ?></span>
                <?php if (!empty($c['book_title'])): ?>
                  <span class="chat-list-item-book"><?= e($c['book_title']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="chat-sidebar-foot">
      <a href="<?= BASE_URL ?>/profile.php" class="chat-foot-user">
        <div class="chat-foot-avatar"><?= e(mb_substr($user['first_name'], 0, 1)) ?></div>
        <div class="chat-foot-info">
          <div class="chat-foot-name"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></div>
          <div class="chat-foot-grade">پایه <?= num_fa($user['grade']) ?> · <?= e(major_label($user['major'] ?? 'math')) ?></div>
        </div>
      </a>
    </div>
  </aside>

  <!-- ══════ محتوای چت ══════ -->
  <main class="chat-main">
    <!-- هدر داخلی -->
    <div class="chat-topbar">
      <button class="chat-menu-btn" id="chatMenuBtn" aria-label="منو"><?= icon('menu') ?></button>
      <div class="chat-topbar-title" id="chatTitle">
        <?= $activeChat ? e($activeChat['title'] ?: 'گفت‌وگوی جدید') : 'معلم سریع' ?>
      </div>
      <div class="chat-topbar-actions">
        <?php if (!empty($allBooks)): ?>
        <select class="chat-book-select" id="chatBookSelect">
          <option value="">بدون کتاب خاص</option>
          <?php foreach ($allBooks as $b): ?>
            <option value="<?= (int)$b['id'] ?>" <?= (!empty($activeChat['book_id']) && (int)$activeChat['book_id'] === (int)$b['id']) ? 'selected' : '' ?>>
              <?= e($b['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button class="chat-icon-btn" id="chatDeleteBtn" title="پاک کردن گفت‌وگو" style="display: <?= $activeChatId ? 'grid' : 'none' ?>">
          <?= icon('trash') ?>
        </button>
      </div>
    </div>

    <!-- پیام‌ها -->
    <div class="chat-messages" id="chatMessages">
      <?php if (!$activeChatId): ?>
        <div class="chat-welcome">
          <div class="chat-welcome-icon"><?= icon('logo') ?></div>
          <h2>سلام <?= e($user['first_name']) ?> 👋</h2>
          <p>هر سؤال درسی داری، بپرس. عکس یا PDF هم می‌تونی بفرستی.</p>
          <div class="chat-welcome-suggestions">
            <button class="chat-suggestion" data-suggest="چطوری مشتق رو قدم به قدم یاد بگیرم؟">
              <?= icon('sparkle') ?> چطوری مشتق رو یاد بگیرم؟
            </button>
            <button class="chat-suggestion" data-suggest="فرمول‌های مثلثات رو یکجا بده">
              <?= icon('sparkle') ?> فرمول‌های مثلثات
            </button>
            <button class="chat-suggestion" data-suggest="یه خلاصه از جنگ صفین بده">
              <?= icon('sparkle') ?> خلاصهٔ جنگ صفین
            </button>
            <button class="chat-suggestion" data-suggest="چرا آسمان آبیه؟">
              <?= icon('sparkle') ?> چرا آسمان آبیه؟
            </button>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($messages as $m): ?>
          <?php if ($m['role'] === 'user'): ?>
            <div class="msg msg-user">
              <div class="msg-bub">
                <?php if (!empty($m['attachment'])): ?>
                  <div class="msg-attachment"><?= icon('image') ?> فایل پیوست</div>
                <?php endif; ?>
                <div class="msg-text"><?= nl2br(e($m['content'])) ?></div>
              </div>
            </div>
          <?php else: ?>
            <div class="msg msg-ai">
              <div class="msg-avatar"><?= icon('logo') ?></div>
              <div class="msg-bub">
                <div class="msg-text"><?= nl2br(e($m['content'])) ?></div>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ══════ بنر سهمیه ══════ -->
    <?php if (!$check['ok']): ?>
      <div class="chat-quota-banner">
        <?= icon('warning') ?>
        <div>
          <b>سهمیه‌ات تموم شده</b>
          <span>برای ادامه، اشتراک بخر</span>
        </div>
        <a href="<?= BASE_URL ?>/pricing.php" class="chat-quota-cta">خرید اشتراک</a>
      </div>
    <?php endif; ?>

    <!-- ══════ ورودی ══════ -->
    <form class="chat-input-wrap" id="chatForm" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="chat_id" id="chatIdInput" value="<?= (int)$activeChatId ?>">
      <input type="hidden" name="book_id" id="bookIdInput" value="<?= e($activeChat['book_id'] ?? '') ?>">

      <div class="chat-input-row">
        <button type="button" class="chat-input-icon" id="attachBtn" title="پیوست عکس">
          <?= icon('attach') ?>
        </button>
        <input type="file" name="attachment" id="attachInput" accept="image/*" style="display:none">

        <div class="chat-input-grow">
          <textarea name="message" id="chatInput" placeholder="سؤال درسی‌ات رو بنویس..." rows="1" required <?= !$check['ok'] ? 'disabled' : '' ?>></textarea>
          <div class="chat-input-preview" id="attachPreview" style="display:none"></div>
        </div>

        <button type="submit" class="chat-send-btn" id="chatSendBtn" <?= !$check['ok'] ? 'disabled' : '' ?>>
          <?= icon('send') ?>
        </button>
      </div>
    </form>
  </main>
</div>

<style>
.chat-shell {
  display: grid;
  grid-template-columns: 280px 1fr;
  height: 100dvh;
  background: var(--bg-0);
}

/* ════ سایدبار ════ */
.chat-sidebar {
  display: flex;
  flex-direction: column;
  border-radius: 0;
  border-left: 1px solid var(--border);
  border-top: 0;
  border-right: 0;
  border-bottom: 0;
  background: linear-gradient(160deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
  backdrop-filter: blur(20px);
  height: 100dvh;
  overflow: hidden;
}

.chat-sidebar-head {
  padding: 14px 14px 10px;
  display: flex;
  gap: 8px;
  border-bottom: 1px solid var(--border);
}

.chat-back {
  flex: 1;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--text-dim);
  text-decoration: none;
  font-size: 12.5px;
  font-weight: 700;
}
.chat-back:hover { background: rgba(255,255,255,0.07); color: var(--text); }

.chat-new-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  color: #1a0e05;
  border: none;
  border-radius: 10px;
  font-family: inherit;
  font-size: 12.5px;
  font-weight: 800;
  cursor: pointer;
}
.chat-new-btn:hover { transform: translateY(-1px); }

.chat-sidebar-search {
  padding: 12px 14px 8px;
}
.chat-sidebar-search input {
  width: 100%;
  padding: 9px 12px;
  background: rgba(0,0,0,0.3);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--text);
  font-family: inherit;
  font-size: 12.5px;
}
.chat-sidebar-search input:focus {
  outline: none;
  border-color: var(--orange);
}

.chat-sidebar-list {
  flex: 1;
  overflow-y: auto;
  padding: 6px 8px 12px;
  -webkit-overflow-scrolling: touch;
}

.chat-list-item {
  display: flex;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  text-decoration: none;
  color: inherit;
  margin-bottom: 4px;
  transition: 0.2s;
}
.chat-list-item:hover { background: rgba(255,255,255,0.04); }
.chat-list-item.is-active {
  background: linear-gradient(135deg, rgba(235,124,42,0.18), rgba(235,124,42,0.04));
  border: 1px solid rgba(235,124,42,0.32);
  padding: 9px 11px;
}
.chat-list-item-ico {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: rgba(255,255,255,0.05);
  display: grid;
  place-items: center;
  color: var(--text-dim);
  flex-shrink: 0;
}
.chat-list-item.is-active .chat-list-item-ico {
  background: rgba(235,124,42,0.18);
  color: var(--orange);
}
.chat-list-item-ico .ico { width: 16px; height: 16px; }

.chat-list-item-body { flex: 1; min-width: 0; }
.chat-list-item-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 3px;
}
.chat-list-item-meta {
  font-size: 11px;
  color: var(--text-muted);
  display: flex;
  gap: 6px;
  align-items: center;
}
.chat-list-item-book {
  font-size: 10px;
  padding: 1px 6px;
  background: rgba(56,217,169,0.12);
  border-radius: 999px;
  color: #a3f0d3;
}

.chat-empty-state {
  text-align: center;
  padding: 30px 16px;
  color: var(--text-dim);
}
.chat-empty-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 12px;
  border-radius: 50%;
  background: rgba(255,255,255,0.04);
  display: grid;
  place-items: center;
  color: var(--text-muted);
}
.chat-empty-icon .ico { width: 28px; height: 28px; }
.chat-empty-title { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
.chat-empty-desc { font-size: 12px; line-height: 1.7; }

.chat-sidebar-foot {
  padding: 12px;
  border-top: 1px solid var(--border);
}
.chat-foot-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: 12px;
  background: rgba(255,255,255,0.04);
  text-decoration: none;
  color: inherit;
}
.chat-foot-user:hover { background: rgba(255,255,255,0.07); }
.chat-foot-avatar {
  width: 36px;
  height: 36px;
  border-radius: 11px;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  display: grid;
  place-items: center;
  color: #1a0e05;
  font-weight: 900;
  flex-shrink: 0;
}
.chat-foot-info { min-width: 0; }
.chat-foot-name {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.chat-foot-grade {
  font-size: 10.5px;
  color: var(--text-muted);
}

/* ════ محتوای چت ════ */
.chat-main {
  display: flex;
  flex-direction: column;
  height: 100dvh;
  overflow: hidden;
  background:
    radial-gradient(ellipse 800px 500px at 50% -10%, rgba(235,124,42,0.10), transparent 60%),
    var(--bg-0);
}

.chat-topbar {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  background: rgba(255,255,255,0.03);
  border-bottom: 1px solid var(--border);
  backdrop-filter: blur(20px);
  z-index: 2;
}

.chat-menu-btn {
  width: 38px;
  height: 38px;
  border-radius: 11px;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border);
  color: var(--text);
  cursor: pointer;
  display: none;
  place-items: center;
}

.chat-topbar-title {
  flex: 1;
  font-size: 15px;
  font-weight: 800;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;
}

.chat-topbar-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.chat-book-select {
  padding: 8px 12px;
  background: rgba(0,0,0,0.3);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--text);
  font-family: inherit;
  font-size: 12px;
  font-weight: 600;
  max-width: 220px;
}
.chat-book-select:focus { outline: none; border-color: var(--orange); }

.chat-icon-btn {
  width: 38px;
  height: 38px;
  border-radius: 11px;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border);
  color: var(--text-dim);
  cursor: pointer;
  display: grid;
  place-items: center;
}
.chat-icon-btn:hover { color: var(--orange); }

/* ════ پیام‌ها ════ */
.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px 18px 12px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  -webkit-overflow-scrolling: touch;
}

.chat-welcome {
  text-align: center;
  margin: auto;
  max-width: 460px;
  padding: 30px 16px;
}
.chat-welcome-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 18px;
  border-radius: 20px;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  display: grid;
  place-items: center;
  color: #1a0e05;
  box-shadow: 0 10px 30px rgba(235,124,42,0.4);
}
.chat-welcome-icon .ico { width: 36px; height: 36px; }
.chat-welcome h2 {
  font-size: 22px;
  font-weight: 800;
  margin-bottom: 8px;
}
.chat-welcome p {
  color: var(--text-dim);
  font-size: 14px;
  line-height: 1.85;
  margin-bottom: 22px;
}

.chat-welcome-suggestions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.chat-suggestion {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 14px;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border);
  border-radius: 12px;
  color: var(--text);
  font-family: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  text-align: right;
  transition: 0.2s;
}
.chat-suggestion:hover {
  background: rgba(235,124,42,0.10);
  border-color: rgba(235,124,42,0.32);
}
.chat-suggestion .ico { width: 16px; height: 16px; color: var(--orange); flex-shrink: 0; }

/* ════ حباب پیام ════ */
.msg { display: flex; gap: 10px; }
.msg-user { justify-content: flex-end; }
.msg-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  display: grid;
  place-items: center;
  color: #1a0e05;
  flex-shrink: 0;
  align-self: flex-end;
}
.msg-avatar .ico { width: 16px; height: 16px; }

.msg-bub {
  max-width: 78%;
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.85;
  word-break: break-word;
}
.msg-user .msg-bub {
  background: linear-gradient(135deg, rgba(235,124,42,0.20), rgba(235,124,42,0.06));
  border: 1px solid rgba(235,124,42,0.32);
  border-bottom-right-radius: 4px;
}
.msg-ai .msg-bub {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border);
  border-bottom-left-radius: 4px;
}
.msg-attachment {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  padding: 4px 10px;
  background: rgba(0,0,0,0.3);
  border-radius: 999px;
  margin-bottom: 8px;
  color: var(--text-dim);
}
.msg-text { white-space: pre-wrap; }

/* ════ بنر سهمیه ════ */
.chat-quota-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 18px 12px;
  padding: 12px 16px;
  background: linear-gradient(135deg, rgba(255,184,107,0.12), rgba(255,184,107,0.04));
  border: 1px solid rgba(255,184,107,0.28);
  border-radius: 14px;
  color: #ffd2a8;
  font-size: 13px;
}
.chat-quota-banner b { display: block; font-size: 13.5px; }
.chat-quota-banner span { font-size: 11.5px; opacity: 0.85; }
.chat-quota-banner > div { flex: 1; }
.chat-quota-cta {
  padding: 8px 14px;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  color: #1a0e05;
  border-radius: 10px;
  font-weight: 800;
  font-size: 12px;
  text-decoration: none;
}

/* ════ ورودی ════ */
.chat-input-wrap {
  padding: 12px 18px 16px;
  background: rgba(255,255,255,0.03);
  border-top: 1px solid var(--border);
  backdrop-filter: blur(20px);
}
.chat-input-row {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  background: rgba(0,0,0,0.3);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 6px 6px 6px 10px;
  transition: 0.2s;
}
.chat-input-row:focus-within { border-color: rgba(235,124,42,0.5); box-shadow: 0 0 0 3px rgba(235,124,42,0.12); }

.chat-input-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: transparent;
  border: none;
  color: var(--text-dim);
  cursor: pointer;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.chat-input-icon:hover { color: var(--orange); background: rgba(255,255,255,0.05); }

.chat-input-grow {
  flex: 1;
  min-width: 0;
}
.chat-input-grow textarea {
  width: 100%;
  background: transparent;
  border: none;
  outline: none;
  color: var(--text);
  font-family: inherit;
  font-size: 14.5px;
  line-height: 1.6;
  resize: none;
  padding: 8px 6px;
  max-height: 160px;
  min-height: 22px;
}
.chat-input-grow textarea::placeholder { color: var(--text-muted); }

.chat-input-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  margin: 4px 0 0;
  background: rgba(56,217,169,0.08);
  border: 1px solid rgba(56,217,169,0.24);
  border-radius: 10px;
  font-size: 12px;
  color: #a3f0d3;
}
.chat-input-preview button {
  background: none;
  border: none;
  color: #ffb3c0;
  cursor: pointer;
  font-size: 14px;
  padding: 0;
}

.chat-send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  border: none;
  color: #1a0e05;
  cursor: pointer;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  box-shadow: 0 6px 18px rgba(235,124,42,0.4);
  transition: 0.2s;
}
.chat-send-btn:hover { transform: scale(1.05); }
.chat-send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ════ موبایل ════ */
@media (max-width: 820px) {
  .chat-shell { grid-template-columns: 1fr; }
  .chat-sidebar {
    position: fixed;
    top: 0; right: 0; bottom: 0;
    width: 86%;
    max-width: 320px;
    z-index: 1000;
    transform: translateX(105%);
    transition: transform 0.3s ease;
    border-left: 1px solid var(--border-orange);
  }
  .chat-sidebar.is-open { transform: translateX(0); }
  .chat-menu-btn { display: grid; }
  .msg-bub { max-width: 88%; }
  .chat-welcome-suggestions { gap: 6px; }
  .chat-suggestion { font-size: 12px; padding: 10px 12px; }
}

.chat-sidebar-backdrop {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 999;
  backdrop-filter: blur(4px);
}
.chat-sidebar-backdrop.is-open { display: block; }

/* تایپینگ */
.typing-bubble {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 14px 18px;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border);
  border-radius: 18px;
  border-bottom-left-radius: 4px;
}
.typing-bubble span {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: var(--orange);
  animation: typingDot 1.2s infinite ease-in-out;
}
.typing-bubble span:nth-child(2) { animation-delay: 0.15s; }
.typing-bubble span:nth-child(3) { animation-delay: 0.3s; }
@keyframes typingDot {
  0%, 80%, 100% { transform: scale(0.5); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}
</style>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';

  // ════ سایدبار موبایل ════
  const sidebar = document.getElementById('chatSidebar');
  const menuBtn = document.getElementById('chatMenuBtn');
  if (menuBtn) {
    let backdrop = document.createElement('div');
    backdrop.className = 'chat-sidebar-backdrop';
    document.body.appendChild(backdrop);
    menuBtn.addEventListener('click', function() {
      sidebar.classList.toggle('is-open');
      backdrop.classList.toggle('is-open');
    });
    backdrop.addEventListener('click', function() {
      sidebar.classList.remove('is-open');
      backdrop.classList.remove('is-open');
    });
  }

  // ════ جست‌وجو ════
  const search = document.getElementById('chatSearch');
  if (search) {
    search.addEventListener('input', function() {
      const q = this.value.toLowerCase();
      document.querySelectorAll('.chat-list-item').forEach(function(el) {
        const t = el.querySelector('.chat-list-item-title').textContent.toLowerCase();
        el.style.display = t.includes(q) ? '' : 'none';
      });
    });
  }

  // ════ انتخاب کتاب ════
  const bookSelect = document.getElementById('chatBookSelect');
  const bookInput = document.getElementById('bookIdInput');
  if (bookSelect) {
    bookSelect.addEventListener('change', function() {
      bookInput.value = this.value;
      // ذخیره خودکار
      if (document.getElementById('chatIdInput').value) {
        saveChatMeta();
      }
    });
  }

  function saveChatMeta() {
    const fd = new FormData();
    fd.append('csrf', csrf);
    fd.append('action', 'update_meta');
    fd.append('chat_id', document.getElementById('chatIdInput').value);
    fd.append('book_id', bookInput.value);
    fetch(base + '/api/chat_meta.php', {method:'POST', body: fd, credentials:'same-origin'});
  }

  // ════ چت جدید ════
  document.getElementById('chatNewBtn').addEventListener('click', function() {
    window.location.href = base + '/chat.php';
  });

  // ════ پیشنهادات ════
  document.querySelectorAll('.chat-suggestion').forEach(function(b) {
    b.addEventListener('click', function() {
      const input = document.getElementById('chatInput');
      input.value = this.dataset.suggest;
      input.focus();
      autoResize();
    });
  });

  // ════ پیوست ════
  const attachBtn = document.getElementById('attachBtn');
  const attachInput = document.getElementById('attachInput');
  const attachPreview = document.getElementById('attachPreview');
  if (attachBtn) {
    attachBtn.addEventListener('click', function() { attachInput.click(); });
    attachInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        attachPreview.innerHTML = '<span>' + this.files[0].name + '</span><button type="button">×</button>';
        attachPreview.style.display = 'flex';
        attachPreview.querySelector('button').addEventListener('click', function() {
          attachInput.value = '';
          attachPreview.style.display = 'none';
        });
      }
    });
  }

  // ════ auto-resize textarea ════
  const input = document.getElementById('chatInput');
  function autoResize() {
    input.style.height = 'auto';
    input.style.height = Math.min(160, input.scrollHeight) + 'px';
  }
  input.addEventListener('input', autoResize);

  // ════ ارسال پیام ════
  const form = document.getElementById('chatForm');
  const sendBtn = document.getElementById('chatSendBtn');
  const messagesEl = document.getElementById('chatMessages');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (sendBtn.disabled || !input.value.trim()) return;
    sendBtn.disabled = true;
    const userMsg = input.value.trim();
    const fd = new FormData(form);

    // اضافه کردن پیام کاربر به UI
    appendMessage('user', userMsg);
    input.value = '';
    autoResize();
    if (attachInput) {
      attachInput.value = '';
      attachPreview.style.display = 'none';
    }
    messagesEl.scrollTop = messagesEl.scrollHeight;

    // نشان دادن تایپینگ
    const typingEl = appendTyping();
    messagesEl.scrollTop = messagesEl.scrollHeight;

    try {
      const r = await fetch(base + '/api/chat.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const reader = r.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';
      let aiMsgEl = null;
      let fullText = '';
      typingEl.remove();

      while (true) {
        const {value, done} = await reader.read();
        if (done) break;
        buffer += decoder.decode(value, {stream:true});
        const lines = buffer.split('\n\n');
        buffer = lines.pop() || '';
        for (const line of lines) {
          if (line.startsWith('data: ')) {
            try {
              const obj = JSON.parse(line.slice(6));
              if (obj.chat_id && !document.getElementById('chatIdInput').value) {
                document.getElementById('chatIdInput').value = obj.chat_id;
                window.history.replaceState({}, '', base + '/chat.php?c=' + obj.chat_id);
              }
              if (obj.error) {
                appendMessage('ai', '⚠️ ' + obj.error);
              } else if (obj.content) {
                fullText += obj.content;
                if (!aiMsgEl) aiMsgEl = appendMessage('ai', '');
                aiMsgEl.querySelector('.msg-text').textContent = fullText;
                aiMsgEl.querySelector('.msg-text').style.whiteSpace = 'pre-wrap';
                messagesEl.scrollTop = messagesEl.scrollHeight;
              }
            } catch(e){}
          }
        }
      }
      if (!fullText && !aiMsgEl) {
        appendMessage('ai', '⚠️ پاسخی دریافت نشد. دوباره تلاش کن.');
      }
    } catch (err) {
      typingEl.remove();
      appendMessage('ai', '⚠️ خطا: ' + err.message);
    } finally {
      sendBtn.disabled = false;
      input.focus();
    }
  });

  function appendMessage(role, text) {
    const wrap = document.createElement('div');
    wrap.className = 'msg msg-' + role;
    if (role === 'ai') {
      wrap.innerHTML = '<div class="msg-avatar">' + getLogoSvg() + '</div><div class="msg-bub"><div class="msg-text"></div></div>';
    } else {
      wrap.innerHTML = '<div class="msg-bub"><div class="msg-text"></div></div>';
    }
    if (text) wrap.querySelector('.msg-text').textContent = text;
    messagesEl.appendChild(wrap);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return wrap;
  }

  function appendTyping() {
    const wrap = document.createElement('div');
    wrap.className = 'msg msg-ai';
    wrap.innerHTML = '<div class="msg-avatar">' + getLogoSvg() + '</div><div class="typing-bubble"><span></span><span></span><span></span></div>';
    messagesEl.appendChild(wrap);
    return wrap;
  }

  function getLogoSvg() {
    return '<svg class="ico" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="11.5" fill="url(#dyLG)"/><g fill="#1a0e05"><path d="M11.7 19.5c-.1-1.5 0-3.5.3-5.5.2-1.5.4-2.5.5-3.2-.1.7-.3 1.6-.5 3.2-.2 2-.4 4-.3 5.5z" stroke="#1a0e05" stroke-width=".4"/><rect x="10.5" y="18.5" width="3" height=".7" rx=".2"/><path d="M10.3 6.5L12 5.8l1.7.7v2l-1.7-.7-1.7.7zM12 5.8v2.7" stroke="#1a0e05" stroke-width=".35" fill="#1a0e05"/><path d="M12 11c-1.5-.5-3-1-4-2.5-.7-1-.5-2 .2-2.3.6-.2 1 .1 1.2.6M12 11c1.5-.5 3-1 4-2.5.7-1 .5-2-.2-2.3-.6-.2-1 .1-1.2.6" stroke="#1a0e05" stroke-width=".6" fill="none" stroke-linecap="round"/><circle cx="8.2" cy="7" r=".5"/><circle cx="15.8" cy="7" r=".5"/></g><defs><radialGradient id="dyLG"><stop offset="0%" stop-color="#ff9a3d"/><stop offset="100%" stop-color="#eb7c2a"/></radialGradient></defs></svg>';
  }

  // ════ حذف چت ════
  const deleteBtn = document.getElementById('chatDeleteBtn');
  if (deleteBtn) {
    deleteBtn.addEventListener('click', async function() {
      if (!confirm('این گفت‌وگو حذف بشه؟')) return;
      const id = document.getElementById('chatIdInput').value;
      if (!id) return;
      const fd = new FormData();
      fd.append('csrf', csrf);
      fd.append('action', 'delete');
      fd.append('chat_id', id);
      await fetch(base + '/api/chat_meta.php', {method:'POST', body: fd, credentials:'same-origin'});
      window.location.href = base + '/chat.php';
    });
  }

  // فوکوس اولیه
  if (input && !input.disabled) input.focus();
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
