<?php
/**
 * دانش‌یار - ثبت‌نام
 * پشتیبانی از: دانش‌آموز، کنکوری (پایه ۱۱+)، مشاور درسی
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
if (current_user()) redirect(BASE_URL . '/dashboard.php');

$error = '';
$accountType = $_POST['account_type'] ?? $_GET['type'] ?? 'student';
if (!in_array($accountType, ['student', 'counselor'], true)) {
    $accountType = 'student';
}

$old = [
    'first_name' => '', 'last_name' => '', 'mobile' => '',
    'grade' => 10, 'major' => 'math',
    'org'        => '', 'students_count' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'درخواست نامعتبر است. لطفاً صفحه را رفرش کن.';
    } else {
        $accountType   = $_POST['account_type'] ?? 'student';
        $old['first_name'] = trim($_POST['first_name'] ?? '');
        $old['last_name']  = trim($_POST['last_name'] ?? '');
        $old['mobile']     = fa_to_en_digits(trim($_POST['mobile'] ?? ''));
        $old['mobile']     = preg_replace('/[^0-9]/', '', $old['mobile']);
        $password          = (string)($_POST['password'] ?? '');
        $accept            = isset($_POST['accept']);
        $old['grade']      = (int)($_POST['grade'] ?? 10);
        $old['major']      = normalize_major($_POST['major'] ?? 'math');
        $old['org']        = trim($_POST['org'] ?? '');
        $old['students_count'] = trim($_POST['students_count'] ?? '');

        // اعتبارسنجی مشترک
        if (!is_valid_person_name($old['first_name']) || !is_valid_person_name($old['last_name'])) {
            $error = 'نام و نام خانوادگی باید واقعی و حداقل ۲ حرفی باشد.';
        } elseif (!is_valid_mobile($old['mobile'])) {
            $error = 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.';
        } elseif (mb_strlen($password) < 6) {
            $error = 'رمز عبور حداقل ۶ کاراکتر باشد.';
        } elseif (!$accept) {
            $error = 'برای ثبت‌نام باید قوانین را بپذیری.';
        } else {
            // اعتبارسنجی اختصاصی هر نوع
            if ($accountType === 'student') {
                if ($old['grade'] < 7 || $old['grade'] > 12) {
                    $error = 'پایه تحصیلی نامعتبر است.';
                } elseif (!array_key_exists($old['major'], major_options())) {
                    $error = 'رشته تحصیلی نامعتبر است.';
                }
            } elseif ($accountType === 'counselor') {
                if (mb_strlen($old['org']) < 2 || mb_strlen($old['org']) > 120) {
                    $error = 'نام مرکز/مدرسه‌ای که در آن مشاوری مشخص کن.';
                }
            }

            if (!$error) {
                $stmt = db()->prepare("SELECT id FROM users WHERE mobile=?");
                $stmt->execute([$old['mobile']]);
                if ($stmt->fetch()) {
                    $error = 'این شماره موبایل قبلاً ثبت شده.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    if ($accountType === 'student') {
                        db()->prepare("INSERT INTO users (first_name,last_name,mobile,password,grade,major,school,role,last_reset_date) VALUES (?,?,?,?,?,?,?, 'user', CURDATE())")
                            ->execute([$old['first_name'], $old['last_name'], $old['mobile'], $hash, $old['grade'], $old['major'], '']);
                    } else { // counselor
                        // برای مشاوران: grade=12, major=math به‌عنوان مقادیر پیش‌فرض (فیلدهای غیرضروری)
                        db()->prepare("INSERT INTO users (first_name,last_name,mobile,password,grade,major,school,role,last_reset_date) VALUES (?,?,?,?, 12, 'other', ?, 'counselor', CURDATE())")
                            ->execute([$old['first_name'], $old['last_name'], $old['mobile'], $hash, $old['org']]);
                    }
                    login_user((int)db()->lastInsertId(), true);
                    redirect(BASE_URL . '/dashboard.php');
                }
            }
        }
    }
}

$pageTitle = 'ثبت‌نام';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap" style="min-height:auto; padding:20px 0 60px">
  <div class="auth-card glass">
    <h1>ثبت‌نام در دانش‌یار</h1>
    <p class="sub">بعدش به همهٔ دستیارهای آموزشی دسترسی داری.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= icon('warning') ?> <?= e($error) ?></div><?php endif; ?>

    <!-- انتخاب نوع حساب -->
    <div class="account-type-picker">
      <label class="account-type-card <?= $accountType === 'student' ? 'is-active' : '' ?>">
        <input type="radio" name="account_type" value="student" <?= $accountType === 'student' ? 'checked' : '' ?> onchange="window.location.href='?type=student'">
        <div class="account-type-body">
          <div class="account-type-icon"><?= icon('school') ?></div>
          <div class="account-type-title">دانش‌آموز / کنکوری</div>
          <div class="account-type-desc">پایه ۷ تا ۱۲، حل سوال، تست، جمع‌بندی، جزوه</div>
        </div>
      </label>
      <label class="account-type-card <?= $accountType === 'counselor' ? 'is-active' : '' ?>">
        <input type="radio" name="account_type" value="counselor" <?= $accountType === 'counselor' ? 'checked' : '' ?> onchange="window.location.href='?type=counselor'">
        <div class="account-type-body">
          <div class="account-type-icon"><?= icon('users') ?></div>
          <div class="account-type-title">مشاور درسی</div>
          <div class="account-type-desc">تحلیل شاگردها، برنامهٔ کلاس، گزارش والدین</div>
        </div>
      </label>
    </div>

    <form method="post" autocomplete="off" id="registerForm">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="account_type" value="<?= e($accountType) ?>">

      <div class="register-progress" id="registerProgress">
        <span class="active"></span><span></span><span></span>
      </div>

      <!-- مرحله ۱: اطلاعات شخصی -->
      <section class="reg-step active" data-step="1">
        <div class="reg-step-title"><?= icon('user') ?> مرحله ۱: اطلاعات شخصی</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
          <div class="form-group">
            <label class="form-label">نام</label>
            <input class="input" name="first_name" required value="<?= e($old['first_name']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">نام خانوادگی</label>
            <input class="input" name="last_name" required value="<?= e($old['last_name']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label"><?= icon('phone') ?> شماره موبایل</label>
          <input class="input input-tel" name="mobile" type="tel" inputmode="numeric" pattern="[0-9]*" required placeholder="۰۹۱۲۳۴۵۶۷۸۹" value="<?= e($old['mobile']) ?>" maxlength="11" autocomplete="tel">
        </div>
        <div class="form-group">
          <label class="form-label"><?= icon('lock') ?> رمز عبور</label>
          <input class="input" name="password" type="password" required minlength="6" placeholder="حداقل ۶ کاراکتر">
        </div>
        <div class="reg-actions">
          <button class="btn btn-primary btn-block" type="button" data-next>ادامه <?= icon('arrow-left') ?></button>
        </div>
      </section>

      <!-- مرحله ۲: اطلاعات تخصصی -->
      <section class="reg-step" data-step="2">
        <div class="reg-step-title"><?= icon($accountType === 'counselor' ? 'users' : 'book') ?> مرحله ۲: <?= $accountType === 'counselor' ? 'اطلاعات شغلی' : 'پایه و رشته' ?></div>

        <?php if ($accountType === 'student'): ?>
          <div class="form-group">
            <label class="form-label">پایه تحصیلی</label>
            <div class="grade-picker">
              <?php for ($g = 7; $g <= 12; $g++): ?>
                <label>
                  <input type="radio" name="grade" value="<?= $g ?>" <?= $old['grade'] == $g ? 'checked' : '' ?>>
                  <span><?= num_fa($g) ?></span>
                </label>
              <?php endfor; ?>
            </div>
            <small style="display:block; margin-top:8px; color:var(--text-dim)">پایه ۱۱ یا ۱۲ = دستیارهای کنکور هم فعال می‌شه</small>
          </div>

          <div class="form-group">
            <label class="form-label">رشته/شاخه تحصیلی</label>
            <div class="major-picker-v2">
              <?php foreach (major_options() as $code => $label):
                $isMain = in_array($code, ['math','experimental','humanities'], true);
                $desc = $isMain ? 'رشته نظری' : 'شاخه غیرنظری / عمومی';
              ?>
                <label class="major-card">
                  <input type="radio" name="major" value="<?= e($code) ?>" <?= $old['major'] === $code ? 'checked' : '' ?>>
                  <span class="major-card-body">
                    <span class="major-card-main">
                      <b><?= e($label) ?></b>
                      <small><?= e($desc) ?></small>
                    </span>
                    <span class="major-badge"><?= $isMain ? 'تمرکز اصلی' : 'پشتیبانی' ?></span>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: /* counselor */ ?>
          <div class="form-group">
            <label class="form-label">مرکز/مدرسه‌ای که در آن مشاوری</label>
            <input class="input" name="org" value="<?= e($old['org']) ?>" required minlength="2" placeholder="مثلاً: مرکز مشاورهٔ تحصیلی مهر، دبیرستان شهید بهشتی">
            <small style="display:block; margin-top:6px; color:var(--text-dim)">برای شناسایی شاگردهایی که زیر نظرت هستن</small>
          </div>
          <div class="form-group">
            <label class="form-label">تعداد تقریبی شاگردها (اختیاری)</label>
            <input class="input" name="students_count" value="<?= e($old['students_count']) ?>" placeholder="مثلاً ۳۰ نفر">
          </div>
        <?php endif; ?>

        <div class="reg-actions">
          <button class="btn btn-ghost" type="button" data-prev>برگشت</button>
          <button class="btn btn-primary" type="button" data-next>ادامه <?= icon('arrow-left') ?></button>
        </div>
      </section>

      <!-- مرحله ۳: قوانین -->
      <section class="reg-step" data-step="3">
        <div class="reg-step-title"><?= icon('shield') ?> مرحله ۳: قوانین و شروع</div>
        <div class="rules-box">
          <b>قوانین استفاده از دانش‌یار:</b><br>
          ۱. دانش‌یار یه <b>دستیار آموزشی</b> برای یادگیری بهتره؛ نه جایگزین معلم یا کلاس.<br>
          ۲. استفاده از سرویس برای دانش‌آموزان پایهٔ ۷ تا ۱۲، کنکوری‌ها و مشاوران درسی مجازه.<br>
          ۳. هدف ما کمک به <b>یادگیری واقعی</b>ه: توضیح مفهوم، تمرین، مرور، آمادگی برای امتحان.<br>
          ۴. استفاده از سرویس برای تولید محتوای نامناسب، توهین، یا هر کار غیرآموزشی ممنوعه.<br>
          ۵. اطلاعات شخصی شما (نام، موبایل، مدرسه/مرکز) محرمانه نگه‌داشته می‌شه و فروخته نمی‌شه.<br>
          ۶. پاسخ‌های هوش مصنوعی ممکنه خطا داشته باشن؛ برای موضوعات مهم، حتماً با منبع رسمی چک کن.<br>
          ۷. اشتراک خریداری‌شده طبق شرایط استرداد قابل برگشته.<br>
          ۸. حساب شما شخصیه؛ به اشتراک‌گذاری یا واگذاری اون به دیگران مجاز نیست.<br>
          ۹. ما حق به‌روزرسانی قوانین رو داریم؛ تغییرات از طریق اعلان عمومی اطلاع‌رسانی می‌شه.
        </div>
        <div class="check-row">
          <input type="checkbox" name="accept" id="accept" required>
          <label for="accept" style="font-size:13px">قوانین بالا را خوانده‌ام و می‌پذیرم.</label>
        </div>
        <div class="reg-actions">
          <button class="btn btn-ghost" type="button" data-prev>برگشت</button>
          <button class="btn btn-primary" type="submit"><?= icon('sparkle') ?> ثبت‌نام و شروع یادگیری</button>
        </div>
      </section>
    </form>

    <div class="switch">قبلاً ثبت‌نام کرده‌ای؟ <a href="<?= BASE_URL ?>/login.php">وارد شو</a></div>
  </div>
