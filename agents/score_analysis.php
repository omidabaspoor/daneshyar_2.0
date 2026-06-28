<?php
/**
 * دانش‌یار - دستیار تحلیل کارنامهٔ آزمون آزمایشی
 * فقط برای کنکوری‌ها
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/score_analysis.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

if ((int)($user['grade'] ?? 0) < 11) {
    redirect(BASE_URL . '/dashboard.php?msg=konkoori_only');
}

$page = 'agents';
$pageTitle = 'تحلیل کارنامه';
$seoTitle = 'تحلیل کارنامهٔ آزمون آزمایشی | دانش‌یار';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">تحلیل کارنامه</span>
  </div>

  <section class="agent-hero" data-agent="score_analysis">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('graph') ?></div>
      <div>
        <h1 class="agent-hero-title">تحلیل کارنامهٔ آزمون</h1>
        <p class="agent-hero-sub">کارنامهٔ آزمون آزمایشی‌ات رو بفرست. دقیق می‌گم کجا قوته، کجا ضعیفه، و چیکار کنی بهتر بشه.</p>
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

  <form class="agent-panel" id="scoreForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('edit') ?> اطلاعات کارنامه</div>
      <span class="agent-panel-hint"><?= icon('info') ?> تایپ کن یا عکس رو توصیف کن</span>
    </div>

    <div class="agent-options">
      <label class="agent-option"><input type="radio" name="subject" value="آزمون قلمچی" checked><span>قلمچی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="آزمون گاج"><span>گاج</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="آزمون سنجش"><span>سنجش</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="آزمون کانون"><span>کانون</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="سایر"><span>سایر</span></label>
    </div>

    <textarea class="agent-textarea" name="context" placeholder="کارنامه‌ات رو اینجا بنویس. مثلاً:
- رتبه کل: ۲۵۰۰
- تراز: ۶۸۰۰
- ریاضی: ۴۰٪
- فیزیک: ۳۵٪
- شیمی: ۵۵٪
- زیست: ۶۵٪
- ادبیات: ۷۰٪
- عربی: ۶۰٪
- دینی: ۷۵٪
- زبان: ۵۰٪
- تاریخ: ۴۰٪
- جغرافیا: ۳۵٪

یا هر اطلاعات دیگه‌ای که داری." required style="min-height: 180px;"></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('graph') ?> تحلیل کن
    </button>
  </form>

  <div class="agent-result" id="scoreResult">
    <div class="agent-result-head">
      <?= icon('graph') ?>
      <div class="agent-result-title">تحلیل کارنامه</div>
      <div class="agent-result-actions">
        <button type="button" id="copyScore"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="scoreBody"></div>
  </div>

  <div class="agent-result" id="scoreLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال تحلیل کارنامه...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('scoreForm');
  const result = document.getElementById('scoreResult');
  const body = document.getElementById('scoreBody');
  const loading = document.getElementById('scoreLoading');
  const btn = form.querySelector('button[type=submit]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال تحلیل...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'score_analysis');
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
      btn.innerHTML = '<?= icon('graph') ?> تحلیل کن';
    }
  });

  document.getElementById('copyScore').addEventListener('click', function(){
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
