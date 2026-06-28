<?php
/**
 * دانش‌یار - صفحه اصلی (لندینگ)
 */
require_once __DIR__ . '/includes/functions.php';

$user = current_user();
if ($user) {
    redirect(BASE_URL . '/dashboard.php');
}

$page = 'home';
$pageTitle = 'دستیار هوشمند آموزشی';
$seoTitle = 'دانش‌یار | دستیار هوشمند آموزشی پایه هفتم تا دوازدهم و کنکور';
$seoDescription = 'دانش‌یار، دستیار هوشمند آموزشی برای دانش‌آموزان و کنکوری‌ها. حل سوال، توضیح درس، تحلیل آزمون، مشاورهٔ تحصیلی، ساخت تست، جمع‌بندی و جزوه.';
$seoKeywords = 'دانش یار, دانش‌یار, هوش مصنوعی آموزشی, حل سوال درسی, معلم خصوصی, تحلیل آزمون, مشاور تحصیلی, کنکور, پایه هفتم, پایه دوازدهم';
include __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero-text">
    <span class="hero-badge"><?= icon('sparkle') ?> ۱۰ دستیار تخصصی برای هر نیاز درسی</span>
    <h1>هر سؤال درسی،<br><span class="grad">یه دستیار آماده</span></h1>
    <p class="hero-desc">دانش‌یار فقط یه چت ساده نیست. ۱۰ ایجنت تخصصی داره: <b>حل سوال، توضیح درس، تحلیل آزمون، ساخت تست، مشاورهٔ تحصیلی، برنامه‌ریزی، جزوه، جمع‌بندی شب امتحان، انتخاب رشته و فلش‌کارت</b>. هر کدوم یه کار متفاوت انجام می‌ده، با هم یه دستیار همه‌فن‌حریف می‌شن.</p>

    <div class="hero-stats">
      <div class="hstat">
        <div class="hstat-v">۱۰</div>
        <div class="hstat-l">دستیار تخصصی</div>
      </div>
      <div class="hstat">
        <div class="hstat-v">۷ تا ۱۲</div>
        <div class="hstat-l">پایه تحصیلی</div>
      </div>
      <div class="hstat">
        <div class="hstat-v">۲۴/۷</div>
        <div class="hstat-l">همیشه آماده</div>
      </div>
    </div>

    <div class="hero-actions">
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-hero">
        <?= icon('rocket') ?> رایگان شروع کن
      </a>
      <a href="<?= BASE_URL ?>/pricing.php" class="btn btn-ghost btn-hero">
        <?= icon('crown') ?> پلن‌ها
      </a>
    </div>

    <div class="hero-trust">
      <?= icon('shield') ?> <span>۳ پیام رایگان روزانه، بدون نیاز به کارت بانکی</span>
    </div>
  </div>

  <!-- Mock Chat -->
  <div class="hero-chat-mock">
    <div class="hcm-header">
      <div class="hcm-dots"><span></span><span></span><span></span></div>
      <div class="hcm-title">دانش‌یار · یکی از ۱۰ دستیار</div>
      <div class="hcm-status"><span class="hcm-pulse"></span> آنلاین</div>
    </div>

    <div class="hcm-body">
      <div class="hcm-msg hcm-user">
        <div class="hcm-bub">
          <span class="hcm-text">چطوری مشتق رو بفهمم؟ هرچی می‌خونم نمی‌فهمم 😩</span>
        </div>
      </div>

      <div class="hcm-msg hcm-ai">
        <div class="hcm-avatar"><img src="<?= BASE_URL ?>/assets/img/logo.png" alt="" width="32" height="32"></div>
        <div class="hcm-bub">
          <div class="hcm-answer">🎯 <b>بیا از پایه شروع کنیم</b></div>
          <div class="hcm-explain">
            مشتق یعنی <b>سرعت تغییر</b>. مثلاً وقتی ماشین می‌ره، سرعتش همون مشتقه.<br>
            <span class="hcm-formula">f'(x) = lim Δx→0 (f(x+Δx) - f(x)) / Δx</span><br>
            یعنی: <b>تغییرات تابع ÷ تغییرات x</b>، وقتی تغییرات خیلی کوچیک بشه.
          </div>
        </div>
      </div>

      <div class="hcm-typing">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>
