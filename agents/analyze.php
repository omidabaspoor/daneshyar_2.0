<?php
/**
 * دانش‌یار - دستیار تحلیل برگه (جریان چندمرحله‌ای)
 *
 * مرحلهٔ ۱: آپلود برگه + پاسخ‌برگ
 * مرحلهٔ ۲: تحلیل کلی (نمودار، درس‌به‌درس)
 * مرحلهٔ ۳: مرور سؤال‌به‌سؤال + یادگیری اشتباهات
 */
require_once __DIR__ . '/../includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/agents/analyze.php'));
}
if (is_banned($user)) { redirect(BASE_URL . '/logout.php'); }

$page = 'agents';
$pageTitle = 'تحلیل برگه';
$seoRobots = 'noindex,nofollow';

$quotaCheck = can_send_message($user);
include __DIR__ . '/../includes/header.php';
?>

<div class="agent-page">
  <div class="agent-nav-bar">
    <a href="<?= BASE_URL ?>/dashboard.php"><?= icon('arrow-right') ?> بازگشت</a>
    <span class="agent-nav-current">تحلیل برگه</span>
  </div>

  <section class="agent-hero" data-agent="analyze">
    <div class="agent-hero-row">
      <div class="agent-hero-ico"><?= icon('graph') ?></div>
      <div>
        <h1 class="agent-hero-title">تحلیل برگه</h1>
        <p class="agent-hero-sub">برگه آزمون و پاسخ‌برگت رو بفرست. یه گزارش کامل می‌گیری: هر سؤال چرا اشتباه شد، چیکار باید بکنی، و یادگیری هر مبحث ضعیف.</p>
        <div class="agent-hero-meta">
          <span class="pill"><?= icon('check') ?> تحلیل سؤال‌به‌سؤال</span>
          <span class="pill"><?= icon('check') ?> یادگیری اشتباهات</span>
          <span class="pill"><?= icon('check') ?> برنامهٔ بهبود</span>
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

  <!-- ══════ مرحله ۱: آپلود ══════ -->
  <section id="anStep1" class="an-step is-active">
    <form class="agent-panel" id="anForm" enctype="multipart/form-data">
      <div class="agent-panel-head">
        <div class="agent-panel-title"><?= icon('screenshot') ?> برگهٔ آزمون</div>
        <span class="agent-panel-hint"><?= icon('info') ?> عکس یا PDF</span>
      </div>

      <label class="agent-upload" style="display: flex; width: 100%; justify-content: center; padding: 18px;">
        <input type="file" name="exam" id="examFile" accept="image/*,application/pdf" style="display:none" required>
        <?= icon('image') ?> آپلود برگهٔ آزمون
      </label>
      <div class="agent-upload-preview" id="examPreview"></div>

      <div class="agent-panel-head" style="margin-top: 16px;">
        <div class="agent-panel-title"><?= icon('edit') ?> پاسخ‌برگ</div>
        <span class="agent-panel-hint"><?= icon('info') ?> اختیاری ولی مفید</span>
      </div>
      <label class="agent-upload" style="display: flex; width: 100%; justify-content: center; padding: 18px;">
        <input type="file" name="answers" id="answersFile" accept="image/*,application/pdf" style="display:none">
        <?= icon('image') ?> آپلود پاسخ‌برگ (اختیاری)
      </label>
      <div class="agent-upload-preview" id="answersPreview"></div>

      <div class="agent-panel-head" style="margin-top: 16px;">
        <div class="agent-panel-title"><?= icon('book') ?> مشخصات آزمون</div>
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

      <textarea class="agent-textarea" name="context" placeholder="اگه نکته خاصی هست (مثلاً «آزمون ۲۰ نمره‌ای، تایم ۴۵ دقیقه»، یا «۵ سؤال اول تستی، بقیه تشریحی») بنویس. اختیاریه." style="margin-top: 12px; min-height: 70px;"></textarea>

      <button type="submit" class="agent-send" id="anBtn" <?= !$quotaCheck['ok'] ? 'disabled' : '' ?>>
        <?= icon('graph') ?> تحلیل کن
      </button>
    </form>
  </section>

  <!-- ══════ مرحله ۲: تحلیل ══════ -->
  <section id="anStep2" class="an-step">
    <div class="an-loading">
      <div class="gen-loading-icon"><?= icon('graph') ?></div>
      <h3>در حال تحلیل برگه...</h3>
      <p>هوش مصنوعی داره هر سؤال رو بررسی می‌کنه و دلیل خطا رو پیدا می‌کنه.</p>
      <div class="gen-loading-bar"><div class="gen-loading-fill"></div></div>
    </div>
  </section>

  <!-- ══════ مرحله ۳: نتیجه کلی + یادگیری ══════ -->
  <section id="anStep3" class="an-step">
    <div class="scorecard glass">
      <div class="scorecard-head">
        <div class="scorecard-icon"><?= icon('graph') ?></div>
        <div>
          <div class="scorecard-label">گزارش کلی آزمون</div>
          <div class="scorecard-value" id="anScoreValue">—</div>
          <div class="scorecard-text" id="anScoreText">—</div>
        </div>
      </div>
      <div class="scorecard-grid">
        <div class="scorecard-cell is-correct">
          <div class="scorecard-cell-num" id="anCorrect">—</div>
          <div class="scorecard-cell-label">درست</div>
        </div>
        <div class="scorecard-cell is-wrong">
          <div class="scorecard-cell-num" id="anWrong">—</div>
          <div class="scorecard-cell-label">نادرست</div>
        </div>
        <div class="scorecard-cell is-blank">
          <div class="scorecard-cell-num" id="anBlank">—</div>
          <div class="scorecard-cell-label">نزده</div>
        </div>
        <div class="scorecard-cell is-time">
          <div class="scorecard-cell-num" id="anTopics">—</div>
          <div class="scorecard-cell-label">مباحث ضعیف</div>
        </div>
      </div>
    </div>

    <div class="an-actions">
      <button class="btn btn-primary" id="goToAnalysis"><?= icon('sparkle') ?> یادگیری هر سؤال اشتباه</button>
      <button class="btn btn-ghost" id="restartAnalyze"><?= icon('refresh') ?> برگهٔ جدید</button>
    </div>

    <div id="analysisList" class="an-analysis"></div>
  </section>
