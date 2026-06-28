<?php
/**
 * دانش‌یار - لندینگ حرفه‌ای (ریدیزاین کامل)
 */
require_once __DIR__ . '/includes/functions.php';

$user = current_user();
if ($user) redirect(BASE_URL . '/dashboard.php');

$page = 'home';
$pageTitle = 'دستیار هوشمند آموزشی';
include __DIR__ . '/includes/header.php';
?>

<style>
/* ========================================================
   LANDING - PROFESSIONAL & SYMMETRICAL
   ======================================================== */

.hero {
  padding: 70px 0 100px;
  text-align: center;
}

.hero h1 {
  font-size: 50px;
  line-height: 1.1;
  margin-bottom: 26px;
}

.hero-desc {
  font-size: 18.5px;
  max-width: 640px;
  margin: 0 auto 48px;
  color: #9ea0b3;
}

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 60px;
  margin-bottom: 48px;
}

.hstat-v {
  font-size: 36px;
  font-weight: 900;
  color: var(--orange);
}

.hstat-l {
  font-size: 14.5px;
  color: #6b6d80;
  margin-top: 6px;
}

/* SECTION */
.section-head {
  text-align: center;
  margin-bottom: 52px;
}

.section-title {
  font-size: 40px;
}

/* ========================================================
   UNIFORM CARDS - PERFECT SYMMETRY
   ======================================================== */

.about-grid,
.services-grid,
.agent-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 28px;
  align-items: stretch;
}

/* Card Base - Premium & Clean */
.about-card,
.service-item,
.agent-card {
  background: linear-gradient(145deg, #16161f, #0f0f17);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 26px;
  padding: 32px 30px;
  box-shadow: 0 18px 48px rgba(0,0,0,0.46);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 285px;
  position: relative;
  overflow: hidden;
}

.about-card:hover,
.service-item:hover,
.agent-card:hover {
  border-color: rgba(235,124,42,0.65);
  transform: translateY(-14px);
  box-shadow: 0 30px 68px rgba(0,0,0,0.56);
}

/* About & Services */
.about-icon,
.service-icon {
  width: 74px;
  height: 74px;
  margin: 0 auto 26px;
  background: rgba(235,124,42,0.13);
  border: 1px solid rgba(235,124,42,0.42);
  border-radius: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--orange);
  flex-shrink: 0;
}

.about-icon .ico,
.service-icon .ico {
  width: 36px;
  height: 36px;
}

.about-card h3,
.service-item h4 {
  font-size: 22px;
  font-weight: 800;
  margin-bottom: 18px;
  color: #fff;
  text-align: center;
}

.about-card p,
.service-item p {
  font-size: 16.5px;
  color: #9ea0b3;
  line-height: 1.95;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

/* Agent Cards */
.agent-card-head {
  display: flex;
  align-items: flex-start;
  gap: 20px;
  margin-bottom: 20px;
}

.agent-icon {
  width: 58px;
  height: 58px;
  background: rgba(235,124,42,0.14);
  border: 1px solid rgba(235,124,42,0.45);
  border-radius: 19px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--orange);
  flex-shrink: 0;
}

.agent-icon .ico {
  width: 30px;
  height: 30px;
}

.agent-card-title {
  font-size: 18px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 8px;
  line-height: 1.3;
}

.agent-card-desc {
  font-size: 15.5px;
  color: #9ea0b3;
  line-height: 1.85;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.agent-card-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: auto;
  padding-top: 22px;
  border-top: 1px solid rgba(255,255,255,0.07);
}

.agent-card-tag {
  font-size: 13px;
  padding: 6px 14px;
  background: rgba(255,255,255,0.05);
  border-radius: 999px;
  color: #6b6d80;
  font-weight: 600;
}

