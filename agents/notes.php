<?php
/**
 * دانش‌یار - ایجنت جزوه‌ساز
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/notes.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

$page = 'agents';
$pageTitle = 'جزوه‌ساز';
$seoTitle = 'جزوه‌ساز هوشمند | دانش‌یار';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">جزوه‌ساز</span>
  </div>

  <section class="agent-hero" data-agent="notes">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('book') ?></div>
      <div>
        <h1 class="agent-hero-title">جزوه‌ساز</h1>
        <p class="agent-hero-sub">از یه فصل یا مبحث، جزوهٔ منظم و خلاصه تولید کن. ساختار تیتر، نکات کلیدی، مثال — همه با هم.</p>
      </div>
    </div>
  </section>

  <?php if (!$quotaCheck['ok']): ?>
    <div class="quota-banner is-low">
      <?= icon('warning') ?>
      <div class="quota-banner-body">
        <b>سهمیه‌ات تموم شده</b>
        <span>برای استفاده نامحدود، اشتراک بخر.</span>
      </div>
      <a href="<?= BASE_URL ?>/pricing.php">خرید اشتراک</a>
    </div>
  <?php endif; ?>

  <form class="agent-panel" id="notesForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('book') ?> درس و مبحث</div>
    </div>

    <div class="agent-options">
      <label class="agent-option"><input type="radio" name="subject" value="math" checked><span>ریاضی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="physics"><span>فیزیک</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="chemistry"><span>شیمی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="biology"><span>زیست</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="literature"><span>ادبیات</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="arabic"><span>عربی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="dini"><span>دینی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="tarikh"><span>تاریخ</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="joghrafia"><span>جغرافیا</span></label>
    </div>

    <input type="text" name="topic" class="agent-textarea" placeholder="مبحث دقیق (مثلاً: مشتق، واکنش‌های اسید و باز، جنگ صفین)" style="margin-top: 10px; min-height: 50px;" required>

    <div class="agent-panel-head" style="margin-top: 14px;">
      <div class="agent-panel-title"><?= icon('edit') ?> متن منبع (اختیاری)</div>
      <span class="agent-panel-hint"><?= icon('info') ?> اگه متنی داری بچسبان</span>
    </div>

    <textarea class="agent-textarea" name="text" placeholder="اگه از کتاب یا جزوه متنی داری، اینجا بچسبان. اگه خالی بذاری، از دانش عمومی کتاب درسی استفاده می‌کنم." style="min-height: 110px;"></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('book') ?> جزوه بساز
    </button>
  </form>

  <div class="agent-result" id="notesResult">
    <div class="agent-result-head">
      <?= icon('book') ?>
      <div class="agent-result-title">جزوهٔ تولیدشده</div>
      <div class="agent-result-actions">
        <button type="button" id="copyNotes"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="notesBody"></div>
  </div>

  <div class="agent-result" id="notesLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال ساخت جزوه...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('notesForm');
  const result = document.getElementById('notesResult');
  const body = document.getElementById('notesBody');
  const loading = document.getElementById('notesLoading');
  const btn = form.querySelector('button[type=submit]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال ساخت جزوه...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'notes');
      const r = await fetch(base + '/api/agent_run.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const data = await r.json();
      loading.style.display = 'none';
      if (data.error) body.innerHTML = '<div class="alert alert-error">'+ data.error +'</div>';
      else if (data.result) body.innerHTML = mdToHtml(data.result);
      else body.innerHTML = '<div class="alert alert-info">پاسخی دریافت نشد.</div>';
      result.classList.add('is-visible');
      result.scrollIntoView({behavior:'smooth', block:'start'});
    } catch (err) {
      body.innerHTML = '<div class="alert alert-error">خطا: '+ err.message +'</div>';
      result.classList.add('is-visible');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<?= icon('book') ?> جزوه بساز';
    }
  });

  document.getElementById('copyNotes').addEventListener('click', function(){
    if (navigator.clipboard) navigator.clipboard.writeText(body.innerText).then(()=> this.textContent = 'کپی شد ✓');
  });

  function mdToHtml(t){
    return t
      .replace(/(# .+)/g, '<h3>$1</h3>')
      .replace(/(## .+)/g, '<h3>$1</h3>')
      .replace(/(### .+)/g, '<h3>$1</h3>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/- (.+)/g, '• $1')
      .replace(/\n/g, '<br>');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