</div>

<style>
.an-step { display: none; }
.an-step.is-active { display: block; animation: fadeUp 0.3s ease; }
.an-loading {
  text-align: center;
  padding: 60px 20px;
  background: linear-gradient(160deg, rgba(255,255,255,0.05), rgba(255,255,255,0.012));
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  backdrop-filter: blur(20px);
}
.an-loading h3 { font-size: 22px; margin-bottom: 10px; }
.an-loading p { color: var(--text-dim); font-size: 14px; max-width: 380px; margin: 0 auto 24px; line-height: 1.8; }
.an-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin: 16px 0 20px;
}
@media (min-width: 720px) {
  .an-actions { flex-direction: row; }
}

.an-analysis { margin-top: 8px; }

.recommendation-box {
  padding: 18px;
  background: linear-gradient(135deg, rgba(77,171,247,0.12), rgba(77,171,247,0.04));
  border: 1px solid rgba(77,171,247,0.28);
  border-radius: 16px;
  margin-bottom: 16px;
}
.recommendation-box h3 {
  font-size: 15px;
  color: #cfe8ff;
  margin-bottom: 10px;
  font-weight: 800;
  display: flex;
  align-items: center;
  gap: 8px;
}
.recommendation-box ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.recommendation-box li {
  padding: 8px 0;
  font-size: 13.5px;
  line-height: 1.85;
  color: var(--text-dim);
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.recommendation-box li:last-child { border-bottom: 0; }
.recommendation-box li b { color: var(--text); }
</style>

<script>
(function(){
  const csrf = '<?= csrf_token() ?>';
  const base = '<?= BASE_URL ?>';

  // پیش‌نمایش فایل
  function bindFile(input, preview) {
    input.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const f = this.files[0];
        preview.classList.add('is-visible');
        preview.innerHTML = '<span>'+ f.name +' ('+ Math.round(f.size/1024) +' KB)</span><button type="button" onclick="this.parentElement.classList.remove(\'is-visible\'); this.parentElement.previousElementSibling.querySelector(\'input\').value=\'\';">×</button>';
      }
    });
  }
  bindFile(document.getElementById('examFile'), document.getElementById('examPreview'));
  bindFile(document.getElementById('answersFile'), document.getElementById('answersPreview'));

  const form = document.getElementById('anForm');
  const step1 = document.getElementById('anStep1');
  const step2 = document.getElementById('anStep2');
  const step3 = document.getElementById('anStep3');
  const btn = document.getElementById('anBtn');

  function showStep(el) {
    [step1, step2, step3].forEach(function(s) { s.classList.remove('is-active'); });
    el.classList.add('is-active');
    el.scrollIntoView({behavior:'smooth', block:'start'});
  }

  let analyzeData = null;

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;border-top-color:#1a0e05"></span> در حال تحلیل...';
    showStep(step2);

    try {
      const fd = new FormData(form);
      fd.append('csrf', csrf);
      fd.append('agent', 'analyze');

      const r = await fetch(base + '/api/agent_run.php', {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const data = await r.json();
      if (data.error) {
        alert(data.error);
        showStep(step1);
        return;
      }
      analyzeData = parseAnalysis(data.result);
      buildSummary();
      showStep(step3);
    } catch (err) {
      alert('خطا: ' + err.message);
      showStep(step1);
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<?= icon('graph') ?> تحلیل کن';
    }
  });

  function parseAnalysis(text) {
    const result = {
      total: 0, correct: 0, wrong: 0, blank: 0,
      questions: [],
      recommendations: []
    };
    // استخراج آمار کلی
    const stats = text.match(/(\d+)\s*درست|درست[:\s]*(\d+)/g);
    // شمارش سؤالات
    const qMatches = text.matchAll(/سؤال\s*([۰-۹0-9]+)[:\s]([\s\S]*?)(?=سؤال\s*[۰-۹0-9]+[:\s]|$)/g);
    for (const m of qMatches) {
      const body = m[2].trim();
      const num = parseInt(m[1].replace(/[۰-۹]/g, function(d){return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);}));
      const statusMatch = body.match(/وضعیت[:\s]*(درست|نادرست|نزده)/);
      const reasonMatch = body.match(/(?:علت خطا|علت)[:\s]*([^\n]+(?:\n[^\n]+)?)/);
      const correctMatch = body.match(/پاسخ صحیح[:\s]*([^\n]+)/);
      const explainMatch = body.match(/(?:توضیح|پاسخ و توضیح)[:\s]*([\s\S]+?)(?=---|$)/);

      result.questions.push({
        number: num,
        body: body,
        status: statusMatch ? statusMatch[1] : '—',
        reason: reasonMatch ? reasonMatch[1].trim() : '',
        correct: correctMatch ? correctMatch[1].trim() : '',
        explanation: explainMatch ? explainMatch[1].trim() : ''
      });

      if (statusMatch) {
        if (statusMatch[1] === 'درست') result.correct++;
        else if (statusMatch[1] === 'نادرست') result.wrong++;
        else if (statusMatch[1] === 'نزده') result.blank++;
      }
      result.total++;
    }
    return result;
  }

  function buildSummary() {
    const score = analyzeData.total > 0 ? Math.round((analyzeData.correct / analyzeData.total) * 100) : 0;
    document.getElementById('anScoreValue').textContent = numFa(score) + '٪';
    document.getElementById('anScoreText').textContent =
      score >= 80 ? '🎉 عملکرد عالی' :
      score >= 60 ? '👍 خوب، با کمی تمرین بهتر می‌شی' :
      score >= 40 ? '📚 قابل قبول، ولی نیاز به تلاش بیشتر' :
      '💪 ناامید نشو، از اشتباهاتت یاد بگیر';
    document.getElementById('anCorrect').textContent = numFa(analyzeData.correct);
    document.getElementById('anWrong').textContent = numFa(analyzeData.wrong);
    document.getElementById('anBlank').textContent = numFa(analyzeData.blank);
    document.getElementById('anTopics').textContent = numFa(analyzeData.questions.filter(function(q){return q.status === 'نادرست' || q.status === 'نزده';}).length);
  }

  document.getElementById('goToAnalysis').addEventListener('click', function() {
    buildAnalysisList();
    document.getElementById('analysisList').scrollIntoView({behavior:'smooth', block:'start'});
  });

  document.getElementById('restartAnalyze').addEventListener('click', function() {
    form.reset();
    document.getElementById('examPreview').classList.remove('is-visible');
    document.getElementById('answersPreview').classList.remove('is-visible');
    showStep(step1);
  });

  function buildAnalysisList() {
    const list = document.getElementById('analysisList');
    let html = '<div class="analysis-header"><h2>یادگیری اشتباهات</h2><p>هر سؤالی که اشتباه جواب دادی، دلیلش و روش صحیحش رو ببین.</p></div>';

    // توصیه‌های کلی
    const wrongOnes = analyzeData.questions.filter(function(q){return q.status === 'نادرست' || q.status === 'نزده';});
    if (wrongOnes.length > 0) {
      html += '<div class="recommendation-box">';
      html += '<h3><?php echo icon('flash') ?> پیشنهاد برای بهبود</h3>';
      html += '<ul>';
      html += '<li><b>تمرکز روی مباحث نادرست:</b> با دستیار «معلم خصوصی» هر مبحث رو دوباره یاد بگیر.</li>';
      html += '<li><b>تست بیشتر بزن:</b> با دستیار «تست‌ساز» از همین مباحث تست جدید بساز.</li>';
      html += '<li><b>جزوه بساز:</b> با دستیار «جزوه‌ساز» خلاصهٔ نکات کلیدی رو داشته باش.</li>';
      html += '</ul>';
      html += '</div>';
    }

    analyzeData.questions.forEach(function(q, i) {
      const status = q.status;
      const isCorrect = status === 'درست';
      const isBlank = status === 'نزده';

      html += '<div class="analysis-item is-' + (isCorrect ? 'correct' : isBlank ? 'blank' : 'wrong') + '">';
      html += '<div class="analysis-head">';
      html += '<div class="analysis-status">' + (isCorrect ? '✓' : isBlank ? '—' : '✗') + '</div>';
      html += '<div class="analysis-q-num">سؤال ' + numFa(q.number) + '</div>';
      html += '</div>';
      html += '<div class="analysis-q-text">' + q.body.split('\n')[0] + '</div>';

      if (!isCorrect) {
        html += '<div class="analysis-explain">';
        if (q.reason) {
          html += '<h4>علت خطا:</h4><p>' + q.reason + '</p>';
        }
        if (q.correct) {
          html += '<h4>پاسخ صحیح:</h4><p>' + q.correct + '</p>';
        }
        if (q.explanation) {
          html += '<h4>یاد بگیر:</h4><p>' + q.explanation.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>') + '</p>';
        }
        html += '</div>';
      }
      html += '</div>';
    });

    if (wrongOnes.length === 0) {
      html += '<div class="agent-empty"><div class="agent-empty-ico"><?= icon('check') ?></div><div class="agent-empty-title">هیچ سؤال اشتباهی نداشتی! 🎉</div><div class="agent-empty-desc">عملکردت عالیه. ادامه بده!</div></div>';
    }

    list.innerHTML = html;
  }

  function numFa(n) {
    const map = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return String(n).split('').map(function(c){ return map[parseInt(c)] || c; }).join('');
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
