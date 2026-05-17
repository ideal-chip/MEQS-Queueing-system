# 15 - حالة السيرفر الحالي

## نتائج الفحص
الأوامر التالية غير موجودة على السيرفر:

```text
php
mysql
mariadb
docker
apache2
nginx
```

الخدمات التالية غير موجودة في systemd:

```text
mysql.service
mariadb.service
apache2.service
nginx.service
```

محاولة التثبيت بـ `apt-get update` فشلت بسبب عدم وجود صلاحية root:

```text
Permission denied /var/lib/apt/lists/lock
```

محاولة `sudo -n true` فشلت لأن sudo يحتاج كلمة مرور:

```text
sudo: a password is required
```

## المطلوب لتشغيل المشروع فعلياً
تشغيل هذا الأمر من مستخدم لديه sudo:

```bash
cd /home/idealchip_server/meqs
sudo bash scripts/install_current_server.sh
```

هذا السكربت يقوم بـ:
- تثبيت PHP و MySQL و Nginx و curl.
- إنشاء `.env`.
- إنشاء قاعدة `project_demo_db`.
- تشغيل schema والـ seed.
- إنشاء systemd service باسم `meqs-demo.service`.
- تشغيل المشروع على `127.0.0.1:8000`.

## أمر التحقق بعد التثبيت
```bash
BASE_URL=http://127.0.0.1:8000 tests/smoke.sh
```
