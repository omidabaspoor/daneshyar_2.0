<?php
/**
 * دانش‌یار - دستیار برنامه‌ریز کنکور
 * فقط برای دانش‌آموزان کنکوری (پایه ۱۱ یا ۱۲)
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/konkoori_planner.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

// چک دسترسی: فقط کنکوری
if ((int)($user['grade'] ?? 0) < 11) {
    redirect(BASE_URL . '/dashboard.php?msg=konkoori_only');
}

$page = 'agents';
$pageTitle = 'برنامه‌ریز کنکور';
$seoTitle = 'برنامه‌ریز کنکور با هوش مصنوعی | دانش‌یار';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">برنامه‌ریز کنکور</span>
  </div>

  <section class="agent-hero" data-agent="konkoori_planner">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('clock') ?></div>
      <div>
        <h1 class="agent-hero-title">برنامه‌ریز کنکور</h1>
        <p class="agent-hero-sub">تا روز کنکور چند روز مونده؟ ساعت مطالعه‌ات چقدره؟ یه برنامهٔ واقع‌بینانه بر اساس بودجه‌بندی کنکور می‌چینم.</p>
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

  <form class="agent-panel" id="planForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('clock') ?> مشخصات برنامه</div>
    </div>

    <div class="agent-options">
      <label class="agent-option"><input type="radio" name="subject" value="konkoor" checked><span>کنکور سراسری</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="math"><span>ریاضی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="physics"><span>فیزیک</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="chemistry"><span>شیمی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="biology"><span>زیست</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="literature"><span>ادبیات</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="arabic"><span>عربی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="dini"><span>دینی</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="tarikh"><span>تاریخ</span></label>
      <label class="agent-option"><input type="radio" name="subject" value="joghrafia"><span>جغرافیا</span></label>
    </div>

    <div class="agent-options">
      <label class="agent-option"><input type="radio" name="grade" value="11" checked><span>پایه ۱۱</span></label>
      <label class="agent-option"><input type="radio" name="grade" value="12"><span>پایه ۱۲</span></label>
    </div>

    <div class="agent-options">
      <label class="agent-option"><input type="radio" name="hours_per_day" value="2"><span>۲ ساعت</span></label>
      <label class="agent-option"><input type="radio" name="hours_per_day" value="3" checked><span>۳ ساعت</span></label>
      <label class="agent-option"><input type="radio" name="hours_per_day" value="4"><span>۴ ساعت</span></label>
      <label class="agent-option"><input type="radio" name="hours_per_day" value="5"><span>۵ ساعت</span></label>
    </div>

    <input type="text" name="exam_date" class="agent-textarea" placeholder="تاریخ کنکور (مثلاً: ۱۴۰۵/۰۴/۰۵ یا «۴ ماه دیگر»)" style="margin-top: 10px; min-height: 50px;" required>

    <textarea class="agent-textarea" name="context" placeholder="اگه نکته خاصی هست (مثلاً «هفتهٔ دیگه آزمون آزمایشی دارم»، «۵ درس باید بخونم»، «ضعیف‌ترم در ریاضی»)" style="margin-top: 10px; min-height: 70px;"></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('clock') ?> برنامه بساز
    </button>
  </form>

  <div class="agent-result" id="planResult">
    <div class="agent-result-head">
      <?= icon('clock') ?>
      <div class="agent-result-title">برنامهٔ کنکور</div>
      <div class="agent-result-actions">
        <button type="button" id="copyPlan"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="planBody"></div>
  </div>

  <div class="agent-result" id="planLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال تنظیم برنامه...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('planForm');
  const result = document.getElementById('planResult');
  const body = document.getElementById('planBody');
  const loading = document.getElementById('planLoading');
  const btn = form.querySelector('button[type=submit]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال برنامه‌ریزی...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'konkoori_planner');
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
      btn.innerHTML = '<?= icon('clock') ?> برنامه بساز';
    }
  });

  document.getElementById('copyPlan').addEventListener('click', function(){
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
