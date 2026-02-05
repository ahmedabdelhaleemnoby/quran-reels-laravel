# حل مشكلة mbstring Extension

## المشكلة:
```
Call to undefined function Illuminate\Support\mb_split()
```

هذا يعني أن السيرفر يفتقد إلى **mbstring extension** في PHP.

---

## الحلول:

### الحل 1: تفعيل mbstring عبر cPanel (الأسهل)

1. اذهب إلى **cPanel**
2. ابحث عن **Select PHP Version** أو **MultiPHP Manager**
3. اختر إصدار PHP (يفضل 8.2 أو أعلى)
4. اضغط على **Extensions** أو **PHP Extensions**
5. فعّل الـ Extensions التالية:
   - ✅ **mbstring**
   - ✅ **pdo**
   - ✅ **pdo_mysql**
   - ✅ **zip**
   - ✅ **gd**
   - ✅ **curl**
   - ✅ **xml**
   - ✅ **fileinfo**
   - ✅ **tokenizer**
   - ✅ **json**
   - ✅ **openssl**

6. احفظ التغييرات

---

### الحل 2: عبر SSH (إذا كان متاح)

```bash
# للسيرفرات التي تستخدم cPanel/WHM
/scripts/phpextensionmgr install mbstring

# أو تواصل مع الدعم الفني للاستضافة
```

---

### الحل 3: استخدام PHP مختلف

إذا كان السيرفر يدعم عدة إصدارات من PHP:

```bash
# جرب استخدام PHP 8.1 أو 8.2 بدلاً من الإصدار الحالي
/usr/local/bin/php81 artisan key:generate
# أو
/usr/local/bin/php82 artisan key:generate
```

---

### الحل 4: تواصل مع الدعم الفني

إذا لم تنفع الحلول السابقة، تواصل مع **الدعم الفني للاستضافة** واطلب منهم:

> "Please enable the mbstring PHP extension for my hosting account. I need it to run a Laravel application."

---

## بعد تفعيل mbstring:

شغل هذه الأوامر:

```bash
php artisan key:generate
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
```

---

## للتحقق من Extensions المتاحة:

```bash
php -m | grep mbstring
```

إذا ظهرت كلمة `mbstring` في النتيجة، يعني الـ extension مفعّل.

---

## Extensions المطلوبة لـ Laravel:

- PHP >= 8.2
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- Filter PHP Extension
- Hash PHP Extension
- **Mbstring PHP Extension** ← المشكلة هنا
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Session PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
