<?php
/**
 * ============================================================
 *  دانش‌یار - API مشترک ایجنت‌های non-streaming
 *  برای: analyze, generate, planner, notes, review, cards
 *  ورودی: agent key + form data
 *  خروجی: JSON { result, error?, used_today? }
 * ============================================================
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai.php';

header('Content-Type: application/json; charset=utf-8');
@ini_set('memory_limit', '256M');
@set_time_limit(300);

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'برای استفاده، اول وارد حساب کاربری خود شو.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (is_banned($user)) {
    http_response_code(403);
    echo json_encode(['error' => 'حساب شما مسدود شده است.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// چک CSRF
$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'توکن امنیتی نامعتبر است. صفحه را رفرش کن.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// چک سهمیه
$quota = can_send_message($user);
if (!$quota['ok']) {
    http_response_code(429);
    $msg = '';
    switch ($quota['reason'] ?? '') {
        case 'free_limit':
            $msg = 'سهمیه رایگان امروزت تموم شد. برای استفاده نامحدود، اشتراک بخر.';
            break;
        case 'daily_limit':
            $msg = 'سهمیه روزانه‌ات تموم شد. فردا دوباره پر می‌شه.';
            break;
        default:
            $msg = 'سهمیه‌ات تموم شده.';
    }
    echo json_encode(['error' => $msg, 'reason' => $quota['reason'] ?? ''], JSON_UNESCAPED_UNICODE);
    exit;
}

$agent = $_POST['agent'] ?? '';
$validAgents = ['analyze', 'generate', 'konkoori_planner', 'score_analysis', 'notes', 'review', 'cards', 'class_tools'];
if (!in_array($agent, $validAgents, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'ایجنت نامعتبر.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ساخت پرامپت هر ایجنت
$prompt = build_agent_prompt($agent, $user, $_POST);

// اضافه کردن به حافظه مکالمه اگه لازم بود
$messages = [
    ['role' => 'system', 'content' => $prompt],
    ['role' => 'user', 'content' => user_input_text($agent, $_POST)],
];

// برای analyze، تصویر هم اضافه کن
$imageBase64 = null;
$imageMime = null;
if ($agent === 'analyze') {
    if (!empty($_FILES['exam']['tmp_name']) && is_uploaded_file($_FILES['exam']['tmp_name'])) {
        $imageBase64 = base64_encode(file_get_contents($_FILES['exam']['tmp_name']));
        $imageMime = $_FILES['exam']['type'] ?: 'image/jpeg';
    }
    if (!empty($_FILES['answers']['tmp_name']) && is_uploaded_file($_FILES['answers']['tmp_name'])) {
        // پاسخ‌برگ رو به پیام کاربر اضافه می‌کنیم به عنوان تصویر دوم یا متن
        $ansText = 'پاسخ‌برگ ضمیمه شد.';
        $messages[1]['content'] = $messages[1]['content'] . "\n\n[پاسخ‌برگ آپلود شد]";
        // برای سادگی، فقط یک تصویر پردازش می‌کنیم
    }
    if ($imageBase64) {
        // تصویر رو به پیام کاربر اضافه می‌کنیم
        $messages[1]['content'] = [
            ['type' => 'text', 'text' => $messages[1]['content']],
            ['type' => 'image_url', 'image_url' => ['url' => 'data:' . $imageMime . ';base64,' . $imageBase64]],
        ];
    }
}

// فراخوانی AI
$result = '';
$ch = curl_init(AI_API_URL);
$payload = [
    'model'       => AI_MODEL,
    'messages'    => $messages,
    'temperature' => 0.3,
    'max_tokens'  => (int)env('AI_MAX_TOKENS', 4096),
    'stream'      => false,
];
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 240,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . AI_API_KEY,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_SSL_VERIFYPEER => env('CURL_INSECURE', false) ? false : true,
    CURLOPT_SSL_VERIFYHOST => env('CURL_INSECURE', false) ? 0 : 2,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'خطا در ارتباط با سرویس: ' . ($curlErr ?: 'نامشخص')], JSON_UNESCAPED_UNICODE);
    exit;
}
$data = json_decode($response, true);
if ($httpCode >= 400 || empty($data['choices'][0]['message']['content'])) {
    $errMsg = $data['error']['message'] ?? 'پاسخ نامعتبر از سرویس';
    http_response_code($httpCode ?: 500);
    echo json_encode(['error' => 'سرویس خطا داد: ' . $errMsg], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = $data['choices'][0]['message']['content'];

// افزایش شمارنده
increment_message_counters($user['id']);

// آپدیت پروفایل یادگیری
$subject = $_POST['subject'] ?? '';
if ($subject) {
    update_learning_profile($user['id'], $subject, 50, 'استفاده از ایجنت ' . $agent);
}

// ذخیره تاریخچه برای بعضی ایجنت‌ها
if (in_array($agent, ['analyze', 'generate', 'notes', 'review', 'cards'])) {
    save_agent_history($user['id'], $agent, user_input_text($agent, $_POST), $result);
}

echo json_encode([
    'result' => $result,
    'agent'  => $agent,
], JSON_UNESCAPED_UNICODE);
exit;

/* ============================================================
   توابع کمکی
   ============================================================ */

