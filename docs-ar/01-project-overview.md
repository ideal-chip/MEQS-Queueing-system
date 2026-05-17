# 01 - نظرة عامة على المشروع

## ما هو المشروع؟
المشروع هو نظام إدارة طوابير وخدمة عملاء باسم iDEAL-Q. الكود مبني بأسلوب PHP كلاسيكي بدون framework، ويحتوي على واجهات الإدارة، الكاونتر، شاشة العرض، شاشة العرض الكبيرة، الكشك، التقييم، وواجهات API داخل مجلد `beaa/`.

يوجد مجلد `code/` يحتوي نسخة مكررة تقريباً من ملفات التشغيل. النسخة العملية التي تم اعتمادها في التسليم هي `beaa/` في جذر المشروع.

## الهدف من النظام
- إصدار أرقام انتظار من الكشك.
- ربط التذاكر بالفئات والخدمات.
- تسجيل دخول موظف الكاونتر واستدعاء/إنهاء/تحويل التذاكر.
- عرض آخر النداءات على شاشات العرض.
- إدارة المستخدمين والموظفين والفئات والكاونترات والشاشات من لوحة الإدارة.
- جمع تقييمات ومتابعات وتقارير تشغيلية.

## المكونات الرئيسية
- `beaa/admin/`: لوحة الإدارة.
- `beaa/counter/`: واجهة موظف الكاونتر.
- `beaa/display/`: شاشة العرض العادية.
- `beaa/bigdisplay/`: شاشة العرض الكبيرة.
- `beaa/bulkcall/`: واجهة النداء الجماعي.
- `beaa/feedback/`: واجهة تقييم الخدمة.
- `beaa/api/`: endpoints يستخدمها الويب والكشك والشاشات.
- `beaa/files/`: أصول النظام مثل الشعارات والملفات الصوتية إن وجدت.
- `beaa/css/`: ملفات CSS.
- `database/`: ملفات schema وseed demo التي تمت إضافتها.
- `docs-ar/`: تقارير التسليم العربية.
- `tests/`: smoke tests مضافة للتسليم.

## التقنية المستخدمة
- Backend/Frontend: PHP pages مباشرة مع HTML/CSS/JavaScript/jQuery.
- Database: MySQL/MariaDB.
- ORM/Migrations: لا يوجد ORM ولا migrations رسمية.
- Authentication: جلسات PHP `$_SESSION`، مع جدول `users` للإدارة وجدول `clerks` للكاونتر.
- Password hashing بعد الإصلاح: `SHA2(password, 256)` في MySQL.
- File upload/storage: لا توجد feature file manager عامة. توجد ملفات static داخل `beaa/files/` ومكتبة I18N مرفقة.
- Background jobs/cron: لا توجد cron jobs واضحة في الكود. الشاشات تعتمد polling عبر JavaScript و endpoints مثل `checkupdate.php`.
- External services: إعدادات SMS موجودة كجداول/واجهات جزئية، لكن لا توجد مفاتيح أو تكامل موثق.

## طريقة التشغيل المحلية
المتطلبات:
- PHP 7.4 أو 8.1 مع `mysqli`.
- MySQL أو MariaDB.
- Web server مثل Apache/Nginx أو PHP built-in server للتجربة.

أوامر تجربة محلية:
```bash
cp .env.example .env
mysql -uroot -p < database/create_demo_database.sql
mysql -uroot -p project_demo_db < database/schema.sql
mysql -uroot -p project_demo_db < database/demo_seed.sql
php -S 127.0.0.1:8000
```

الروابط:
- الإدارة: `http://127.0.0.1:8000/beaa/admin/account/login.php`
- الكاونتر: `http://127.0.0.1:8000/beaa/counter/`
- الكشك: `http://127.0.0.1:8000/beaa/api/kiosk/get.php?id=1`
- الشاشة الكبيرة: `http://127.0.0.1:8000/beaa/bigdisplay/?id=1`

## العلاقات بين backend/frontend/database
المشروع monolith: صفحات PHP تولد HTML وتستدعي قاعدة MySQL مباشرة. الواجهات تستخدم AJAX لاستدعاء `beaa/api/*`، وهذه endpoints تقرأ/تكتب مباشرة في MySQL عبر `beaa/api/db.php`.
