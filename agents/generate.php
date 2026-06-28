<?php
/**
 * دانش‌یار - دستیار تست‌ساز (جریان چندمرحله‌ای)
 *
 * مرحلهٔ ۱: فرم (درس، مبحث، سطح، تعداد)
 * مرحلهٔ ۲: ساخت تست (AI)
 * مرحلهٔ ۳: محیط آزمون (تایمر، سؤال‌به‌سؤال، ثبت پاسخ)
 * مرحلهٔ ۴: کارنامه (نمره، درست/نادرست)
 * مرحلهٔ ۵: تحلیل اشتباهات + یادگیری
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/generate.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

$page = 'agents';
$pageTitle = 'تست‌ساز';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">تست‌ساز</span>
  </div>

  <section class="agent-hero" data-agent="generate">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('sparkle') ?></div>
      <div>
        <h1 class="agent-hero-title">تست‌ساز</h1>
        <p class="agent-hero-sub">تست بساز، همونجا بده، نمره بگیر، اشتباهاتت رو قدم‌به‌قدم یاد بگیر. یه چرخهٔ کامل یادگیری.</p>
        <div class="agent-hero-meta">
          <span class="pill"><?= icon('check') ?> تست شخصی</span>
          <span class="pill"><?= icon('check') ?> محیط آزمون</span>
          <span class="pill"><?= icon('check') ?> تحلیل اشتباهات</span>
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

  <!-- ══════ مرحله ۱: فرم ══════ -->
  <section id="genStep1" class="gen-step is-active">
    <form class="agent-panel" id="genForm">
      <div class="agent-panel-head">
        <div class="agent-panel-title"><?= icon('book') ?> درس و مبحث</div>
      </div>

      <div class="agent-options">
        <label class="agent-option"><input type="radio" name="subject" value="math" required checked><span>ریاضی</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="physics"><span>فیزیک</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="chemistry"><span>شیمی</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="biology"><span>زیست</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="literature"><span>ادبیات</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="arabic"><span>عربی</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="dini"><span>دینی</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="tarikh"><span>تاریخ</span></label>
        <label class="agent-option"><input type="radio" name="subject" value="joghrafia"><span>جغرافیا</span></label>
      </div>

      <input type="text" name="topic" class="agent-textarea" placeholder="مبحث دقیق (مثلاً: مشتق، جنگ صفین، آرایهٔ تشبیه، مثلثات)" style="margin-top: 10px; min-height: 50px;" required>

      <div class="agent-panel-head" style="margin-top: 14px;">
        <div class="agent-panel-title"><?= icon('chart') ?> سطح و تعداد</div>
      </div>

      <div class="agent-options">
        <label class="agent-option"><input type="radio" name="level" value="easy" checked><span>آسان</span></label>
        <label class="agent-option"><input type="radio" name="level" value="medium"><span>متوسط</span></label>
        <label class="agent-option"><input type="radio" name="level" value="hard"><span>سخت</span></label>
        <label class="agent-option"><input type="radio" name="level" value="konkoor"><span>کنکوری</span></label>
      </div>

      <div class="agent-options">
        <label class="agent-option"><input type="radio" name="count" value="5"><span>۵ سؤال (۱۰ دقیقه)</span></label>
        <label class="agent-option"><input type="radio" name="count" value="10" checked><span>۱۰ سؤال (۲۰ دقیقه)</span></label>
        <label class="agent-option"><input type="radio" name="count" value="15"><span>۱۵ سؤال (۳۰ دقیقه)</span></label>
        <label class="agent-option"><input type="radio" name="count" value="20"><span>۲۰ سؤال (۴۵ دقیقه)</span></label>
      </div>

      <button type="submit" class="agent-send" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
        <?= icon('sparkle') ?> بساز و شروع کن
      </button>
    </form>
  </section>

  <!-- ══════ مرحله ۲: ساخت ══════ -->
  <section id="genStep2" class="gen-step">
    <div class="gen-loading">
      <div class="gen-loading-icon"><?= icon('sparkle') ?></div>
      <h3>در حال طراحی تست...</h3>
      <p>هوش مصنوعی داره سؤال‌های مرتبط با کتاب درسی طراحی می‌کنه. چند ثانیه صبر کن.</p>
      <div class="gen-loading-bar"><div class="gen-loading-fill"></div></div>
    </div>
  </section>

  <!-- ══════ مرحله ۳: محیط آزمون ══════ -->
  <section id="genStep3" class="gen-step">
    <div class="quiz-bar">
      <div class="quiz-bar-progress">
        <div class="quiz-bar-num"><span id="quizCurrent">۱</span> / <span id="quizTotal">۱۰</span></div>
        <div class="quiz-bar-track"><div class="quiz-bar-fill" id="quizProgress"></div></div>
      </div>
      <div class="quiz-bar-timer" id="quizTimer">۲۰:۰۰</div>
    </div>

    <div class="quiz-card glass" id="quizCard">
      <!-- پر می‌شه با JS -->
    </div>

    <div class="quiz-nav">
      <button class="btn btn-ghost" id="quizPrev"><?= icon('arrow-right') ?> قبلی</button>
      <div class="quiz-nav-dots" id="quizDots"></div>
      <button class="btn btn-primary" id="quizNext">بعدی <?= icon('arrow-left') ?></button>
    </div>
  </section>

  <!-- ══════ مرحله ۴: کارنامه ══════ -->
  <section id="genStep4" class="gen-step">
    <div class="scorecard glass">
      <div class="scorecard-head">
        <div class="scorecard-icon"><?= icon('trophy') ?></div>
        <div>
          <div class="scorecard-label">نمرهٔ شما</div>
          <div class="scorecard-value" id="scoreValue">—</div>
          <div class="scorecard-text" id="scoreText">—</div>
        </div>
      </div>
      <div class="scorecard-grid">
        <div class="scorecard-cell is-correct">
          <div class="scorecard-cell-num" id="scoreCorrect">—</div>
          <div class="scorecard-cell-label">درست</div>
        </div>
        <div class="scorecard-cell is-wrong">
          <div class="scorecard-cell-num" id="scoreWrong">—</div>
          <div class="scorecard-cell-label">نادرست</div>
        </div>
        <div class="scorecard-cell is-blank">
          <div class="scorecard-cell-num" id="scoreBlank">—</div>
          <div class="scorecard-cell-label">نزده</div>
        </div>
        <div class="scorecard-cell is-time">
          <div class="scorecard-cell-num" id="scoreTime">—</div>
          <div class="scorecard-cell-label">زمان</div>
        </div>
      </div>
    </div>

    <div class="scorecard-actions">
      <button class="btn btn-primary" id="goToAnalysis"><?= icon('sparkle') ?> تحلیل و یادگیری اشتباهات</button>
      <button class="btn btn-ghost" id="restartQuiz"><?= icon('refresh') ?> تست جدید</button>
    </div>
  </section>

  <!-- ══════ مرحله ۵: تحلیل و یادگیری ══════ -->
  <section id="genStep5" class="gen-step">
    <div class="analysis-header">
      <h2>تحلیل اشتباهات</h2>
      <p>برای هر سؤالی که اشتباه جواب دادی، توضیح کامل و روش صحیح رو می‌بینی.</p>
    </div>

    <div id="analysisList"></div>

    <div class="scorecard-actions">
      <button class="btn btn-ghost" id="analysisNewTest"><?= icon('sparkle') ?> تست جدید</button>
      <a class="btn btn-primary" href="<?= BASE_URL ?>/dashboard.php"><?= icon('home') ?> بازگشت به خانه</a>
    </div>
  </section>
</div>

<style>
/* مراحل */
.gen-step { display: none; }
.gen-step.is-active { display: block; animation: fadeUp 0.3s ease; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

/* لودینگ ساخت تست */
.gen-loading {
  text-align: center;
  padding: 60px 20px;
  background: linear-gradient(160deg, rgba(255,255,255,0.05), rgba(255,255,255,0.012));
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  backdrop-filter: blur(20px);
}
.gen-loading-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  display: grid;
  place-items: center;
  color: #1a0e05;
  box-shadow: 0 10px 30px rgba(235,124,42,0.4);
  animation: pulse-scale 1.5s ease-in-out infinite;
}
@keyframes pulse-scale {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}
.gen-loading-icon .ico { width: 38px; height: 38px; }
.gen-loading h3 { font-size: 22px; margin-bottom: 10px; }
.gen-loading p { color: var(--text-dim); font-size: 14px; max-width: 380px; margin: 0 auto 24px; line-height: 1.8; }
.gen-loading-bar {
  width: 280px;
  max-width: 100%;
  height: 6px;
  background: rgba(255,255,255,0.06);
  border-radius: 999px;
  margin: 0 auto;
  overflow: hidden;
}
.gen-loading-fill {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, var(--orange), var(--orange-2));
  border-radius: 999px;
  animation: loadFill 2.5s ease-in-out infinite;
}
@keyframes loadFill {
  0% { width: 0%; }
  60% { width: 80%; }
  100% { width: 95%; }
}

