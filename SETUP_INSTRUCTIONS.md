# خطوات تشغيل المشروع

## 1. تفعيل ZIP Extension في PHP

1. افتح الملف: `C:\xampp\php\php.ini`
2. ابحث عن السطر: `;extension=zip`
3. احذف الـ `;` ليصبح: `extension=zip`
4. احفظ الملف وأعد تشغيل Apache من XAMPP Control Panel

## 2. تثبيت المكتبات

افتح Terminal في مجلد المشروع وقم بتشغيل:

```bash
composer install --ignore-platform-reqs
```

## 3. إعداد قاعدة البيانات

```bash
# إنشاء ملف قاعدة البيانات
New-Item -ItemType File -Path database\database.sqlite -Force

# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل الـ migrations
php artisan migrate
```

## 4. تشغيل السيرفر

```bash
php artisan serve
```

ثم افتح المتصفح على: `http://localhost:8000`

---

## ملاحظات مهمة

- تأكد من تشغيل Apache و MySQL من XAMPP Control Panel
- المشروع يستخدم SQLite كقاعدة بيانات افتراضية
- إذا واجهت مشاكل، تأكد من أن PHP 8.2 أو أعلى مثبت
