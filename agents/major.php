<?php
/**
 * دانش‌یار - ایجنت انتخاب رشته
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/major.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

// فقط کنکوری (پایه ۱۱ یا ۱۲)
if ((int)($user['grade'] ?? 0) < 11) {
    redirect(BASE_URL . '/dashboard.php?msg=konkoori_only');
}

$page = 'agents';
$pageTitle = 'انتخاب رشته';
$seoTitle = 'انتخاب رشته با هوش مصنوعی | دانش‌یار';
$seoDescription = 'مشاورهٔ انتخاب رشته برای کنکوری‌ها بر اساس علاقه، نمره و هدف.';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">انتخاب رشته</span>
  </div>

  <section class="agent-hero" data-agent="major">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('trophy') ?></div>
      <div>
        <h1 class="agent-hero-title">انتخاب رشته</h1>
        <p class="agent-hero-sub">یکی از مهم‌ترین تصمیم‌هات زندگیه. علاقه، نمره و هدفت رو بگو — مثل یه مشاور باتجربه، چند رشتهٔ مناسب پیشنهاد می‌دم. <b>PRO · مخصوص کنکوری‌ها</b></p>
        <div class="agent-hero-meta">
          <span class="pill is-pro"><?= icon('star') ?> PRO</span>
          <span class="pill"><?= icon('check') ?> مخصوص کنکوری‌ها</span>
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

  <form class="agent-panel" id="majorForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('user') ?> خودت رو معرفی کن</div>
      <span class="agent-panel-hint"><?= icon('info') ?> هر چی بیشتر بگی، بهتر کمکت می‌کنم</span>
    </div>

    <textarea class="agent-textarea" name="message" placeholder="مثلاً: «پایهٔ دوازدهم تجربی‌ام، معدل ۱۸، به پزشکی علاقه دارم ولی می‌ترسم سخت باشه. پدر و مادرم می‌خوان مهندسی بخونم. بازار کار برام مهمه.»" required></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('sparkle') ?> راهنمایی کن
    </button>
  </form>

  <div class="agent-result" id="majorResult">
    <div class="agent-result-head">
      <?= icon('trophy') ?>
      <div class="agent-result-title">پیشنهادهای مشاور</div>
      <div class="agent-result-actions">
        <button type="button" id="copyMajor"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="majorBody"></div>
  </div>

  <div class="agent-result" id="majorLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال مشاوره...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('majorForm');
  const result = document.getElementById('majorResult');
  const body = document.getElementById('majorBody');
  const loading = document.getElementById('majorLoading');
  const btn = form.querySelector('button[type=submit]');
  const msg = form.querySelector('textarea[name=message]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled || !msg.value.trim()) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال مشاوره...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');
    body.innerHTML = '';

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'major');
      const r = await fetch(base + '/api/agent_chat.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const reader = r.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';
      loading.style.display = 'none';
      result.classList.add('is-visible');
      while (true) {
        const {value, done} = await reader.read();
        if (done) break;
        buffer += decoder.decode(value, {stream: true});
        const lines = buffer.split('\n\n');
        buffer = lines.pop() || '';
        let fullText = '';
        for (const line of lines) {
          if (line.startsWith('data: ')) {
            try {
              const obj = JSON.parse(line.slice(6));
              if (obj.error) {
                body.innerHTML = '<div class="alert alert-error">'+ obj.error +'</div>';
              } else if (obj.choices?.[0]?.delta?.content) {
                fullText += obj.choices[0].delta.content;
              } else if (obj.content) {
                fullText += obj.content;
              }
            } catch(e){}
          }
        }
        if (fullText) body.innerHTML = mdToHtml(fullText);
      }
    } catch (err) {
      body.innerHTML = '<div class="alert alert-error">خطا: '+ err.message +'</div>';
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<?= icon('sparkle') ?> راهنمایی کن';
    }
  });

  document.getElementById('copyMajor').addEventListener('click', function(){
    if (navigator.clipboard) navigator.clipboard.writeText(body.innerText).then(()=> this.textContent = 'کپی شد ✓');
  });

  function mdToHtml(t){
    return t
      .replace(/(## .+)/g, '<h3>$1</h3>')
      .replace(/(### .+)/g, '<h3>$1</h3>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/\n/g, '<br>');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
