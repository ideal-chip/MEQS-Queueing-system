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