</section>

<!-- ============ بنر معرفی ایجنت‌ها ============ -->
<section class="exam-banner glass">
  <div class="exam-bg-shape"></div>
  <div class="exam-content">
    <div class="exam-icon"><?= icon('sparkle') ?></div>
    <h2>۱۰ دستیار، هر کدوم یه کار متفاوت</h2>
    <p>دانش‌یار فقط چت نیست. از <b>تحلیل آزمون</b> و <b>مشاورهٔ کنکور</b> تا <b>جمع‌بندی شب امتحان</b> و <b>فلش‌کارت هوشمند</b>. هر ایجنت تخصصیه و یه کار مشخص رو بهتر از بقیه انجام می‌ده.</p>
    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-hero">
      <?= icon('rocket') ?> رایگان شروع کن
    </a>
  </div>
</section>

<!-- ============ ایجنت‌ها ============ -->
<section class="features-wrap">
  <h2 class="section-title">دستیارهای <span class="grad">دانش‌یار</span></h2>
  <p class="section-sub">هر کدوم رو جداگانه استفاده کن یا همه رو با هم 🎯</p>

  <div class="agent-grid">
    <?php foreach (agents_registry() as $key => $a): ?>
      <a href="<?= e(BASE_URL . '/' . ($a['standalone'] ? 'agents/' . $key . '.php' : ($a['href'] ?? 'chat.php'))) ?>" class="agent-card" data-agent="<?= e($key) ?>">
        <div class="agent-card-head">
          <div class="agent-icon"><?= icon($a['icon']) ?></div>
          <div class="agent-card-title-wrap">
            <h3 class="agent-card-title">
              <?= e($a['title']) ?>
              <?php if (!empty($a['pro'])): ?><span class="badge-pro">PRO</span><?php endif; ?>
            </h3>
            <div class="agent-card-desc"><?= e($a['short_desc']) ?></div>
          </div>
        </div>
        <div class="agent-card-foot">
          <span class="agent-card-tag"><?= e($a['tag']) ?></span>
          <span class="agent-card-cta"><?= icon('arrow-left') ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ سه قدم ============ -->
<section class="steps-wrap">
  <h2 class="section-title">فقط در ۳ قدم</h2>
  <p class="section-sub">بدون پیچیدگی؛ انتخاب، ارسال، یادگیری</p>

  <div class="steps">
    <div class="step glass">
      <div class="step-num">1</div>
      <div class="step-icon"><?= icon('plus') ?></div>
      <h3>ثبت‌نام کن</h3>
      <p>فقط با موبایل، ۳۰ ثانیه‌ای</p>
    </div>
    <div class="step-arrow"><?= icon('arrow-left') ?></div>
    <div class="step glass">
      <div class="step-num">2</div>
      <div class="step-icon"><?= icon('sparkle') ?></div>
      <h3>دستیار رو انتخاب کن</h3>
      <p>هر سؤال، یه دستیار مخصوص</p>
    </div>
    <div class="step-arrow"><?= icon('arrow-left') ?></div>
    <div class="step glass">
      <div class="step-num">3</div>
      <div class="step-icon"><?= icon('check') ?></div>
      <h3>واقعاً یاد بگیر</h3>
      <p>جواب، توضیح و راهنمایی یکجا</p>
    </div>
  </div>
</section>

