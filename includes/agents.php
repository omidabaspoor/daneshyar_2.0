<?php
/**
 * ============================================================
 *  دانش‌یار - رجیستری ایجنت‌ها + توابع مشترک
 *  هر ایجنت audience داره: all / konkoori / counselor
 * ============================================================
 */

if (!function_exists('agents_registry')) {

    /**
     * رجیستری همهٔ دستیارها
     *
     * سطوح دسترسی (audience):
     *   - 'all'       → همهٔ کاربران لاگین‌شده
     *   - 'konkoori'  → فقط دانش‌آموزان کنکوری (پایه ۱۱ یا ۱۲)
     *   - 'counselor' → فقط مشاوران درسی
     */
    function agents_registry() {
        return [
            // ═══════ همهٔ دانش‌آموزان ═══════
            'solve' => [
                'title'       => 'معلم سریع',
                'short_desc'  => 'هر سؤالی داری، بفرست. جواب تشریحی قدم‌به‌قدم می‌گیری',
                'tag'         => 'چت درسی',
                'icon'        => 'chat',
                'href'        => 'chat.php',
                'standalone'  => false,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => true,
                'pro'         => false,
            ],
            'analyze' => [
                'title'       => 'تحلیل برگه',
                'short_desc'  => 'برگه آزمون + پاسخ‌برگ بفرست، هر سؤال رو بررسی و دلیل خطا رو بگو',
                'tag'         => 'گزارش شخصی',
                'icon'        => 'graph',
                'standalone'  => true,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],
            'generate' => [
                'title'       => 'تست‌ساز',
                'short_desc'  => 'تست بساز، همونجا بده، نمره بگیر، اشتباهاتت رو یاد بگیر',
                'tag'         => 'ساخت + آزمون',
                'icon'        => 'sparkle',
                'standalone'  => true,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],
            'tutor' => [
                'title'       => 'معلم خصوصی',
                'short_desc'  => 'هر مفهوم رو قدم‌به‌قدم یاد بگیر',
                'tag'         => 'توضیح درس',
                'icon'        => 'school',
                'standalone'  => true,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => true,
                'pro'         => false,
            ],
            'notes' => [
                'title'       => 'جزوه‌ساز',
                'short_desc'  => 'از هر فصل، جزوهٔ خلاصه و منظم تولید کن',
                'tag'         => 'جزوه',
                'icon'        => 'book',
                'standalone'  => true,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],
            'review' => [
                'title'       => 'جمع‌بندی امتحان',
                'short_desc'  => 'مهم‌ترین نکات یه درس، برای شب امتحان',
                'tag'         => 'خلاصه فوری',
                'icon'        => 'flash',
                'standalone'  => true,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],
            'cards' => [
                'title'       => 'کارت مرور',
                'short_desc'  => 'فلش‌کارت بساز، مرور کن، یادت نره',
                'tag'         => 'مرور هوشمند',
                'icon'        => 'star',
                'standalone'  => true,
                'audience'    => 'all',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],

            // ═══════ فقط کنکوری‌ها ═══════
            'konkoori_planner' => [
                'title'       => 'برنامهٔ کنکور',
                'short_desc'  => 'برنامهٔ روزانه تا روز کنکور، واقع‌بینانه و قابل اجرا',
                'tag'         => 'برنامهٔ شخصی',
                'icon'        => 'clock',
                'standalone'  => true,
                'audience'    => 'konkoori',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],
            'score_analysis' => [
                'title'       => 'تحلیل کارنامه',
                'short_desc'  => 'کارنامهٔ آزمونت رو بده، تحلیل درس‌به‌درس و برنامهٔ بهبود بگیر',
                'tag'         => 'آزمون آزمایشی',
                'icon'        => 'graph',
                'standalone'  => true,
                'audience'    => 'konkoori',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => false,
            ],
            'major' => [
                'title'       => 'مشاور انتخاب رشته',
                'short_desc'  => 'بر اساس علاقه، نمره و هدفت، چند رشتهٔ مناسب پیشنهاد بگیر',
                'tag'         => 'انتخاب رشته',
                'icon'        => 'trophy',
                'standalone'  => true,
                'audience'    => 'konkoori',
                'requires_ai' => true,
                'streams'     => true,
                'pro'         => true,
            ],

            // ═══════ فقط مشاوران ═══════
            'advisor' => [
                'title'       => 'تحلیل‌گر شاگرد',
                'short_desc'  => 'وضعیت یک شاگرد رو تحلیل کن، برنامهٔ شخصی‌سازی‌شده بساز',
                'tag'         => 'تحلیل شاگرد',
                'icon'        => 'star',
                'standalone'  => true,
                'audience'    => 'counselor',
                'requires_ai' => true,
                'streams'     => true,
                'pro'         => true,
            ],
            'class_tools' => [
                'title'       => 'مدیر کلاس',
                'short_desc'  => 'چند شاگرد رو با هم تحلیل کن، مقایسه و گزارش بساز',
                'tag'         => 'مدیریت کلاس',
                'icon'        => 'users',
                'standalone'  => true,
                'audience'    => 'counselor',
                'requires_ai' => true,
                'streams'     => false,
                'pro'         => true,
            ],
        ];
    }

    /**
     * آیا این ایجنت برای این کاربر قابل دسترسه؟
     */
    function agent_visible_for_user($agentKey, $user) {
        $reg = agents_registry();
        if (!isset($reg[$agentKey])) return false;
        $a = $reg[$agentKey];
        $audience = $a['audience'] ?? 'all';

        if ($audience === 'all') return true;
        if ($audience === 'konkoori') {
            // کنکوری: پایه ۱۱ یا ۱۲
            return (int)($user['grade'] ?? 0) >= 11;
        }
        if ($audience === 'counselor') {
            return ($user['role'] ?? 'user') === 'counselor';
        }
        return false;
    }

    /**
     * لیست ایجنت‌های قابل دسترس برای یک کاربر
     */
    function agents_for_user($user) {
        $all = agents_registry();
        $out = [];
        foreach ($all as $k => $a) {
            if (agent_visible_for_user($k, $user)) {
                $out[$k] = $a;
            }
        }
        return $out;
    }

    /**
     * اطلاعات اشتراک کاربر
     */
    function user_subscription_summary($user) {
        if (empty($user['subscription_type']) || $user['subscription_type'] === 'none') {
            return ['active' => false];
        }
        if (!empty($user['subscription_end']) && strtotime($user['subscription_end']) < time()) {
            return ['active' => false];
        }
        $endFa = '';
        if (!empty($user['subscription_end'])) {
            $ts = strtotime($user['subscription_end']);
            $endFa = date_fa_short(date('Y-m-d', $ts));
        }
        return [
            'active'      => true,
            'plan_title'  => $user['subscription_type'] ?? 'اشتراک',
            'end_fa'      => $endFa,
            'daily_limit' => (int)($user['daily_limit'] ?? 50),
            'total_limit' => (int)($user['total_limit'] ?? 0),
        ];
    }

    /**
     * خلاصه پروفایل یادگیری کاربر
     */
    function get_learning_profile_summary($userId) {
        $profile = get_setting('learning_profile_' . $userId, null);
        if (!$profile) return ['has_data' => false];
        $data = is_string($profile) ? json_decode($profile, true) : $profile;
        if (!is_array($data) || empty($data['topics'])) return ['has_data' => false];
        $topics = array_slice($data['topics'], 0, 5);
        return ['has_data' => true, 'topics' => $topics];
    }

    /**
     * به‌روزرسانی پروفایل یادگیری
     */
    function update_learning_profile($userId, $topic, $level, $note = '') {
        if (!$userId || !$topic) return;
        $profile = get_setting('learning_profile_' . $userId, null);
        $data = $profile ? (is_string($profile) ? json_decode($profile, true) : $profile) : ['topics' => []];
        if (!isset($data['topics'])) $data['topics'] = [];
        if (isset($data['topics'][$topic])) {
            $cur = $data['topics'][$topic];
            $data['topics'][$topic] = [
                'name'  => $topic,
                'level' => (int)(($cur['level'] * 0.7) + ($level * 0.3)),
                'note'  => $note ?: ($cur['note'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        } else {
            $data['topics'][$topic] = [
                'name'  => $topic,
                'level' => (int)$level,
                'note'  => $note,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        $data['topics'] = array_slice($data['topics'], -10, 10, true);
        set_setting('learning_profile_' . $userId, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * تاریخچه گفت‌وگوهای اخیر کاربر
     */
    function get_recent_chats_for_user($userId, $limit = 4) {
        $pdo = db();
        $st = $pdo->prepare("
            SELECT c.id, c.title, c.updated_at, b.title AS book_title
            FROM chats c
            LEFT JOIN books b ON b.id = c.book_id
            WHERE c.user_id = ?
            ORDER BY c.is_pinned DESC, c.updated_at DESC
            LIMIT ?
        ");
        $st->execute([$userId, $limit]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * افزایش شمارنده پیام
     */
    function increment_message_counters($userId) {
        $pdo = db();
        $today = date('Y-m-d');
        $st = $pdo->prepare("
            UPDATE users
            SET messages_used_total = messages_used_total + 1,
                messages_used_today = messages_used_today + 1,
                free_used_today = IF(last_reset_date = ?, free_used_today + 1, 1),
                last_reset_date = ?
            WHERE id = ?
        ");
        $st->execute([$today, $today, $userId]);
    }

    /**
     * تاریخ میلادی کوتاه به فارسی (با الگوریتم دقیق شمسی)
     */
    if (!function_exists('date_fa_short')) {
        function date_fa_short($date) {
            if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '—';
            $ts = is_numeric($date) ? (int)$date : strtotime($date);
            if (!$ts) return '—';
            $diff = time() - $ts;
            if ($diff < 60)        return 'لحظاتی پیش';
            if ($diff < 3600)      return 'امروز';
            if ($diff < 86400)     return 'دیروز';
            if ($diff < 86400 * 3) return 'چند روز پیش';
            if ($diff < 86400 * 7) return 'این هفته';
            [$jy, $jm, $jd] = gregorian_to_jalali((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
            return num_fa($jd) . ' ' . month_fa($jm) . ' ' . num_fa($jy);
        }
    }

    /**
     * تاریخ میلادی کامل به فارسی
     */
    if (!function_exists('date_fa_full')) {
        function date_fa_full($date) {
            if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '—';
            $ts = is_numeric($date) ? (int)$date : strtotime($date);
            if (!$ts) return '—';
            [$jy, $jm, $jd] = gregorian_to_jalali((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
            $time = date('H:i', $ts);
            return num_fa($jd) . ' ' . month_fa($jm) . ' ' . num_fa($jy) . ' - ' . $time;
        }
    }

    /**
     * تبدیل میلادی به شمسی (الگوریتم دقیق)
     */
    if (!function_exists('gregorian_to_jalali')) {
        function gregorian_to_jalali($gy, $gm, $gd) {
            $gy = (int)$gy; $gm = (int)$gm; $gd = (int)$gd;
            $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
            $jy = $gy - 621;
            $r = ($gy - $gy % 4) / 4;
            $p = $gy + 1 - (($gy + 1) % 4) / 4;
            $j_d_m = [0,31,62,93,124,155,186,216,246,276,306,336];
            if ($gy % 4 == 0 && ($gm > 2 || ($gm == 2 && $gd > 28))) $r++;
            $j_d = $g_d_m[$gm - 1] + $gd - 1;
            $j_d += 79 * $r + 79 - 1;
            if ($j_d > 365) { $j_y = 1; } else { $j_y = 0; }
            $j_d = $j_d - 1;
            if ($j_d < 186 && $j_y == 1) { $j_y = 0; $j_d += 365; }
            $jm = 0;
            while ($jm < 11 && $j_d >= $j_d_m[$jm + 1]) { $jm++; }
            $jd = $j_d - $j_d_m[$jm] + 1;
            return [$jy + $j_y, $jm + 1, $jd];
        }
    }

    if (!function_exists('month_fa')) {
        function month_fa($m) {
            $names = [1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',
                      7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند'];
            return $names[(int)$m] ?? '';
        }
    }

    /**
     * پرامپت اختصاصی هر ایجنت
     */
    function agent_specific_prompt($agentKey, $user, $bookContext = '') {
        $prompts = [
            'tutor' => "\n\n# حالت معلم خصوصی\nتو الان نقش یه معلم خصوصی رو داری. کاربر یه مفهوم درسی رو نمی‌فهمه یا می‌خواد عمیق‌تر یاد بگیره.\n- از پایه شروع کن، نه از وسط.\n- اول با یه مثال ساده و ملموس مفهوم رو جا بنداز.\n- بعد مرحله به مرحله عمقش کن.\n- در آخر، با یه خلاصهٔ ۲-۳ خطی جمعش کن.\n- جواب کوتاه ولی عمیق باشه، نه کم‌عمق و طولانی.\n- اگه موضوع محاسباتیه، فرمول‌ها رو با LaTeX بنویس.",
            'advisor' => "\n\n# حالت مشاور\nتو الان نقش یه مشاور حرفه‌ای رو داری. کاربر مشاور درسیه و می‌خواد وضعیت یک یا چند شاگردش رو تحلیل کنه.\n- با لحن حرفه‌ای و دقیق صحبت کن.\n- اگه اطلاعات کافی نیست، سؤال بپرس.\n- توصیه‌ها واقع‌بینانه و عملی باشن، نه شعاری.\n- به بازار کار، منابع معتبر و مسیر موفقیت اشاره کن.",
            'major' => "\n\n# حالت انتخاب رشته\nتو الان نقش یه مشاور انتخاب رشتهٔ باتجربه رو داری.\n1. اول اطلاعات بگیر: پایه، معدل، رشته فعلی، علاقه، هدف، شرایط مالی، منطقه.\n2. نقاط قوت و ضعف رو تحلیل کن.\n3. ۳ تا ۵ رشتهٔ مناسب پیشنهاد بده.\n4. برای هر رشته، دروس مهم، بازار کار واقع‌بینانه و مسیر موفقیت رو توضیح بده.\n- بازار کار رو واقع‌بینانه بگو، نه آرمانی.\n- از انتخاب رشتهٔ صرفاً بر اساس «پول» یا «پرستیژ» پرهیز کن.\n- آخر سر یه جمع‌بندی ۳ خطی بده.",
        ];
        return $prompts[$agentKey] ?? '';
    }
}
