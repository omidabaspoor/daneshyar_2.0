<?php
/**
 * دانش‌یار - ایجنت توضیح درس (معلم خصوصی)
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/tutor.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

$page = 'agents';
$pageTitle = 'توضیح درس';
$seoTitle = 'توضیح درس با معلم خصوصی هوش مصنوعی | دانش‌یار';
$seoDescription = 'معلم خصوصی هوش مصنوعی؛ هر درسی رو قدم‌به‌قدم یاد بگیر.';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">معلم خصوصی</span>
  </div>

  <section class="agent-hero" data-agent="tutor">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('school') ?></div>
      <div>
        <h1 class="agent-hero-title">معلم خصوصی</h1>
        <p class="agent-hero-sub">هر مفهوم درسی رو که نفهمیدی، اینجا بنویس. مثل یه معلم واقعی، قدم‌به‌قدم، با مثال، توضیح می‌دم تا واقعاً یاد بگیری.</p>
        <div class="agent-hero-meta">
          <span class="pill"><?= icon('check') ?> توضیح قدم‌به‌قدم</span>
          <span class="pill"><?= icon('check') ?> مثال‌های ملموس</span>
          <span class="pill"><?= icon('check') ?> بدون عجله</span>
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

  <form class="agent-panel" id="tutorForm">
    <div class="agent-panel-head">
      <div class="agent-panel-title"><?= icon('chat') ?> چی رو می‌خوای یاد بگیری؟</div>
      <span class="agent-panel-hint"><?= icon('info') ?> هر چی واضح‌تر بگی، بهتر توضیح می‌دم</span>
    </div>

    <textarea class="agent-textarea" name="message" placeholder="مثلاً: «مشتق رو نفهمیدم»، یا «فرق بین ρ و چگالی چیه؟»، یا «قاعدهٔ هشت‌تایی رو توضیح بده»" required></textarea>

    <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
      <?= icon('sparkle') ?> توضیح بده
    </button>
  </form>

  <div class="agent-result" id="tutorResult">
    <div class="agent-result-head">
      <?= icon('school') ?>
      <div class="agent-result-title">توضیح معلم</div>
      <div class="agent-result-actions">
        <button type="button" id="copyTutor"><?= icon('edit') ?> کپی</button>
      </div>
    </div>
    <div class="agent-result-body" id="tutorBody"></div>
  </div>

  <div class="agent-result" id="tutorLoading" style="display:none">
    <div class="agent-result-loading">
      <span class="spinner"></span>
      <span>در حال توضیح...</span>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';
  const form = document.getElementById('tutorForm');
  const result = document.getElementById('tutorResult');
  const body = document.getElementById('tutorBody');
  const loading = document.getElementById('tutorLoading');
  const btn = form.querySelector('button[type=submit]');
  const msg = form.querySelector('textarea[name=message]');

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (btn.disabled || !msg.value.trim()) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال توضیح...';
    loading.style.display = 'block';
    result.classList.remove('is-visible');
    body.innerHTML = '';

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'tutor');
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
      btn.innerHTML = '<?= icon('sparkle') ?> توضیح بده';
    }
  });

  document.getElementById('copyTutor').addEventListener('click', function(){
    if (navigator.clipboard) {
      navigator.clipboard.writeText(body.innerText).then(()=> this.textContent = 'کپی شد ✓');
    }
  });

  function mdToHtml(t){
    return t
      .replace(/### (.+)/g, '<h3>$1</h3>')
      .replace(/## (.+)/g, '<h3>$1</h3>')
      .replace(/# (.+)/g, '<h3>$1</h3>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.+?)\*/g, '<em>$1</em>')
      .replace(/```([^`]+)```/g, '<pre><code>$1</code></pre>')
      .replace(/`([^`]+)`/g, '<code>$1</code>')
      .replace(/\n/g, '<br>');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