<!-- ============ سوالات پرتکرار ============ -->
<section class="faq-wrap">
  <h2 class="section-title">سوالات پرتکرار</h2>
  <p class="section-sub">هر چیزی که قبل از شروع باید بدونی</p>
  <div class="faq-grid">
    <div class="faq-item glass">
      <h3>دانش‌یار دقیقاً چه کاری می‌کنه؟</h3>
      <p>یه پلتفرم آموزشی با ۱۰ دستیار هوش مصنوعی تخصصیه؛ از حل سوال و توضیح درس تا تحلیل آزمون و مشاورهٔ کنکور.</p>
    </div>
    <div class="faq-item glass">
      <h3>چه فرقی با بقیه اپ‌ها داره؟</h3>
      <p>ما فقط چت نیستیم. هر نیاز درسی (حل، توضیح، تست، جمع‌بندی، مشاوره) یه دستیار جداگونه با کار تخصصی.</p>
    </div>
    <div class="faq-item glass">
      <h3>چه پایه‌هایی پشتیبانی می‌شه؟</h3>
      <p>پایهٔ هفتم تا دوازدهم، هر سه رشتهٔ ریاضی، تجربی و انسانی. مخصوص کنکوری‌ها هم دستیار جداگونه داریم.</p>
    </div>
    <div class="faq-item glass">
      <h3>رایگانه؟</h3>
      <p>۳ پیام رایگان روزانه برای همه. برای استفادهٔ نامحدود، پلن‌های اشتراک با قیمت مناسب فعاله.</p>
    </div>
    <div class="faq-item glass">
      <h3>می‌تونم عکس سؤال بفرستم؟</h3>
      <p>بله. در دستیار «حل سوال» می‌تونی عکس یا PDF بفرستی. در دستیار «تحلیل آزمون» هم برگه آزمون و پاسخ‌برگ.</p>
    </div>
    <div class="faq-item glass">
      <h3>پاسخ‌ها دقیقن؟</h3>
      <p>پاسخ‌ها بر اساس کتاب درسی رسمی ایران و با دقت بالا تولید می‌شن. ولی همیشه با معلم یا منبع رسمی هم چک کن.</p>
    </div>
  </div>
</section>

<script type="application/ld+json">
<?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => [
    ['@type'=>'Question','name'=>'دانش‌یار دقیقاً چه کاری می‌کند؟','acceptedAnswer'=>['@type'=>'Answer','text'=>'یک پلتفرم آموزشی با ۱۰ دستیار هوش مصنوعی تخصصی برای دانش‌آموزان پایه هفتم تا دوازدهم و کنکوری‌ها.']],
    ['@type'=>'Question','name'=>'چه فرقی با بقیه اپ‌ها دارد؟','acceptedAnswer'=>['@type'=>'Answer','text'=>'ما فقط چت نیستیم؛ هر نیاز درسی یک دستیار جداگانه با کار تخصصی دارد.']],
    ['@type'=>'Question','name'=>'چه پایه‌هایی پشتیبانی می‌شود؟','acceptedAnswer'=>['@type'=>'Answer','text'=>'پایه هفتم تا دوازدهم، هر سه رشته ریاضی، تجربی و انسانی. دستیار ویژه کنکوری‌ها نیز موجود است.']],
    ['@type'=>'Question','name'=>'رایگان است؟','acceptedAnswer'=>['@type'=>'Answer','text'=>'۳ پیام رایگان روزانه برای همه؛ برای استفاده نامحدود پلن‌های اشتراک با قیمت مناسب فعال است.']],
  ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<!-- ============ CTA نهایی ============ -->
<section class="final-cta">
  <div class="glass glass-orange final-cta-card">
    <h2>یادگیری جدی، با ۱۰ دستیار کنارت 🚀</h2>
    <p>هر نیاز درسی یه دستیار مخصوص. از حل سوال تا مشاورهٔ کنکور، از جمع‌بندی شب امتحان تا ساخت جزوه. همه توی یه پلتفرم.</p>
    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-hero">
      <?= icon('sparkle') ?> شروع با ۳ پیام رایگان
    </a>
    <small>💎 بدون کارت بانکی، ۳۰ ثانیه ثبت‌نام</small>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
