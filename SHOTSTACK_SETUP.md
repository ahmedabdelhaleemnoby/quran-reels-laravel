# دليل إعداد Shotstack للـ Shared Hosting

## 🎯 لماذا Shotstack؟

Shotstack هو API متخصص في توليد الفيديو ويعمل على Shared Hosting.

---

## 📝 الخطوة 1: إنشاء حساب Shotstack

1. **اذهب إلى:**
   https://shotstack.io/

2. **اضغط "Sign Up" أو "Get Started"**

3. **أكمل التسجيل**

4. **بعد تسجيل الدخول:**
   - اذهب إلى **Dashboard**
   - اختر **API Keys**
   - انسخ الـ **API Key**

---

## 🔧 الخطوة 2: تثبيت Shotstack SDK

```bash
composer require shotstack/shotstack-sdk-php
```

---

## ⚙️ الخطوة 3: إضافة API Key في .env

```env
SHOTSTACK_API_KEY=your_api_key_here
SHOTSTACK_ENVIRONMENT=stage  # استخدم 'stage' للتجربة المجانية
```

**ملاحظة:** 
- `stage` = بيئة تجريبية مجانية (فيديو به watermark)
- `production` = بيئة إنتاج (بدون watermark، مدفوع)

---

## 📹 كيف يعمل Shotstack؟

### **المفهوم:**
1. ترفع الصور والصوت لـ Shotstack
2. تُنشئ "Timeline" (سيناريو الفيديو)
3. Shotstack يولد الفيديو
4. تحصل على رابط الفيديو الجاهز

### **مثال بسيط:**
```php
use Shotstack\Client\Api\EditApi;
use Shotstack\Client\Configuration;
use Shotstack\Client\Model\Edit;
use Shotstack\Client\Model\Timeline;
use Shotstack\Client\Model\Track;
use Shotstack\Client\Model\Clip;
use Shotstack\Client\Model\ImageAsset;
use Shotstack\Client\Model\AudioAsset;

// إعداد API
$config = Configuration::getDefaultConfiguration()
    ->setApiKey('x-api-key', env('SHOTSTACK_API_KEY'))
    ->setHost('https://api.shotstack.io/stage');

$client = new EditApi(null, $config);

// إنشاء Timeline
$imageAsset = new ImageAsset();
$imageAsset->setSrc('https://example.com/image.jpg');

$imageClip = new Clip();
$imageClip->setAsset($imageAsset)
    ->setStart(0)
    ->setLength(5);

$track = new Track();
$track->setClips([$imageClip]);

$timeline = new Timeline();
$timeline->setTracks([$track]);

$edit = new Edit();
$edit->setTimeline($timeline);

// إرسال للتوليد
$response = $client->postRender($edit);
$renderId = $response->getResponse()->getId();

// بعد دقائق، تحقق من الحالة
$status = $client->getRender($renderId);
if ($status->getResponse()->getStatus() === 'done') {
    $videoUrl = $status->getResponse()->getUrl();
}
```

---

## 💰 التكلفة

### **الخطة المجانية (Stage):**
- ✅ غير محدود من الفيديوهات
- ⚠️ فيديو به watermark
- ⚠️ جودة أقل قليلاً

### **الخطة المدفوعة (Production):**
- ✅ بدون watermark
- ✅ جودة عالية
- 💵 $49/شهر لـ 100 فيديو
- 💵 $149/شهر لـ 500 فيديو

---

## 🔄 الفرق بين FFmpeg و Shotstack

| الميزة | FFmpeg (VPS) | Shotstack (Shared Hosting) |
|--------|--------------|---------------------------|
| **يعمل على Shared Hosting** | ❌ | ✅ |
| **التكلفة** | $6/شهر VPS | مجاني/مدفوع |
| **السرعة** | سريع جداً | متوسط (يعتمد على الـ API) |
| **التحكم** | كامل | محدود |
| **الجودة** | ممتاز | ممتاز |

---

## 📊 المقارنة النهائية

| الحل | التكلفة | يعمل على Shared Hosting | الجودة |
|------|---------|------------------------|--------|
| **VPS + FFmpeg** | $6/شهر | ❌ | ⭐⭐⭐⭐⭐ |
| **Shotstack (مجاني)** | $0 | ✅ | ⭐⭐⭐⭐ (watermark) |
| **Shotstack (مدفوع)** | $49/شهر | ✅ | ⭐⭐⭐⭐⭐ |

---

## 🎯 التوصية

### **إذا كان المشروع:**

**تجريبي/شخصي:**
- استخدم Shotstack Stage (مجاني مع watermark)

**تجاري/جاد:**
- **VPS + FFmpeg** ($6/شهر) - أرخص وأفضل
- أو Shotstack Production ($49/شهر)

---

## 🚀 الخطوات التالية

1. سجل في Shotstack: https://shotstack.io/
2. احصل على API Key
3. أخبرني وسأعدل الكود ليستخدم Shotstack

---

## ⚠️ ملاحظة مهمة

**VPS أرخص من Shotstack المدفوع:**
- VPS: $6/شهر (غير محدود)
- Shotstack: $49/شهر (100 فيديو فقط)

**لذلك VPS هو الخيار الأفضل اقتصادياً!**