</div>

<style>
.account-type-picker {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin: 18px 0 22px;
}
.account-type-card {
  position: relative;
  cursor: pointer;
  display: block;
}
.account-type-card input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.account-type-body {
  padding: 14px 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  text-align: center;
  transition: 0.2s var(--ease);
}
.account-type-card:hover .account-type-body {
  border-color: rgba(235,124,42,0.3);
  background: rgba(255,255,255,0.06);
}
.account-type-card input:checked + .account-type-body {
  background: linear-gradient(135deg, rgba(235,124,42,0.20), rgba(235,124,42,0.06));
  border-color: rgba(235,124,42,0.5);
  box-shadow: 0 6px 20px rgba(235,124,42,0.18);
}
.account-type-icon {
  width: 44px;
  height: 44px;
  margin: 0 auto 8px;
  border-radius: 13px;
  background: rgba(235,124,42,0.14);
  border: 1px solid rgba(235,124,42,0.24);
  display: grid;
  place-items: center;
  color: var(--orange);
}
.account-type-icon .ico {
  width: 22px;
  height: 22px;
}
.account-type-title {
  font-size: 14px;
  font-weight: 800;
  margin-bottom: 3px;
  color: var(--text);
}
.account-type-desc {
  font-size: 11px;
  color: var(--text-dim);
  line-height: 1.6;
}
@media (max-width: 480px) {
  .account-type-picker { grid-template-columns: 1fr; }
}
</style>

