# 09 - تقرير التسليم الفني

## المعمارية
تطبيق PHP monolith. لا توجد طبقة services واضحة ولا ORM. كل صفحة أو endpoint تستدعي `beaa/api/db.php` وتنفذ SQL مباشرة.

## وحدات backend
- Auth admin: `beaa/admin/account/login.php`
- Auth counter: `beaa/api/counter/index.php` type `11`
- Queue events: `events`, `events_logs`, `transfers`
- Kiosk API: `beaa/api/kiosk/*`
- Displays API: `beaa/api/display/*`, `beaa/api/bigdisplay/*`
- Admin CRUD: `beaa/admin/views/*/process.php`

## تدفق المصادقة
الإدارة تستخدم جدول `users` و `$_SESSION['username']` و bitmask في `user_privileges`.
الكاونتر يستخدم جدول `clerks` و `$_SESSION['clerkID']` و `$_SESSION['counterID']`.

## المخاطر ونقاط الضعف
- SQL raw في أغلب الملفات، وبعضها قابل للحقن إذا وصلته مدخلات غير موثوقة.
- لا توجد migrations رسمية.
- لا توجد tests أصلية.
- تكرار مجلد `code/` قد يسبب لبساً في النشر.
- لا توجد إدارة secrets حديثة قبل الإصلاح.
- عدم وجود CSRF protection في نماذج الإدارة.

## تحسينات مقترحة
- توحيد نسخة واحدة من الكود وإزالة/أرشفة `code/` بعد موافقة المالك.
- تحويل queries الحساسة إلى prepared statements.
- إضافة migrations رسمية.
- إضافة role model واضح بدل bitmask غير موثق.
- إضافة logging مركزي وbackup routine.
- تفعيل HTTPS وقيود session cookies.
