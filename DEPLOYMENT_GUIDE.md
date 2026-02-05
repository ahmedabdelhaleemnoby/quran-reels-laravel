# دليل حل مشكلة 500 Internal Server Error

## الأسباب الشائعة والحلول:

### 1. ✅ صلاحيات الملفات (الأهم)
على السيرفر، قم بتنفيذ هذه الأوامر عبر SSH:

```bash
# إعطاء صلاحيات للمجلدات المطلوبة
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# أو إذا لم ينفع:
chmod -R 777 storage bootstrap/cache
```

### 2. ✅ ملف .env
تأكد من:
- وجود ملف `.env` على السيرفر (انسخه من `.env.example`)
- تعديل إعدادات قاعدة البيانات:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ar.gfoura.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=اسم_قاعدة_البيانات
DB_USERNAME=اسم_المستخدم
DB_PASSWORD=كلمة_المرور
```

### 3. ✅ توليد Application Key
على السيرفر:
```bash
php artisan key:generate
```

### 4. ✅ تشغيل Migrations
```bash
php artisan migrate --force
```

### 5. ✅ مسح الـ Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 6. ✅ التأكد من إصدار PHP
المشروع يحتاج PHP 8.2 أو أعلى. تحقق من إصدار PHP على السيرفر.

### 7. ✅ فحص ملف error_log
ابحث عن ملف `error_log` في:
- `/storage/logs/laravel.log`
- أو في جذر المشروع
- أو في cPanel > Error Log

هذا الملف سيخبرك بالمشكلة بالضبط.

---

## خطوات سريعة للحل:

1. افتح File Manager في cPanel
2. اذهب لمجلد المشروع
3. انقر بزر الماوس الأيمن على مجلد `storage` → Change Permissions → 777
4. نفس الشيء لمجلد `bootstrap/cache` → 777
5. تأكد من وجود ملف `.env` وأنه يحتوي على `APP_KEY`
6. افتح Terminal في cPanel وشغل:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

## إذا لم تنفع الحلول السابقة:

أرسل لي محتوى ملف:
- `storage/logs/laravel.log` (آخر 50 سطر)
- أو `error_log` من cPanel
