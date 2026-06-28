<?php
/**
 * دانش‌یار - ایجنت جمع‌بندی شب امتحان
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/review.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

$page = 'agents';
$pageTitle = 'جمع‌بندی امتحان';
$seoTitle = 'جمع‌بندی شب امتحان | دانش‌یار';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">جمع‌بندی امتحان</span>
  </div>

  <section class="agent-hero" data-agent="review">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('flash') ?></div>
      <div>
        <h1 class="agent-hero-title">جمع‌بندی شب امتحان</h1>
        <p class="agent-hero-sub">فردا امتحان داری؟ فقط مباحث مهم رو بگو. در ۱۵ دقیقه، مهم‌ترین نکات، فرمول‌ها و تله‌های امتحانی رو جمع می‌کنم.</p>
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

  <form class="agent-panel" id="revForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('flash') ?> درس و مباحث</div>
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

    <textarea class="agent-textarea" name="topic" placeholder="مباحثی که فردا امتحان داری. مثلاً: «مشتق و کاربرد مشتق، تابع، مثلثات». اگه همهٔ درس رو می‌خوای بنویس «همه»." required></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('flash') ?> جمع‌بندی کن
    </button>
  </form>

  <div class="agent-result" id="revResult">
    <div class="agent-result-head">
      <?= icon('flash') ?>
      <div class="agent-result-title">جمع‌بندی</div>
      <div class="agent-result-actions">
        <button type="button" id="copyRev"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="revBody"></div>
  </div>

  <div class="agent-result" id="revLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال جمع‌بندی...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('revForm');
  const result = document.getElementById('revResult');
  const body = document.getElementById('revBody');
  const loading = document.getElementById('revLoading');
  const btn = form.querySelector('button[type=submit]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال جمع‌بندی...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'review');
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
      btn.innerHTML = '<?= icon('flash') ?> جمع‌بندی کن';
    }
  });

  document.getElementById('copyRev').addEventListener('click', function(){
    if (navigator.clipboard) navigator.clipboard.writeText(body.innerText).then(()=> this.textContent = 'کپی شد ✓');
  });

  function mdToHtml(t){
    return t
      .replace(/(## .+)/g, '<h3>$1</h3>')
      .replace(/(### .+)/g, '<h3>$1</h3>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/- \[ \] (.+)/g, '☐ $1')
      .replace(/- (.+)/g, '• $1')
      .replace(/\n/g, '<br>');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
