# 00 - ملخص التسليم التنفيذي

## حالة التشغيل
لم يعمل المشروع على هذا السيرفر حتى الآن لأن السيرفر لا يحتوي حزم التشغيل الأساسية:
- لا يوجد `php`.
- لا يوجد `mysql` أو `mariadb`.
- لا يوجد `apache2` أو `nginx`.
- لا يوجد `docker`.
- المستخدم الحالي `idealchip_server` لا يملك sudo بدون كلمة مرور.

هذا يعني أن المشروع لا يستطيع العمل حالياً حتى لو كان الكود صحيحاً. تم تجهيز سكربت تثبيت كامل:

```bash
sudo bash scripts/install_current_server.sh
```

بعد تشغيله بصلاحية root، يفترض أن يعمل المشروع على:

```text
http://127.0.0.1:8000/beaa/admin/account/login.php
http://127.0.0.1:8000/beaa/counter/
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

## أهم المخاطر
- المشروع PHP قديم بدون framework ولا ORM.
- معظم SQL مكتوب مباشرة داخل الصفحات.
- لا توجد migrations أصلية من المطور السابق.
- مجلد `code/` يبدو نسخة مكررة وقد يسبب لبساً.
- لا توجد اختبارات أصلية.
- يجب عدم استخدام متصفح الملفات إلا خلف login وصلاحية admin.
