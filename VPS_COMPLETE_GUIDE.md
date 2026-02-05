# دليل إعداد VPS الكامل - الحل الأمثل

## 🎯 لماذا VPS هو الأفضل؟

### **المقارنة:**

| الميزة | Shared Hosting + Shotstack | VPS + FFmpeg |
|--------|---------------------------|--------------|
| **التكلفة الشهرية** | $49 | **$6** ✅ |
| **عدد الفيديوهات** | 100 فقط | **غير محدود** ✅ |
| **Watermark** | يوجد (مجاني) | **لا يوجد** ✅ |
| **السرعة** | متوسط | **سريع جداً** ✅ |
| **التحكم** | محدود | **كامل** ✅ |
| **الجودة** | ممتاز | **ممتاز** ✅ |

**VPS أرخص 8 مرات وأفضل في كل شيء!**

---

## 📝 الخطوة 1: اختيار VPS Provider

### **الخيارات الموصى بها:**

#### **1. DigitalOcean** ⭐ (الأسهل والأفضل)
- **السعر:** $6/شهر
- **المميزات:**
  - واجهة سهلة جداً
  - إعداد سريع (5 دقائق)
  - دعم فني ممتاز
  - دروس كثيرة
- **الرابط:** https://www.digitalocean.com/
- **الخطة:** Basic Droplet - $6/month

#### **2. Vultr**
- **السعر:** $6/شهر
- مشابه لـ DigitalOcean
- **الرابط:** https://www.vultr.com/

#### **3. Linode (Akamai)**
- **السعر:** $5/شهر
- **الرابط:** https://www.linode.com/

#### **4. Contabo** (الأرخص)
- **السعر:** €3.99/شهر (~$4.5)
- مواصفات أعلى لكن دعم فني أقل
- **الرابط:** https://contabo.com/

---

## 🚀 الخطوة 2: إنشاء VPS (DigitalOcean مثال)

### **1. التسجيل:**
1. اذهب إلى: https://www.digitalocean.com/
2. اضغط "Sign Up"
3. أكمل التسجيل (قد تحتاج بطاقة ائتمان)

### **2. إنشاء Droplet:**
1. اضغط "Create" → "Droplets"
2. **اختر:**
   - **Image:** Ubuntu 22.04 LTS
   - **Plan:** Basic - $6/month (1 GB RAM, 1 CPU)
   - **Datacenter:** اختر الأقرب لك (مثل Frankfurt لمصر)
   - **Authentication:** SSH Key (أو Password)
3. اضغط "Create Droplet"

### **3. انتظر 1-2 دقيقة:**
- سيتم إنشاء السيرفر
- ستحصل على **IP Address** (مثل: 159.89.123.45)

---

## 🔧 الخطوة 3: الاتصال بالسيرفر

### **من Windows (PowerShell):**

```powershell
ssh root@YOUR_SERVER_IP
```

مثال:
```powershell
ssh root@159.89.123.45
```

**ملاحظة:** إذا طلب password، أدخله (سيكون مرسول على إيميلك)

---

## ⚙️ الخطوة 4: إعداد السيرفر (نسخ ولصق)

بعد الاتصال بالسيرفر، نفذ هذه الأوامر **واحد تلو الآخر:**

### **1. تحديث النظام:**
```bash
apt update && apt upgrade -y
```

### **2. تثبيت المتطلبات الأساسية:**
```bash
apt install -y software-properties-common curl wget git unzip
```

### **3. تثبيت PHP 8.2:**
```bash
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
php8.2-intl php8.2-soap php8.2-sqlite3
```

### **4. تثبيت FFmpeg (الأهم!):**
```bash
apt install -y ffmpeg
```

### **5. التحقق من FFmpeg:**
```bash
ffmpeg -version
```
يجب أن ترى معلومات FFmpeg.

### **6. تثبيت Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### **7. تثبيت Nginx:**
```bash
apt install -y nginx
systemctl start nginx
systemctl enable nginx
```