function user_input_text($agent, $post) {
    $subject = $post['subject'] ?? '';
    $context = $post['context'] ?? '';
    $topic   = $post['topic'] ?? '';
    $grade   = $post['grade'] ?? '';
    $level   = $post['level'] ?? '';
    $count   = $post['count'] ?? '';
    $text    = $post['text'] ?? '';
    $examDate = $post['exam_date'] ?? '';
    $hours   = $post['hours_per_day'] ?? '';

    switch ($agent) {
        case 'analyze':
            $txt = 'یک برگه آزمون آپلود شده.';
            if ($subject) $txt .= "\nدرس: {$subject}";
            if ($context) $txt .= "\nنکات: {$context}";
            $txt .= "\n\nلطفاً:\n1. هر سؤال رو جدا بخوان.\n2. اگه پاسخ‌برگ دارم، با پاسخ‌برگ تطبیق بده.\n3. برای هر سؤال، وضعیت (درست/نادرست/نزده) و دلیل خطا (سوتی مفهومی، محاسباتی، بی‌دقتی، نرسیدن وقت، نفهمیدن سؤال) رو مشخص کن.\n4. در آخر، ۳ تا ۵ نکتهٔ کلیدی برای مرور بده.";
            return $txt;

        case 'generate':
            $txt = 'تست شخصی بساز.';
            if ($subject) $txt .= "\nدرس: {$subject}";
            if ($topic) $txt .= "\nمبحث: {$topic}";
            if ($grade)  $txt .= "\nپایه: {$grade}";
            if ($level)  $txt .= "\nسطح: {$level}";
            if ($count)  $txt .= "\nتعداد: {$count} سؤال";
            $txt .= "\n\nبرای هر سؤال:\n- صورت سؤال\n- گزینه‌ها (اگه تستیه)\n- پاسخ صحیح\n- توضیح کوتاه";
            return $txt;

        case 'konkoori_planner':
            $txt = 'برنامهٔ کنکور بده.';
            if ($subject) $txt .= "\nدرس اصلی: {$subject}";
            if ($grade)   $txt .= "\nپایه: {$grade}";
            if ($examDate) $txt .= "\nتاریخ کنکور: {$examDate}";
            if ($hours)   $txt .= "\nساعت مطالعه روزانه: {$hours}";
            if ($context) $txt .= "\nنکات: {$context}";
            $txt .= "\n\nبرنامه باید:\n- واقع‌بینانه باشه (نه فشرده و خسته‌کننده)\n- اولویت با مباحث پرتکرار کنکور باشه\n- زمان مرور و تست‌زنی داشته باشه\n- استراحت کافی داشته باشه\n- هر هفته یه ارزیابی پیشرفت داشته باشه";
            return $txt;

        case 'score_analysis':
            $txt = 'تحلیل کارنامهٔ آزمون آزمایشی بده.';
            if ($subject) $txt .= "\nدرس/کارنامه: {$subject}";
            if ($context) $txt .= "\nجزئیات کارنامه:\n{$context}";
            $txt .= "\n\nلطفاً:\n1. رتبه کل و درصد هر درس رو تحلیل کن.\n2. نقاط قوت (درس‌هایی که خوب بوده) رو مشخص کن.\n3. نقاط ضعف رو دقیق بگو.\n4. برای هر درس ضعیف، ۳ تا ۵ مبحث کلیدی برای مرور پیشنهاد بده.\n5. برنامهٔ پیشنهادی برای هفتهٔ بعد بده.";
            return $txt;

        case 'notes':
            $txt = 'جزوه بساز.';
            if ($subject) $txt .= "\nدرس: {$subject}";
            if ($topic)   $txt .= "\nفصل/مبحث: {$topic}";
            if ($text)    $txt .= "\nمتن منبع:\n" . $text;
            $txt .= "\n\nجزوه باید:\n- ساختار منظم داشته باشه (تیتر، زیرتیتر)\n- نکات کلیدی بولد شده باشن\n- مثال داشته باشه\n- برای مرور سریع مناسب باشه";
            return $txt;

        case 'review':
            $txt = 'جمع‌بندی شب امتحان بده.';
            if ($subject) $txt .= "\nدرس: {$subject}";
            if ($topic)   $txt .= "\nمباحث: {$topic}";
            $txt .= "\n\nجمع‌بندی باید:\n- فقط مهم‌ترین نکات باشه (نه همهٔ درس)\n- طوری باشه که تو ۱۵ دقیقه بشه خوند\n- فرمول‌ها، تاریخ‌ها، تعاریف کلیدی\n- نکاتی که معلم امتحان می‌ده";
            return $txt;

        case 'cards':
            $txt = 'فلش‌کارت بساز.';
            if ($subject) $txt .= "\nدرس: {$subject}";
            if ($topic)   $txt .= "\nمبحث: {$topic}";
            if ($count)   $txt .= "\nتعداد: {$count}";
            $txt .= "\n\nهر فلش‌کارت:\n- رو (سؤال)\n- پشت (جواب)\n- سختی (۱ تا ۵)";
            return $txt;

        case 'class_tools':
            $txt = 'برنامهٔ درسی برای چند شاگرد بساز.';
            if ($subject) $txt .= "\nدرس: {$subject}";
            if ($context) $txt .= "\nجزئیات شاگردها و کلاس:\n{$context}";
            $txt .= "\n\nلطفاً:\n1. برای هر شاگرد، سطح فعلی رو تحلیل کن.\n2. برنامهٔ شخصی‌سازی‌شده برای هر شاگرد بده.\n3. منابع پیشنهادی معرفی کن.\n4. زمان‌بندی مرور هفتگی بده.\n5. پیشنهاد ارزیابی پیشرفت بده.";
            return $txt;

        default:
            return $text ?: 'درخواست نامشخص';
    }
}