<script>
(function(){
  var form = document.getElementById('registerForm');
  if (!form) return;
  var steps = form.querySelectorAll('.reg-step');
  var progress = document.querySelectorAll('#registerProgress span');
  var currentStep = 0;

  function showStep(n) {
    if (n < 0 || n >= steps.length) return;
    steps.forEach(function(s, i) {
      s.classList.toggle('active', i === n);
    });
    progress.forEach(function(p, i) {
      p.classList.toggle('active', i <= n);
    });
    currentStep = n;
    steps[n].scrollIntoView({behavior:'smooth', block:'start'});
  }

  form.querySelectorAll('[data-next]').forEach(function(b) {
    b.addEventListener('click', function() {
      // اعتبارسنجی ساده برای رفتن به مرحلهٔ بعد
      var currentSection = steps[currentStep];
      var inputs = currentSection.querySelectorAll('input[required]:not([type=radio]):not([type=checkbox])');
      var radios = currentSection.querySelectorAll('input[type=radio][required]');
      var ok = true;
      inputs.forEach(function(i){ if (!i.value.trim()) { i.focus(); ok = false; } });
      if (ok && radios.length > 0) {
        var anyChecked = false;
        radios.forEach(function(r){ if (r.checked) anyChecked = true; });
        if (!anyChecked) ok = false;
      }
      if (ok) showStep(currentStep + 1);
    });
  });
  form.querySelectorAll('[data-prev]').forEach(function(b) {
    b.addEventListener('click', function() { showStep(currentStep - 1); });
  });

  // شماره موبایل فقط عدد
  var m = form.querySelector('input[name="mobile"]');
  if (m) {
    m.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9۰-۹٠-٩]/g, '');
    });
    m.addEventListener('paste', function(e) {
      e.preventDefault();
      var t = (e.clipboardData || window.clipboardData).getData('text');
      this.value = t.replace(/[^0-9]/g, '');
    });
  }
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
