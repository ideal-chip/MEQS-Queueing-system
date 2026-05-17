# 00 - ملخص التسليم التنفيذي

## حالة التشغيل
تم تشغيل المشروع فعلياً على هذا السيرفر بدون صلاحية root عبر runtime محلي داخل `.runtime/`:
- PHP 8.4.21 static مع `mysqli/mysqlnd`.
- MariaDB 11.4.10 محلي على `127.0.0.1:3307`.
- PHP built-in server على `127.0.0.1:8000`.

الروابط الحالية:

```text
http://127.0.0.1:8000/beaa/admin/account/login.php
http://127.0.0.1:8000/beaa/counter/
http://127.0.0.1:8000/beaa/admin/file-browser.php
```

لإعادة التشغيل:

```bash
bash scripts/start_local_runtime.sh
```

للإيقاف:

```bash
bash scripts/stop_local_runtime.sh
```

## ما تم إنجازه
- تهيئة Git محلي على فرع `handover-run-fix-demo`.
- تتبع ملفات المشروع الموروثة داخل Git.
- إزالة إعدادات قاعدة البيانات hardcoded من ملفات الاتصال.
- إضافة `.env.example`.
- إنشاء SQL schema لقاعدة بيانات ديمو.
- إنشاء demo seed data.
- إنشاء حسابات دخول تجريبية.
- إضافة smoke tests.
- إضافة Postman collection.
- إضافة متصفح ملفات آمن داخل لوحة الإدارة.
- كتابة تقارير عربية مفصلة داخل `docs-ar/`.

## حسابات الديمو
| الدور | المستخدم | كلمة المرور | مكان الدخول |
|---|---|---|---|
| Admin | `admin.demo@example.com` | `AdminDemo@123` | `/beaa/admin/account/login.php` |
| Operator | `operator.demo@example.com` | `OperatorDemo@123` | `/beaa/counter/` |
| Viewer | `viewer.demo@example.com` | `ViewerDemo@123` | `/beaa/admin/account/login.php` |

## قاعدة البيانات
| البند | القيمة |
|---|---|
| Database | `project_demo_db` |
| User | `project_demo_user` |
| Password | `ProjectDemo@12345` |
| Schema | `database/schema.sql` |
| Seed | `database/demo_seed.sql` |

## الأمر الكامل للتشغيل عند توفر sudo
```bash
cd /home/idealchip_server/meqs
sudo bash scripts/install_current_server.sh
BASE_URL=http://127.0.0.1:8000 tests/smoke.sh
```

## أمر الاختبار الحالي بدون sudo
```bash
PHP_CMD=/home/idealchip_server/meqs/.runtime/static-php/buildroot/bin/php \
MYSQL_CMD=/home/idealchip_server/meqs/.runtime/mariadb/bin/mariadb \
DB_HOST=127.0.0.1 DB_PORT=3307 \
DB_USER=project_demo_user DB_PASSWORD=ProjectDemo@12345 DB_NAME=project_demo_db \
BASE_URL=http://127.0.0.1:8000 tests/smoke.sh
```

## أهم المخاطر
- المشروع PHP قديم بدون framework ولا ORM.
- معظم SQL مكتوب مباشرة داخل الصفحات.
- لا توجد migrations أصلية من المطور السابق.
- مجلد `code/` يبدو نسخة مكررة وقد يسبب لبساً.
- لا توجد اختبارات أصلية.
- يجب عدم استخدام متصفح الملفات إلا خلف login وصلاحية admin.
