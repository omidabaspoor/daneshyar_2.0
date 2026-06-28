<?php
/**
 * ============================================================
 *  دانش‌یار - داشبورد اصلی کاربر
 *  مرکز همهٔ دستیارها بعد از لاگین
 *  فیلتر بر اساس نقش: دانش‌آموز / کنکوری / مشاور
 * ============================================================
 */
require_once __DIR__ . '/includes/functions.php';

$user = current_user();
if (!$user) {
    redirect(BASE_URL . '/login.php?next=' . urlencode(BASE_URL . '/dashboard.php'));
}
if (is_banned($user)) {
    redirect(BASE_URL . '/logout.php');
}
if (!is_profile_complete($user)) {
    redirect(BASE_URL . '/profile.php?complete=1');
}

$page = 'dashboard';
$pageTitle = 'داشبورد';
$seoTitle = 'داشبورد | دانش‌یار - دستیار هوشمند آموزشی';
$seoDescription = 'داشبورد دانش‌یار؛ دستیارهای آموزشی برای دانش‌آموزان، کنکوری‌ها و مشاوران.';
$seoRobots = 'noindex,nofollow';

// تشخیص نقش کاربر
$userRole = ($user['role'] ?? 'user');
$userGrade = (int)($user['grade'] ?? 0);
$isKonkoori = $userGrade >= 11;
$isCounselor = $userRole === 'counselor';
$isAdmin = $userRole === 'admin';

// پیام خوش‌آمدگویی بر اساس ساعت
$hour = (int)date('G');
if ($hour < 5)       $greetingTime = 'شب بخیر';
elseif ($hour < 12)  $greetingTime = 'صبح بخیر';
elseif ($hour < 17)  $greetingTime = 'بعدازظهر بخیر';
elseif ($hour < 20)  $greetingTime = 'عصر بخیر';
else                 $greetingTime = 'شب بخیر';

// اطلاعات اشتراک
$subInfo = user_subscription_summary($user);

// ایجنت‌های قابل دسترس
$userAgents = agents_for_user($user);

// پروفایل یادگیری
$learningProfile = get_learning_profile_summary($user['id']);

// تاریخچه اخیر
$recentChats = get_recent_chats_for_user($user['id'], 4);

// تعیین پیام خوش‌آمدگویی بر اساس نقش
if ($isCounselor) {
    $roleLabel = 'مشاور';
    $roleBlurb = 'ابزارهای تحلیل شاگردها و ساخت برنامهٔ درسی';
} elseif ($isKonkoori) {
    $roleLabel = 'کنکوری';
    $roleBlurb = 'برنامه‌ریز، تحلیل کارنامه، انتخاب رشته';
} else {
    $roleLabel = 'دانش‌آموز';
    $roleBlurb = 'دستیارهای یادگیری پایهٔ ' . num_fa($userGrade);
}