.agent-card-cta {
  color: var(--orange);
  font-size: 15.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ========================================================
   MOBILE - 2 CARDS PER ROW + PERFECT SYMMETRY
   ======================================================== */

@media (max-width: 768px) {
  .about-grid,
  .services-grid,
  .agent-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 20px !important;
  }

  .about-card,
  .service-item,
  .agent-card {
    padding: 24px 20px;
    min-height: 235px;
  }

  .about-icon,
  .service-icon {
    width: 56px;
    height: 56px;
    margin-bottom: 18px;
  }

  .about-card h3,
  .service-item h4 {
    font-size: 18px;
  }

  .about-card p,
  .service-item p {
    font-size: 14.5px;
  }

  .agent-card-head {
    gap: 15px;
    margin-bottom: 15px;
  }

  .agent-icon {
    width: 48px;
    height: 48px;
  }

  .agent-card-title {
    font-size: 16.5px;
  }

  .agent-card-desc {
    font-size: 14px;
    -webkit-line-clamp: 3;
  }

  .hero h1 {
    font-size: 40px;
  }
}

@media (max-width: 480px) {
  .about-grid,
  .services-grid,
  .agent-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 14px !important;
  }

  .about-card,
  .service-item,
  .agent-card {
    padding: 20px 16px;
    min-height: 215px;
  }

  .about-icon,
  .service-icon {
    width: 50px;
    height: 50px;
    margin-bottom: 14px;
  }

  .about-card h3,
  .service-item h4 {
    font-size: 16.5px;
  }

  .about-card p,
  .service-item p {
    font-size: 13.5px;
  }

  .agent-icon {
    width: 44px;
    height: 44px;
  }

  .agent-card-title {
    font-size: 15.5px;
  }

  .agent-card-desc {
    font-size: 13px;
    -webkit-line-clamp: 3;
  }

  .hero h1 {
    font-size: 34px;
  }
}
</style>

<!-- HERO -->
<section class="hero">
  <div class="hero-text">
    <span class="hero-badge"><?= icon('sparkle') ?> ۱۰ دستیار تخصصی</span>
    <h1>هر سؤال درسی،<br><span class="grad">یه دستیار آماده</span></h1>
    <p class="hero-desc">دانش‌یار با ۱۰ ایجنت تخصصی، هر نیاز درسی‌ات را بهتر از بقیه برآورده می‌کند.</p>

    <div class="hero-stats">
      <div class="hstat"><div class="hstat-v">۱۰</div><div class="hstat-l">دستیار</div></div>
      <div class="hstat"><div class="hstat-v">۷-۱۲</div><div class="hstat-l">پایه</div></div>
      <div class="hstat"><div class="hstat-v">۲۴/۷</div><div class="hstat-l">همیشه</div></div>
    </div>

    <div class="hero-actions">
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-hero"><?= icon('rocket') ?> شروع رایگان</a>
      <a href="<?= BASE_URL ?>/pricing.php" class="btn btn-ghost btn-hero"><?= icon('crown') ?> پلن‌ها</a>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section style="padding: 100px 0;">
  <div class="container">
    <div class="section-head">
      <h2 class="section-title">درباره <span class="grad">دانش‌یار</span></h2>
    </div>
    <div class="about-grid">
      <div class="about-card glass">
        <div class="about-icon"><?= icon('sparkle') ?></div>
        <h3>مأموریت ما</h3>
        <p>کمک به دانش‌آموزان برای یادگیری عمیق و واقعی با هوش مصنوعی.</p>
      </div>
      <div class="about-card glass">
        <div class="about-icon"><?= icon('book') ?></div>
        <h3>رویکرد ما</h3>
        <p>پاسخ‌ها دقیقاً بر اساس کتاب درسی رسمی ایران تولید می‌شوند.</p>
      </div>
      <div class="about-card glass">
        <div class="about-icon"><?= icon('users') ?></div>
        <h3>جامعه ما</h3>
        <p>هزاران دانش‌آموز، کنکوری و مشاور از دانش‌یار استفاده می‌کنند.</p>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section style="padding: 100px 0;">
  <div class="container">
    <div class="section-head">
      <h2 class="section-title">خدمات <span class="grad">دانش‌یار</span></h2>
    </div>
    <div class="services-grid">
      <div class="service-item glass">
        <div class="service-icon"><?= icon('chat') ?></div>
        <h4>حل سوال درسی</h4>
        <p>عکس یا متن سوال را بفرست و جواب گام‌به‌گام بگیر.</p>
      </div>
      <div class="service-item glass">
        <div class="service-icon"><?= icon('book') ?></div>
        <h4>توضیح درس</h4>
        <p>درس را از پایه تا پیشرفته با مثال‌های ساده یاد بگیر.</p>
      </div>
      <div class="service-item glass">
        <div class="service-icon"><?= icon('chart') ?></div>
        <h4>تحلیل آزمون</h4>
        <p>آزمون و پاسخ‌برگ را بفرست و نقاط ضعف خودت را بشناس.</p>
      </div>
      <div class="service-item glass">
        <div class="service-icon"><?= icon('sparkle') ?></div>
        <h4>ساخت تست</h4>
        <p>تست‌های سفارشی بر اساس سطح و موضوع مورد نیازت.</p>
      </div>
      <div class="service-item glass">
        <div class="service-icon"><?= icon('users') ?></div>
        <h4>مشاوره تحصیلی</h4>
        <p>برنامه‌ریزی، انتخاب رشته و مشاوره کنکور.</p>
      </div>
      <div class="service-item glass">
        <div class="service-icon"><?= icon('clock') ?></div>
        <h4>جمع‌بندی شب امتحان</h4>
        <p>نکات کلیدی و سوالات پرتکرار هر درس را داشته باش.</p>
      </div>
    </div>
  </div>