/* نوار بالای آزمون */
.quiz-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  background: linear-gradient(160deg, rgba(255,255,255,0.06), rgba(255,255,255,0.012));
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  backdrop-filter: blur(20px);
  margin-bottom: 14px;
}
.quiz-bar-progress {
  flex: 1;
  min-width: 0;
}
.quiz-bar-num {
  font-size: 13px;
  color: var(--text-dim);
  margin-bottom: 6px;
  font-weight: 700;
}
.quiz-bar-num span:first-child { color: var(--orange); font-weight: 800; }
.quiz-bar-track {
  height: 6px;
  background: rgba(255,255,255,0.06);
  border-radius: 999px;
  overflow: hidden;
}
.quiz-bar-fill {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, var(--orange), var(--orange-2));
  border-radius: 999px;
  transition: width 0.4s ease;
}
.quiz-bar-timer {
  font-size: 18px;
  font-weight: 800;
  color: var(--text);
  background: rgba(0,0,0,0.3);
  padding: 8px 14px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.08);
  min-width: 80px;
  text-align: center;
  font-variant-numeric: tabular-nums;
}
.quiz-bar-timer.is-low { color: var(--orange); }
.quiz-bar-timer.is-critical {
  color: #ffb3c0;
  background: rgba(255,84,112,0.12);
  border-color: rgba(255,84,112,0.28);
  animation: pulse-scale 0.8s ease-in-out infinite;
}