### **8. تثبيت MySQL:**
```bash
apt install -y mysql-server
mysql_secure_installation
```

**عند السؤال:**
- Set root password? **Y** → أدخل password قوي
- Remove anonymous users? **Y**
- Disallow root login remotely? **Y**
- Remove test database? **Y**
- Reload privilege tables? **Y**

---

## 📦 الخطوة 5: رفع المشروع

### **1. إنشاء مجلد المشروع:**
```bash
mkdir -p /var/www/quran-reels
cd /var/www/quran-reels
```

### **2. رفع الملفات:**

**الطريقة 1: Git (إذا كان المشروع على GitHub):**
```bash
git clone https://github.com/YOUR_USERNAME/quran-reels.git .
```

**الطريقة 2: رفع يدوي:**
- استخدم FileZilla أو WinSCP
- رفع كل ملفات المشروع إلى `/var/www/quran-reels`

### **3. تثبيت Dependencies:**
```bash
composer install --no-dev --optimize-autoloader
```

### **4. إعداد .env:**
```bash
cp .env.example .env
nano .env
```

**عدّل:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_SERVER_IP

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quran_reels
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

اضغط `Ctrl+X` ثم `Y` ثم `Enter` للحفظ.

### **5. إنشاء قاعدة البيانات:**
```bash
mysql -u root -p
```

داخل MySQL:
```sql
CREATE DATABASE quran_reels CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### **6. تشغيل Migrations:**
```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **7. ضبط الصلاحيات:**
```bash
chown -R www-data:www-data /var/www/quran-reels
chmod -R 755 /var/www/quran-reels
chmod -R 775 /var/www/quran-reels/storage
chmod -R 775 /var/www/quran-reels/bootstrap/cache
```

---

## 🌐 الخطوة 6: إعداد Nginx

### **1. إنشاء ملف الإعداد:**
```bash
nano /etc/nginx/sites-available/quran-reels
```

### **2. لصق هذا:**
```nginx
server {
    listen 80;
    server_name YOUR_SERVER_IP;
    root /var/www/quran-reels/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**غيّر `YOUR_SERVER_IP` بـ IP السيرفر الحقيقي**

اضغط `Ctrl+X` ثم `Y` ثم `Enter`.

### **3. تفعيل الموقع:**
```bash
ln -s /etc/nginx/sites-available/quran-reels /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 🎉 الخطوة 7: اختبار الموقع

افتح المتصفح واذهب إلى:
```
http://YOUR_SERVER_IP
```

**يجب أن يعمل المشروع الآن!** 🚀

---

## 🔒 الخطوة 8: إضافة Domain + SSL (اختياري)

### **1. ربط Domain:**
في إعدادات الـ Domain، أضف:
```
A Record: @ → YOUR_SERVER_IP
A Record: www → YOUR_SERVER_IP
```

### **2. تثبيت SSL مجاني (Let's Encrypt):**
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## 💰 التكلفة النهائية

| البند | التكلفة |
|-------|---------|
| **VPS (DigitalOcean)** | $6/شهر |
| **Domain (اختياري)** | $10-15/سنة |
| **SSL** | مجاني |
| **المجموع** | **$6/شهر** |

**مقابل:**
- ✅ فيديوهات غير محدودة
- ✅ بدون watermark
- ✅ تحكم كامل
- ✅ سرعة عالية

---

## 🆘 المساعدة

إذا واجهت أي مشكلة في أي خطوة، أخبرني وسأساعدك! 

**الخطوات التالية:**
1. سجل في DigitalOcean
2. أنشئ Droplet
3. أخبرني بالـ IP
4. سأساعدك في باقي الخطوات

---

## 📊 الخلاصة

**VPS هو الحل الأمثل لأنه:**
- ✅ أرخص من Shotstack (8 مرات)
- ✅ غير محدود
- ✅ تحكم كامل
- ✅ سريع جداً
- ✅ احترافي

**ابدأ الآن!** 🚀