include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-page">

  <!-- ══════ خوش‌آمدگویی ══════ -->
  <section class="dy-dash-hero">
    <div class="dy-dash-greet">
      <div class="dy-dash-avatar"><?= e(mb_substr($user['first_name'], 0, 1)) ?></div>
      <div class="dy-dash-info">
        <span class="dy-greeting-time"><?= e($greetingTime) ?> · <?= e(format_jalali_date(date('Y-m-d H:i:s'))) ?></span>
        <h2><?= e($user['first_name']) ?> عزیز، <span class="grad">دستیارت آماده‌ست</span></h2>
        <p>شما به‌عنوان <b><?= e($roleLabel) ?></b> وارد شدی؛ <?= e($roleBlurb) ?>.</p>
        <div class="dy-dash-meta">
          <?php if (!$isCounselor): ?>
            <span class="dy-dash-pill"><?= icon('book') ?> پایه <?= num_fa($userGrade) ?></span>
            <span class="dy-dash-pill"><?= icon('brain') ?> <?= e(major_label($user['major'] ?? 'math')) ?></span>
          <?php else: ?>
            <span class="dy-dash-pill"><?= icon('users') ?> نقش: مشاور درسی</span>
          <?php endif; ?>
          <?php if (!empty($subInfo['active'])): ?>
            <span class="dy-dash-pill is-active"><?= icon('check') ?> اشتراک فعال</span>
          <?php else: ?>
            <span class="dy-dash-pill is-free"><?= icon('star') ?> سهمیه رایگان</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($subInfo['active'])): ?>
      <div class="dy-dash-quota">
        <div class="dy-dash-quota-card">
          <div class="dy-dash-quota-label"><?= icon('clock') ?> پایان اشتراک</div>
          <div class="dy-dash-quota-value"><?= e($subInfo['end_fa'] ?? '—') ?><small> <?= e($subInfo['plan_title'] ?? '') ?></small></div>
        </div>
        <div class="dy-dash-quota-card">
          <div class="dy-dash-quota-label"><?= icon('chat') ?> پیام‌های امروز</div>
          <div class="dy-dash-quota-value"><?= num_fa($user['messages_used_today'] ?? 0) ?> <small>/ <?= num_fa($subInfo['daily_limit'] ?? 0) ?></small></div>
        </div>
        <div class="dy-dash-quota-card">
          <div class="dy-dash-quota-label"><?= icon('chart') ?> کل پیام‌ها</div>
          <div class="dy-dash-quota-value"><?= num_fa($user['messages_used_total'] ?? 0) ?></div>
        </div>
      </div>
    <?php else: ?>
      <div class="dy-dash-quota">
        <div class="dy-dash-quota-card">
          <div class="dy-dash-quota-label"><?= icon('star') ?> سهمیه رایگان امروز</div>
          <div class="dy-dash-quota-value"><?= num_fa($user['free_used_today'] ?? 0) ?> <small>/ <?= num_fa((int)(FREE_DAILY_LIMIT)) ?></small></div>
        </div>
        <div class="dy-dash-quota-card" style="grid-column: span 2;">
          <div class="dy-dash-quota-label"><?= icon('rocket') ?> ارتقا به اشتراک</div>
          <div class="dy-dash-quota-value" style="font-size: 16px;">پیام نامحدود + همهٔ دستیارها</div>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <!-- ══════ نوار وضعیت اشتراک ══════ -->
  <?php if (empty($subInfo['active'])): ?>
    <div class="dy-status-bar">
      <div class="dy-status-bar-left">
        <?= icon('star') ?>
        <div class="text">سهمیهٔ رایگان روزانه — برای استفادهٔ نامحدود <b>اشتراک بخر</b></div>
      </div>
      <div class="dy-status-bar-right">
        <a href="<?= BASE_URL ?>/pricing.php" class="is-primary">مشاهدهٔ پلن‌ها</a>
      </div>
    </div>
  <?php endif; ?>

  <!-- ══════ میانبرهای سریع (۴ تای اول) ══════ -->
  <div class="dy-shortcuts">
    <?php
      $shortcutKeys = ['solve','analyze','generate','tutor'];
      foreach ($shortcutKeys as $k):
        if (!isset($userAgents[$k])) continue;
        $a = $userAgents[$k];
        $href = $a['standalone'] ? (BASE_URL . '/agents/' . $k . '.php') : (BASE_URL . '/chat.php');
    ?>
      <a href="<?= e($href) ?>" class="dy-shortcut">
        <?= icon($a['icon']) ?><span><?= e($a['title']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ══════ پروفایل یادگیری ══════ -->
  <?php if (!empty($learningProfile['has_data']) && !$isCounselor): ?>
    <div class="dy-section-head">
      <h2 class="dy-section-title"><?= icon('chart') ?> پروفایل یادگیری‌ات</h2>
      <span class="dy-section-sub">بعد از هر جلسه به‌روز می‌شه</span>
    </div>
    <div class="dy-profile-bar">
      <?php foreach ($learningProfile['topics'] as $t): ?>
        <div class="dy-profile-row">
          <span class="label"><?= e($t['name']) ?></span>
          <div class="track"><div class="fill <?= $t['level'] >= 70 ? 'is-good' : ($t['level'] <= 35 ? 'is-weak' : '') ?>" style="width: <?= max(4, min(100, (int)$t['level'])) ?>%"></div></div>
          <span class="value"><?= num_fa((int)$t['level']) ?>٪</span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ══════ دستیارهای قابل دسترس ══════ -->
  <div class="dy-section-head">
    <h2 class="dy-section-title"><?= icon('sparkle') ?> دستیارهای آموزشی</h2>
    <span class="dy-section-sub"><?= count($userAgents) ?> دستیار فعال برای تو</span>
  </div>

  <?php if (empty($userAgents)): ?>
    <div class="agent-empty">
      <div class="agent-empty-ico"><?= icon('sparkle') ?></div>
      <div class="agent-empty-title">هنوز دستیاری برای شما فعال نیست</div>
      <div class="agent-empty-desc">با مدیر تماس بگیرید تا حساب شما فعال شود.</div>
    </div>
  <?php else: ?>
    <div class="agent-grid">
      <?php foreach ($userAgents as $key => $a): ?>
        <?php
          $href = $a['standalone']
            ? (BASE_URL . '/agents/' . $key . '.php')
            : (BASE_URL . '/' . ($a['href'] ?? 'chat.php'));
        ?>
        <a href="<?= e($href) ?>" class="agent-card" data-agent="<?= e($key) ?>">
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
            <span class="agent-card-cta">شروع <?= icon('arrow-left') ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ══════ گفت‌وگوهای اخیر ══════ -->
  <?php if (!empty($recentChats) && !$isCounselor): ?>
    <div class="dy-section-head">
      <h2 class="dy-section-title"><?= icon('history') ?> گفت‌وگوهای اخیر</h2>
      <a href="<?= BASE_URL ?>/chat.php" class="dy-link">همه <?= icon('arrow-left') ?></a>
    </div>
    <div class="agent-history">
      <?php foreach ($recentChats as $c): ?>
        <a href="<?= BASE_URL ?>/chat.php?c=<?= (int)$c['id'] ?>" class="history-item">
          <div class="history-item-ico"><?= icon('chat') ?></div>
          <div class="history-item-body">
            <div class="history-item-title"><?= e($c['title'] ?: 'گفت‌وگوی جدید') ?></div>
            <div class="history-item-meta">
              <span class="tag"><?= e(date_fa_short($c['updated_at'])) ?></span>
              <?php if (!empty($c['book_title'])): ?>
                <span class="tag">📖 <?= e($c['book_title']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