/* کارت سؤال */
.quiz-card {
  padding: 22px 20px;
  margin-bottom: 14px;
  min-height: 320px;
}
.quiz-q-num {
  display: inline-block;
  font-size: 12px;
  font-weight: 800;
  color: var(--orange);
  background: var(--orange-soft);
  border: 1px solid rgba(235,124,42,0.28);
  padding: 4px 10px;
  border-radius: 999px;
  margin-bottom: 14px;
}
.quiz-q-text {
  font-size: 16px;
  line-height: 1.85;
  color: var(--text);
  margin-bottom: 18px;
  font-weight: 700;
}
.quiz-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.quiz-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14.5px;
  color: var(--text);
}
.quiz-option:hover {
  background: rgba(255,255,255,0.07);
  border-color: rgba(255,255,255,0.16);
}
.quiz-option.is-selected {
  background: linear-gradient(135deg, rgba(235,124,42,0.20), rgba(235,124,42,0.06));
  border-color: rgba(235,124,42,0.5);
}
.quiz-option-letter {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255,255,255,0.06);
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 13px;
  flex-shrink: 0;
  color: var(--text-dim);
}
.quiz-option.is-selected .quiz-option-letter {
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  color: #1a0e05;
}

/* نوار پایین آزمون */
.quiz-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
}
.quiz-nav-dots {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: center;
  flex: 1;
}
.quiz-dot {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  font-size: 12px;
  font-weight: 700;
  display: grid;
  place-items: center;
  cursor: pointer;
  color: var(--text-dim);
}
.quiz-dot.is-current {
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  color: #1a0e05;
  border-color: transparent;
}
.quiz-dot.is-answered {
  background: rgba(56,217,169,0.18);
  border-color: rgba(56,217,169,0.35);
  color: #a3f0d3;
}