function build_agent_prompt($agent, $user, $post) {
    $grade = (int)($user['grade'] ?? 10);
    $major = major_label($user['major'] ?? 'math');

    $base = DaneshyarAI::systemPrompt(null, $user, '', '');

    $extra = "\n\n# حالت ایجنت: {$agent}\n";

    switch ($agent) {
        case 'analyze':
            $extra .= "نقش تو الان: تحلیل‌گر حرفه‌ای برگه آزمون. باید هر سؤال رو جدا تحلیل کنی و دلیل دقیق خطا رو مشخص کنی.\n";
            $extra .= "\n# وظایف\n";
            $extra .= "1. هر سؤال رو جداگانه بخوان و صورتش رو بفهم.\n";
            $extra .= "2. اگه پاسخ‌برگ فرستاده شده، با پاسخ‌برگ تطبیق بده.\n";
            $extra .= "3. برای هر سؤال، وضعیت رو دقیق مشخص کن: **درست / نادرست / نزده**.\n";
            $extra .= "4. برای سؤال‌های نادرست یا نزده، علت خطا رو از این لیست تشخیص بده:\n";
            $extra .= "   - سوتی مفهومی (مفهوم رو نفهمیده)\n";
            $extra .= "   - اشتباه محاسباتی (روش درست، محاسبه غلط)\n";
            $extra .= "   - بی‌دقتی (می‌دونست ولی اشتباه جزئی)\n";
            $extra .= "   - مدیریت زمان (وقت نرسیده)\n";
            $extra .= "   - نفهمیدن صورت سؤال\n";
            $extra .= "5. پاسخ صحیح و روش حل رو توضیح بده.\n";
            $extra .= "\n# قالب خروجی (دقیق رعایت کن)\n";
            $extra .= "## 📊 خلاصه کلی\n";
            $extra .= "- تعداد کل: [عدد]\n";
            $extra .= "- درست: [عدد]\n";
            $extra .= "- نادرست: [عدد]\n";
            $extra .= "- نزده: [عدد]\n";
            $extra .= "- درصد: [عدد]\n\n";
            $extra .= "## 📝 تحلیل سؤال به سؤال\n";
            $extra .= "(برای هر سؤال دقیقاً این قالب رو رعایت کن)\n\n";
            $extra .= "سؤال ۱: [موضوع یا صورت کوتاه سؤال]\n";
            $extra .= "وضعیت: [درست/نادرست/نزده]\n";
            $extra .= "علت خطا: [اگه نادرست یا نزده - یکی از دلایل بالا + توضیح کوتاه]\n";
            $extra .= "پاسخ صحیح: [پاسخ صحیح و روش حل کامل]\n";
            $extra .= "توضیح: [چطور این سؤال باید حل می‌شد - آموزش کامل]\n";
            $extra .= "---\n\n";
            $extra .= "سؤال ۲: [همین قالب]\n";
            $extra .= "وضعیت: ...\n";
            $extra .= "...\n";
            $extra .= "---\n\n";
            $extra .= "## 🎯 پیشنهاد برای بهبود\n";
            $extra .= "۱. [اولین پیشنهاد]\n";
            $extra .= "۲. [دومین پیشنهاد]\n";
            $extra .= "۳. [سومین پیشنهاد]\n";
            $extra .= "\n";
            $extra .= "\n# نکات کیفی\n";
            $extra .= "- هر سؤال رو واقع‌بینانه تحلیل کن.\n";
            $extra .= "- توضیح باید به‌قدر کافی آموزشی باشه که دانش‌آموز بتونه یاد بگیره.\n";
            $extra .= "- لحن مهربان ولی دقیق داشته باش.\n";
            break;

        case 'generate':
            $extra .= "نقش تو الان: سازندهٔ تست حرفه‌ای. تست‌های استاندارد با کیفیت کنکور تولید کن.\n";
            $extra .= "\n# قوانین مهم\n";
            $extra .= "1. تست‌ها باید **دقیقاً مرتبط با کتاب درسی ایران** برای پایهٔ {$grade} و رشتهٔ {$post['subject']} باشن.\n";
            $extra .= "2. سطح دشواری: {$post['level']}\n";
            $extra .= "3. **دقیقاً {$post['count']} سؤال** تولید کن. نه کمتر، نه بیشتر.\n";
            $extra .= "4. هر سؤال **۴ گزینه** داشته باشه (الف، ب، ج، د) مگر اینکه موضوعش تشریحی باشه.\n";
            $extra .= "5. پاسخ‌ها باید **از روی کتاب** باشن، نه حدسی.\n";
            $extra .= "\n# قالب خروجی (دقیق رعایت کن - این قالب برای پردازش لازمه)\n";
            $extra .= "از قالب زیر **بدون هیچ تغییری** استفاده کن. شمارهٔ سؤال، حرف گزینه‌ها، و کلمهٔ 'پاسخ' باید دقیق باشن:\n\n";
            $extra .= "سؤال ۱: [صورت سؤال کامل، واضح، با اعداد فارسی]\n";
            $extra .= "الف) [گزینه اول]\n";
            $extra .= "ب) [گزینه دوم]\n";
            $extra .= "ج) [گزینه سوم]\n";
            $extra .= "د) [گزینه چهارم]\n";
            $extra .= "پاسخ: [حرف گزینه درست: الف/ب/ج/د]\n";
            $extra .= "توضیح: [دلیل صحیح بودن پاسخ + تحلیل تله‌های گزینه‌های نادرست + نکته کلیدی]\n";
            $extra .= "---\n\n";
            $extra .= "سؤال ۲: [همین قالب]\n";
            $extra .= "الف) ... ب) ... ج) ... د) ...\n";
            $extra .= "پاسخ: ...\n";
            $extra .= "توضیح: ...\n";
            $extra .= "---\n\n";
            $extra .= "(برای همهٔ {$post['count']} سؤال تکرار کن)\n";
            $extra .= "\n# نکات کیفی\n";
            $extra .= "- از کتاب درسی رسمی استفاده کن، نه منبع خارجی.\n";
            $extra .= "- اگه محاسباتیه، عدد و واحد دقیق باشه.\n";
            $extra .= "- تله‌های آزمونی بساز (مثل جابه‌جایی اعداد، منفی اشتباه، فرمول اشتباه).\n";
            $extra .= "- توضیح هر سؤال حداقل ۲ جمله باشه.\n";
            break;

        case 'konkoori_planner':
            $extra .= "نقش تو الان: برنامه‌ریز کنکور. باید برنامهٔ واقع‌بینانه و عملی بدی.\n";
            $extra .= "\nقالب خروجی:\n";
            $extra .= "## 📅 برنامهٔ کنکور {$post['subject']}\n";
            $extra .= "از امروز تا {$post['exam_date']}\n\n";
            $extra .= "### 📊 تحلیل اولیه\n";
            $extra .= "- روزهای باقی‌مانده تا کنکور: X\n";
            $extra .= "- ساعت مطالعه روزانه: {$post['hours_per_day']}\n";
            $extra .= "- مباحث پرتکرار کنکور: ...\n\n";
            $extra .= "### 🗓 برنامهٔ هفتگی (۷ روز)\n";
            $extra .= "**روز ۱**:\n";
            $extra .= "- صبح: مبحث ۱ (X ساعت)\n";
            $extra .= "- عصر: تست ۲۰ تایی مبحث ۱ (X ساعت)\n";
            $extra .= "- شب: مرور نکات (X ساعت)\n\n";
            $extra .= "(برای همهٔ روزهای هفته تکرار کن)\n\n";
            $extra .= "### 💡 نکات ویژهٔ کنکور\n";
            $extra .= "- مباحث پرتکرار\n";
            $extra .= "- تست‌های ضروری\n";
            $extra .= "- روش مرور\n";
            break;

        case 'score_analysis':
            $extra .= "نقش تو الان: تحلیل‌گر کارنامهٔ آزمون آزمایشی. کاربر کارنامه‌اش رو فرستاده و می‌خواد تحلیل دقیق و راهنمایی برای ادامه مسیر بگیره.\n";
            $extra .= "\nقالب خروجی:\n";
            $extra .= "## 📊 تحلیل کارنامه\n\n";
            $extra .= "### 📈 خلاصهٔ کلی\n";
            $extra .= "- رتبه کل: ...\n";
            $extra .= "- درصد کل: ...\n";
            $extra .= "- وضعیت نسبت به دفعات قبل: بهتر / ثابت / بدتر\n\n";
            $extra .= "### ✅ نقاط قوت (درس‌هایی که خوب بوده)\n";
            $extra .= "- درس ۱: ...\n";
            $extra .= "- درس ۲: ...\n\n";
            $extra .= "### ⚠️ نقاط ضعف (اولویت‌بندی شده)\n";
            $extra .= "1. درس X (بیشترین تأثیر): ...\n";
            $extra .= "2. درس Y: ...\n\n";
            $extra .= "### 🎯 مباحث کلیدی برای مرور هر درس ضعیف\n";
            $extra .= "- درس X: مبحث ۱، مبحث ۲، مبحث ۳\n\n";
            $extra .= "### 📅 برنامهٔ هفتهٔ بعد\n";
            $extra .= "- روز ۱: ...\n";
            $extra .= "- روز ۲: ...\n";
            break;

        case 'class_tools':
            $extra .= "نقش تو الان: ابزار مشاور درسی. باید برای چند شاگرد برنامهٔ شخصی‌سازی‌شده بسازی.\n";
            $extra .= "\nقالب خروجی:\n";
            $extra .= "## 👥 برنامهٔ شخصی‌سازی‌شده برای شاگردها\n\n";
            $extra .= "### شاگرد ۱: [نام]\n";
            $extra .= "- **سطح فعلی**: ...\n";
            $extra .= "- **نقاط قوت**: ...\n";
            $extra .= "- **نقاط ضعف**: ...\n";
            $extra .= "- **برنامهٔ هفتگی**: ...\n";
            $extra .= "- **منابع پیشنهادی**: ...\n\n";
            $extra .= "(برای هر شاگرد تکرار کن)\n\n";
            $extra .= "### 📊 مقایسهٔ کلاس\n";
            $extra .= "- میانگین کلاس: ...\n";
            $extra .= "- شاگردان نیازمند توجه بیشتر: ...\n";
            break;

        case 'notes':
            $extra .= "نقش تو الان: جزوه‌ساز. باید از متن یا مبحث، جزوهٔ منظم و خلاصه تولید کنی.\n";
            $extra .= "\nقالب خروجی (Markdown با ساختار منظم):\n";
            $extra .= "# 📚 جزوهٔ {$post['subject']} - {$post['topic']}\n\n";
            $extra .= "## 🎯 اهداف یادگیری\n";
            $extra .= "- ...\n\n";
            $extra .= "## 📖 مفاهیم کلیدی\n";
            $extra .= "### ۱. [مفهوم اول]\n";
            $extra .= "- تعریف: ...\n";
            $extra .= "- مثال: ...\n";
            $extra .= "- نکته: ...\n\n";
            $extra .= "(برای همهٔ مفاهیم تکرار کن)\n\n";
            $extra .= "## 📌 نکات طلایی برای امتحان\n";
            $extra .= "- ...\n";
            break;

        case 'review':
            $extra .= "نقش تو الان: مربی جمع‌بندی شب امتحان. کاربر وقت کم داره و فقط مهم‌ترین‌ها رو می‌خواد.\n";
            $extra .= "\nقالب خروجی (فشرده و کاربردی):\n";
            $extra .= "## 🌙 جمع‌بندی {$post['subject']} - {$post['topic']}\n\n";
            $extra .= "### ⏰ زمان پیشنهادی مطالعه: X ساعت\n\n";
            $extra .= "### 🔥 مهم‌ترین نکات (حتماً بخون)\n";
            $extra .= "1. ...\n۲. ...\n۳. ...\n\n";
            $extra .= "### 📐 فرمول‌های ضروری\n";
            $extra .= "- ...\n\n";
            $extra .= "### 📅 تاریخ‌ها/اسامی مهم (اگه لازمه)\n";
            $extra .= "- ...\n\n";
            $extra .= "### ❌ اشتباهات رایج دانش‌آموزا\n";
            $extra .= "- ...\n\n";
            $extra .= "### ✅ چک‌لیست قبل از امتحان\n";
            $extra .= "- [ ] ...\n";
            break;

        case 'cards':
            $extra .= "نقش تو الان: سازندهٔ فلش‌کارت. باید فلش‌کارت‌هایی بسازی که برای مرور فاصله‌دار مناسب باشن.\n";
            $extra .= "\nقالب خروجی:\n";
            $extra .= "## 🎴 فلش‌کارت {$post['subject']} - {$post['topic']}\n";
            $extra .= "تعداد: {$post['count']}\n\n";
            $extra .= "---\n\n";
            $extra .= "### کارت ۱\n";
            $extra .= "**رو**: [سؤال]\n";
            $extra .= "**پشت**: [جواب]\n";
            $extra .= "**سختی**: ⭐⭐⭐ (۱ تا ۵)\n\n";
            $extra .= "(برای همه تکرار کن)\n";
            break;
    }

    return $base . $extra;
}

function save_agent_history($userId, $agent, $input, $output) {
    try {
        $pdo = db();
        $title = mb_substr(strip_tags($input), 0, 80);
        $st = $pdo->prepare("INSERT INTO agent_history (user_id, agent_key, input_text, output_text, title, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $st->execute([$userId, $agent, mb_substr($input, 0, 1000), mb_substr($output, 0, 10000), $title]);
    } catch (Exception $e) {
        // ignore - table might not exist yet
    }
}
