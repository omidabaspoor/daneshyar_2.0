<?php
/**
 * دانش‌یار - دستیار ابزارهای مشاور
 * فقط برای مشاوران درسی
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/class_tools.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

if (($user['role'] ?? 'user') !== 'counselor') {
    redirect(BASE_URL . '/dashboard.php?msg=counselor_only');
}

$page = 'agents';
$pageTitle = 'ابزار کلاس';
$seoTitle = 'ابزار مدیریت کلاس برای مشاوران | دانش‌یار';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">ابزار کلاس</span>
  </div>

  <section class="agent-hero" data-agent="class_tools">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('users') ?></div>
      <div>
        <h1 class="agent-hero-title">ابزار کلاس</h1>
        <p class="agent-hero-sub">لیست شاگردها و عملکردشون رو بنویس. برای هر کدوم برنامهٔ شخصی می‌سازم و مقایسهٔ کلاس می‌دم. <b>PRO</b></p>
        <div class="agent-hero-meta">
          <span class="pill is-pro"><?= icon('star') ?> PRO</span>
          <span class="pill"><?= icon('users') ?> برای مشاوران</span>
        </div>
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

  <form class="agent-panel" id="clsForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('users') ?> لیست شاگردها</div>
      <span class="agent-panel-hint"><?= icon('info') ?> هر شاگرد با عملکردش</span>
    </div>

    <textarea class="agent-textarea" name="context" placeholder="لیست شاگردها رو اینجا بنویس. مثلاً:

علی (پایه ۱۲ تجربی):
- معدل: ۱۸.۵
- ریاضی: ضعیف (۴۰٪)
- فیزیک: متوسط (۵۵٪)
- زیست: خوب (۷۰٪)

مریم (پایه ۱۱ ریاضی):
- معدل: ۱۹
- ریاضی: خیلی خوب (۸۰٪)
- فیزیک: متوسط (۶۰٪)
- شیمی: خوب (۷۰٪)

یا هر اطلاعات دیگه‌ای که داری." required style="min-height: 200px;"></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('users') ?> برنامه بساز
    </button>
  </form>

  <div class="agent-result" id="clsResult">
    <div class="agent-result-head">
      <?= icon('users') ?>
      <div class="agent-result-title">برنامهٔ شخصی شاگردها</div>
      <div class="agent-result-actions">
        <button type="button" id="copyCls"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="clsBody"></div>
  </div>

  <div class="agent-result" id="clsLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال تحلیل کلاس...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('clsForm');
  const result = document.getElementById('clsResult');
  const body = document.getElementById('clsBody');
  const loading = document.getElementById('clsLoading');
  const btn = form.querySelector('button[type=submit]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال ساخت برنامه...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'class_tools');
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
      btn.innerHTML = '<?= icon('users') ?> برنامه بساز';
    }
  });

  document.getElementById('copyCls').addEventListener('click', function(){
    if (navigator.clipboard) navigator.clipboard.writeText(body.innerText).then(()=> this.textContent = 'کپی شد ✓');
  });

  function mdToHtml(t){
    return t
      .replace(/(## .+)/g, '<h3>$1</h3>')
      .replace(/(### .+)/g, '<h3>$1</h3>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/- (.+)/g, '• $1')
      .replace(/\n/g, '<br>');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