</section>

<!-- AGENTS -->
<section style="padding: 100px 0;">
  <div class="container">
    <div class="section-head">
      <h2 class="section-title">دستیارهای <span class="grad">دانش‌یار</span></h2>
    </div>
    <div class="agent-grid">
      <?php foreach (agents_registry() as $key => $a): ?>
        <a href="<?= e(BASE_URL . '/' . ($a['standalone'] ? 'agents/' . $key . '.php' : ($a['href'] ?? 'chat.php'))) ?>" class="agent-card">
          <div class="agent-card-head">
            <div class="agent-icon"><?= icon($a['icon']) ?></div>
            <div>
              <div class="agent-card-title"><?= e($a['title']) ?></div>
              <div class="agent-card-desc"><?= e($a['short_desc']) ?></div>
            </div>
          </div>
          <div class="agent-card-foot">
            <span class="agent-card-tag"><?= e($a['tag']) ?></span>
            <span class="agent-card-cta">شروع <?= icon('arrow-left') ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- STEPS -->
<section style="padding: 100px 0;">
  <div class="container">
    <div class="section-head">
      <h2 class="section-title">فقط در ۳ قدم</h2>
    </div>
    <div class="steps" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:26px;">
      <div class="step glass"><div class="step-num">1</div><h3>ثبت‌نام کن</h3><p>۳۰ ثانیه با موبایل</p></div>
      <div class="step glass"><div class="step-num">2</div><h3>دستیار انتخاب کن</h3><p>هر سؤال، یه دستیار</p></div>
      <div class="step glass"><div class="step-num">3</div><h3>یاد بگیر</h3><p>جواب + توضیح کامل</p></div>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section style="padding: 100px 0 120px;">
  <div class="container">
    <div class="glass glass-orange" style="padding:60px 48px; text-align:center; border-radius:30px;">
      <h2 style="font-size:34px; margin-bottom:20px;">یادگیری جدی، با ۱۰ دستیار کنارت</h2>
      <p style="color:#cfcfda; margin-bottom:36px; font-size:18px;">هر نیاز درسی، یه دستیار مخصوص.</p>
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-hero"><?= icon('sparkle') ?> شروع با ۳ پیام رایگان</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>