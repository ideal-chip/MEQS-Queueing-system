# 13 - الإصلاحات المطبقة

## 1. إعداد قاعدة البيانات من البيئة
- المشكلة: بيانات اتصال MySQL كانت hardcoded داخل PHP.
- السبب: لا يوجد `.env` أو config مركزي.
- الملفات: `beaa/api/db.php`, `beaa/admin/account/config.php`, `code/beaa/api/db.php`, `.env.example`.
- الإصلاح: إضافة loader بسيط لـ `.env` واستخدام متغيرات `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`.
- التحقق: ضع `.env` ثم افتح صفحة login أو شغل `tests/smoke.sh`.

## 2. استبدال `PASSWORD()`
- المشكلة: MySQL 8 لا يدعم دالة `PASSWORD()` للاستخدام العام.
- السبب: الكود قديم ويستخدم hashing داخل MySQL.
- الملفات: ملفات login وusers/clerks/counter في `beaa/` و`code/`.
- الإصلاح: استخدام `SHA2(password,256)` للديمو.
- التحقق: seed بيانات الديمو ثم سجل الدخول بالحسابات التجريبية.

## 3. إضافة schema وseed
- المشكلة: لا توجد migrations أو SQL setup.
- الملفات: `database/schema.sql`, `database/demo_seed.sql`, `database/create_demo_database.sql`.
- الإصلاح: إنشاء مخطط ديمو وبيانات تشغيل أساسية.
- التحقق: نفذ ملفات SQL ثم افتح لوحة الإدارة.

## 4. إضافة smoke tests
- المشكلة: لا توجد اختبارات.
- الملف: `tests/smoke.sh`.
- الإصلاح: اختبار أوامر التشغيل والاتصال والصفحات الأساسية.
- التحقق: `BASE_URL=http://127.0.0.1:8000 tests/smoke.sh`.

## 5. إضافة متصفح ملفات آمن
- المشكلة: طلب وجود web file browser للملفات.
- السبب: لا توجد feature أصلية لذلك في المشروع.
- الملفات: `beaa/admin/file-browser.php`, `beaa/admin/common/nav.php`.
- الإصلاح: إضافة متصفح read-only يعمل بعد login وبصلاحية إعدادات النظام، ويمنع الوصول إلى `.env` و `.git` و `.ssh`.
- التحقق: سجل دخول admin ثم افتح `/beaa/admin/file-browser.php`.

## 6. إضافة سكربت تثبيت للسيرفر الحالي
- المشكلة: السيرفر لا يحتوي PHP/MySQL ولا يمكن التشغيل بدون صلاحية root.
- الملف: `scripts/install_current_server.sh`.
- الإصلاح: سكربت يثبت المتطلبات، ينشئ قاعدة الديمو، يشغل seed، ويضيف systemd service.
- التحقق: `sudo bash scripts/install_current_server.sh`.