/* کارنامه */
.scorecard {
  padding: 24px 22px;
  margin-bottom: 16px;
  background: linear-gradient(160deg, rgba(235,124,42,0.16), rgba(235,124,42,0.04));
  border: 1px solid rgba(235,124,42,0.32);
  box-shadow: 0 12px 40px rgba(0,0,0,0.3);
}
.scorecard-head {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.scorecard-icon {
  width: 70px;
  height: 70px;
  border-radius: 20px;
  background: linear-gradient(135deg, var(--orange), var(--orange-2));
  display: grid;
  place-items: center;
  color: #1a0e05;
  flex-shrink: 0;
  box-shadow: 0 10px 28px rgba(235,124,42,0.4);
}
.scorecard-icon .ico { width: 34px; height: 34px; }
.scorecard-label {
  font-size: 13px;
  color: rgba(255,255,255,0.7);
  margin-bottom: 6px;
}
.scorecard-value {
  font-size: 38px;
  font-weight: 900;
  color: var(--text);
  line-height: 1;
  margin-bottom: 6px;
  font-variant-numeric: tabular-nums;
}
.scorecard-text {
  font-size: 14px;
  color: var(--text-dim);
  line-height: 1.6;
}
.scorecard-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}
.scorecard-cell {
  padding: 14px 12px;
  background: rgba(0,0,0,0.25);
  border-radius: 14px;
  text-align: center;
}
.scorecard-cell.is-correct { border: 1px solid rgba(56,217,169,0.28); }
.scorecard-cell.is-wrong { border: 1px solid rgba(255,84,112,0.28); }
.scorecard-cell.is-blank { border: 1px solid rgba(255,255,255,0.08); }
.scorecard-cell.is-time { border: 1px solid rgba(77,171,247,0.28); }
.scorecard-cell-num {
  font-size: 22px;
  font-weight: 800;
  font-variant-numeric: tabular-nums;
  line-height: 1.1;
}
.scorecard-cell.is-correct .scorecard-cell-num { color: #a3f0d3; }
.scorecard-cell.is-wrong .scorecard-cell-num { color: #ffc1cc; }
.scorecard-cell.is-blank .scorecard-cell-num { color: var(--text-dim); }
.scorecard-cell.is-time .scorecard-cell-num { color: #cfe8ff; font-size: 16px; }
.scorecard-cell-label {
  font-size: 11px;
  color: var(--text-dim);
  margin-top: 4px;
}
.scorecard-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 16px;
}
@media (min-width: 720px) {
  .scorecard-grid { grid-template-columns: repeat(4, 1fr); }
  .scorecard-actions { flex-direction: row; }
}

/* تحلیل و یادگیری */
.analysis-header {
  text-align: center;
  margin-bottom: 20px;
  padding: 20px;
}
.analysis-header h2 {
  font-size: 24px;
  font-weight: 800;
  margin-bottom: 8px;
}
.analysis-header p {
  color: var(--text-dim);
  font-size: 14px;
  line-height: 1.85;
}
.analysis-item {
  margin-bottom: 14px;
  padding: 18px 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
}
.analysis-item.is-correct {
  border-color: rgba(56,217,169,0.32);
  background: rgba(56,217,169,0.06);
}
.analysis-item.is-wrong {
  border-color: rgba(255,84,112,0.32);
  background: rgba(255,84,112,0.06);
}
.analysis-item.is-blank {
  border-color: rgba(255,255,255,0.08);
}
.analysis-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}
.analysis-status {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 13px;
  flex-shrink: 0;
}
.analysis-item.is-correct .analysis-status {
  background: #38d9a9;
  color: #1a0e05;
}
.analysis-item.is-wrong .analysis-status {
  background: #ff5470;
  color: #1a0e05;
}
.analysis-item.is-blank .analysis-status {
  background: rgba(255,255,255,0.1);
  color: var(--text-dim);
}
.analysis-q-num {
  font-size: 14px;
  font-weight: 800;
  color: var(--text);
}
.analysis-q-text {
  font-size: 14px;
  line-height: 1.85;
  color: var(--text);
  margin-bottom: 14px;
}
.analysis-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 14px;
}
.analysis-opt {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: rgba(255,255,255,0.03);
  border-radius: 10px;
  font-size: 13px;
  color: var(--text-dim);
}
.analysis-opt.is-user {
  background: rgba(255,84,112,0.1);
  border: 1px solid rgba(255,84,112,0.25);
  color: #ffc1cc;
}
.analysis-opt.is-correct {
  background: rgba(56,217,169,0.1);
  border: 1px solid rgba(56,217,169,0.25);
  color: #a3f0d3;
}
.analysis-opt-tag {
  font-size: 10px;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(0,0,0,0.3);
  margin-right: 6px;
}
.analysis-explain {
  background: rgba(0,0,0,0.25);
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 14px;
  line-height: 1.9;
  color: var(--text);
}
.analysis-explain h4 {
  font-size: 13px;
  font-weight: 800;
  color: var(--orange);
  margin-bottom: 8px;
}
.analysis-explain p {
  margin: 0;
}
</style>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';

  // ══════ مرحله ۱ → ۲ → ۳ ══════
  const form = document.getElementById('genForm');
  const step1 = document.getElementById('genStep1');
  const step2 = document.getElementById('genStep2');
  const step3 = document.getElementById('genStep3');
  const step4 = document.getElementById('genStep4');
  const step5 = document.getElementById('genStep5');

  function showStep(el) {
    [step1, step2, step3, step4, step5].forEach(function(s) {
      s.classList.remove('is-active');
    });
    el.classList.add('is-active');
    el.scrollIntoView({behavior: 'smooth', block: 'start'});
  }

  let quizData = null;       // questions array
  let userAnswers = [];      // {q, answer, blank}
  let currentIdx = 0;
  let timerInterval = null;
  let timerSeconds = 0;
  let timerTotal = 0;

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (form.querySelector('button[type=submit]').disabled) return;
    const fd = new FormData(form);
    fd.append('csrf', csrf);
    fd.append('agent', 'generate');

    showStep(step2);
    try {
      const r = await fetch(base + '/api/agent_run.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const data = await r.json();
      if (data.error) {
        alert(data.error);
        showStep(step1);
        return;
      }
      // پارس کردن خروجی AI به ساختار سوالات
      quizData = parseQuestions(data.result);
      if (!quizData || !quizData.length) {
        alert('سؤالی تولید نشد. دوباره تلاش کن.');
        showStep(step1);
        return;
      }
      userAnswers = quizData.map(function() { return null; });
      currentIdx = 0;
      startQuiz();
    } catch (err) {
      alert('خطا: ' + err.message);
      showStep(step1);
    }
  });

  function parseQuestions(text) {
    // خروجی AI: شماره‌گذاری سؤال ۱، ۲، ... + گزینه‌ها + پاسخ صحیح
    const questions = [];
    const blocks = text.split(/(?=سؤال\s*[۰-۹0-9]+\s*[:：]|\*\*سؤال\s*[۰-۹0-9]+\*\*)/);
    for (const b of blocks) {
      const m = b.match(/سؤال\s*([۰-۹0-9]+)[:：](.*?)(?=سؤال\s*[۰-۹0-9]+[:：]|$)/s);
      if (!m) continue;
      const num = parseInt(m[1].replace(/[۰-۹]/g, function(d){return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);}));
      const body = m[2].trim();
      const opts = [];
      const optRegex = /([الف-ی])\)\s*([^\n]+)/g;
      let om;
      while ((om = optRegex.exec(body)) !== null) {
        opts.push({letter: om[1], text: om[2].trim()});
      }
      const ansMatch = body.match(/پاسخ[:\s]*([الف-ی\d])/);
      let correctLetter = ansMatch ? ansMatch[1] : (opts[0] ? opts[0].letter : '');
      // اگه پاسخ عددی بود (مثل ۳)، فرض می‌کنیم گزینهٔ الف=۱، ب=۲...
      const bodyText = body.replace(/\*\*[^*]+\*\*/g, '').trim();
      questions.push({
        number: num,
        text: bodyText.split('\n')[0].replace(/^[الف-ی]\)\s*/, '').trim(),
        options: opts.length >= 2 ? opts : [],
        correct: correctLetter,
        explanation: body
      });
    }
    return questions;
  }

  function startQuiz() {
    showStep(step3);
    timerTotal = quizData.length * 120;
    timerSeconds = timerTotal;
    updateTimer();
    timerInterval = setInterval(function() {
      timerSeconds--;
      updateTimer();
      if (timerSeconds <= 0) finishQuiz();
    }, 1000);
    renderQuestion();
    renderDots();
  }

  function updateTimer() {
    const m = Math.floor(timerSeconds / 60);
    const s = timerSeconds % 60;
    const el = document.getElementById('quizTimer');
    el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    el.classList.toggle('is-low', timerSeconds < 300);
    el.classList.toggle('is-critical', timerSeconds < 60);
  }

  function renderQuestion() {
    const q = quizData[currentIdx];
    const ans = userAnswers[currentIdx];
    document.getElementById('quizCurrent').textContent = String(currentIdx + 1).fa ? (currentIdx + 1) : numFa(currentIdx + 1);
    document.getElementById('quizTotal').textContent = numFa(quizData.length);
    document.getElementById('quizProgress').style.width = ((currentIdx + 1) / quizData.length * 100) + '%';

    let optsHtml = '';
    if (q.options && q.options.length) {
      optsHtml = '<div class="quiz-options">';
      q.options.forEach(function(o) {
        const isSelected = ans === o.letter;
        optsHtml += '<div class="quiz-option' + (isSelected ? ' is-selected' : '') + '" data-letter="' + o.letter + '">';
        optsHtml += '<div class="quiz-option-letter">' + o.letter + '</div>';
        optsHtml += '<div class="quiz-option-text">' + o.text + '</div>';
        optsHtml += '</div>';
      });
      optsHtml += '</div>';
    } else {
      optsHtml = '<textarea class="agent-textarea" placeholder="پاسخت رو اینجا بنویس..." style="min-height: 120px;"></textarea>';
    }

    document.getElementById('quizCard').innerHTML =
      '<div class="quiz-q-num">سؤال ' + numFa(currentIdx + 1) + ' از ' + numFa(quizData.length) + '</div>' +
      '<div class="quiz-q-text">' + q.text + '</div>' +
      optsHtml;

    document.querySelectorAll('.quiz-option').forEach(function(el) {
      el.addEventListener('click', function() {
        userAnswers[currentIdx] = el.dataset.letter;
        renderQuestion();
        renderDots();
      });
    });

    const textarea = document.querySelector('#quizCard textarea');
    if (textarea) {
      if (ans && ans.startsWith('text:')) textarea.value = ans.slice(5);
      textarea.addEventListener('input', function() {
        userAnswers[currentIdx] = 'text:' + textarea.value;
        renderDots();
      });
    }
  }

  function renderDots() {
    const dotsEl = document.getElementById('quizDots');
    let html = '';
    for (let i = 0; i < quizData.length; i++) {
      const isAns = userAnswers[i] !== null && userAnswers[i] !== '';
      const isCur = i === currentIdx;
      html += '<div class="quiz-dot' + (isCur ? ' is-current' : '') + (isAns && !isCur ? ' is-answered' : '') + '" data-idx="' + i + '">' + numFa(i + 1) + '</div>';
    }
    dotsEl.innerHTML = html;
    dotsEl.querySelectorAll('.quiz-dot').forEach(function(d) {
      d.addEventListener('click', function() {
        currentIdx = parseInt(d.dataset.idx);
        renderQuestion();
      });
    });
  }

  document.getElementById('quizPrev').addEventListener('click', function() {
    if (currentIdx > 0) { currentIdx--; renderQuestion(); }
  });
  document.getElementById('quizNext').addEventListener('click', function() {
    if (currentIdx < quizData.length - 1) { currentIdx++; renderQuestion(); }
    else finishQuiz();
  });

  function finishQuiz() {
    clearInterval(timerInterval);
    let correct = 0, wrong = 0, blank = 0;
    userAnswers.forEach(function(a, i) {
      const ca = quizData[i].correct;
      const isAnswered = a !== null && a !== '' && !(typeof a === 'string' && a.startsWith('text:') && a.length === 5);
      if (!isAnswered) blank++;
      else if (a === ca) correct++;
      else wrong++;
    });
    const total = quizData.length;
    const score = Math.round((correct / total) * 100);
    const elapsed = timerTotal - timerSeconds;

    document.getElementById('scoreValue').textContent = numFa(score) + '٪';
    document.getElementById('scoreText').textContent =
      score >= 80 ? '🎉 عالی! سطح بالایی داری' :
      score >= 60 ? '👍 خوبه! فقط چند نکتهٔ کوچیک' :
      score >= 40 ? '📚 قابل قبول، ولی جا برای بهتر شدن هست' :
      '💪 ناامید نشو، با تمرین بهتر می‌شی';
    document.getElementById('scoreCorrect').textContent = numFa(correct);
    document.getElementById('scoreWrong').textContent = numFa(wrong);
    document.getElementById('scoreBlank').textContent = numFa(blank);
    const m = Math.floor(elapsed / 60);
    const s = elapsed % 60;
    document.getElementById('scoreTime').textContent = numFa(m) + ':' + numFa(s);
    showStep(step4);
  }

  document.getElementById('goToAnalysis').addEventListener('click', function() {
    buildAnalysis();
    showStep(step5);
  });

  function buildAnalysis() {
    const list = document.getElementById('analysisList');
    let html = '';
    quizData.forEach(function(q, i) {
      const ua = userAnswers[i];
      const isAnswered = ua !== null && ua !== '' && !(typeof ua === 'string' && ua.startsWith('text:') && ua.length === 5);
      const isCorrect = isAnswered && ua === q.correct;
      const status = !isAnswered ? 'blank' : (isCorrect ? 'correct' : 'wrong');

      html += '<div class="analysis-item is-' + status + '">';
      html += '<div class="analysis-head">';
      html += '<div class="analysis-status">' + (status === 'correct' ? '✓' : status === 'wrong' ? '✗' : '—') + '</div>';
      html += '<div class="analysis-q-num">سؤال ' + numFa(i + 1) + '</div>';
      html += '</div>';
      html += '<div class="analysis-q-text">' + q.text + '</div>';

      if (q.options && q.options.length) {
        html += '<div class="analysis-options">';
        q.options.forEach(function(o) {
          const isUser = ua === o.letter;
          const isCorrectOpt = q.correct === o.letter;
          html += '<div class="analysis-opt' + (isUser ? ' is-user' : '') + (isCorrectOpt ? ' is-correct' : '') + '">';
          if (isCorrectOpt) html += '<span class="analysis-opt-tag">پاسخ درست</span>';
          else if (isUser) html += '<span class="analysis-opt-tag">پاسخ تو</span>';
          html += '<b>' + o.letter + ')</b> ' + o.text;
          html += '</div>';
        });
        html += '</div>';
      }

      if (status !== 'correct' && q.explanation) {
        html += '<div class="analysis-explain">';
        html += '<h4>' + (status === 'blank' ? 'پاسخ صحیح و توضیح:' : 'چرا اشتباه بود؟') + '</h4>';
        html += '<p>' + q.explanation.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>') + '</p>';
        html += '</div>';
      }
      html += '</div>';
    });
    list.innerHTML = html;
  }

  document.getElementById('restartQuiz').addEventListener('click', function() {
    form.reset();
    showStep(step1);
  });
  document.getElementById('analysisNewTest').addEventListener('click', function() {
    form.reset();
    showStep(step1);
  });

  function numFa(n) {
    const map = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return String(n).split('').map(function(c){ return map[parseInt(c)] || c; }).join('');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
