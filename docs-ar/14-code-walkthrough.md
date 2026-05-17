# 14 - شرح تفصيلي للكود

## نقطة الدخول الرئيسية
الملف `index.php` في الجذر يحول المستخدم إلى واجهات النظام. التطبيق الفعلي موجود داخل `beaa/`.

## الاتصال بقاعدة البيانات
الملف الرئيسي:

```text
beaa/api/db.php
```

يوفر:
- `$mysqli` لاتصالات mysqli المباشرة.
- `getRow($query)` لجلب صف واحد.
- `getValue($query)` لجلب قيمة واحدة.
- `getColumn($query)` لجلب عمود.
- `getArray($query)` و `getArrayAssoc($query)` لجلب قوائم.
- `executeQuery($query)` لتنفيذ query واحد.
- `executeMultiQuery($query)` لتنفيذ عدة queries.

بعد الإصلاح أصبح يقرأ الإعدادات من `.env`.

## اللغة والإعدادات
الملف:

```text
language.php
```

يعتمد على جدولين:
- `texts`: النصوص المترجمة.
- `settings`: إعدادات النظام.

أهم الدوال:
- `getTextValue($key, $lang)`
- `getSetting($key)`
- `setSetting($key, $newVal)`
- `getEventDigitMod()`

## لوحة الإدارة
المجلد:

```text
beaa/admin/
```

كل صفحة إدارة تضبط `$prev` ثم تستدعي:

```php
require_once './common/php_head.php';
```

الملف `php_head.php` يقوم بـ:
- `session_start()`
- تحميل اللغة.
- التحقق من login.
- التحقق من bitmask الصلاحيات.

أمثلة صلاحيات:
- `1`: التقارير والعمليات.
- `16`: إعدادات النظام.
- `32`: إدارة مستخدمي الإدارة.
- `64`: البحث.
- `128`: اللغات.

## نمط CRUD في الإدارة
كل module غالباً له:

```text
beaa/admin/<module>.php
beaa/admin/views/<module>/process.php
beaa/admin/views/<module>/form.php
beaa/admin/views/<module>/list.php
```

`process.php` يستقبل `mode`:
- `list`
- `add`
- `edit`
- `delete`

## مستخدمو الإدارة
الملفات:

```text
beaa/admin/account/login.php
beaa/admin/views/users/process.php
```

الجدول:

```text
users
```

بعد الإصلاح يتم التحقق من كلمة المرور باستخدام:

```sql
SHA2(password, 256)
```

## موظفو الكاونتر
الملفات:

```text
beaa/counter/
beaa/api/counter/index.php
beaa/admin/views/clerks/process.php
```

الجدول:

```text
clerks
```

تسجيل دخول الكاونتر يتم عبر `beaa/api/counter/index.php` مع `type=11`.

## التذاكر
الجدول الرئيسي:

```text
events
```

كل تذكرة تحتوي:
- الفئة `event_category`.
- الرقم `event_no`.
- الحالة `event_level`.
- الأولوية `event_priority`.
- المنطقة `event_zone`.
- الكشك `event_kiosk`.

السجلات المرتبطة:
- `events_logs`: النداءات والمعالجة.
- `transfers`: التحويلات.
- `displays_logs`: ما يظهر على الشاشات.
- `audios_logs`: النداءات الصوتية.

## الكشك
المجلد:

```text
beaa/api/kiosk/
```

أهم endpoints:
- `get.php`: جلب إعدادات الكشك والفئات.
- `set.php`: إصدار تذكرة واحدة.
- `set-bulk.php`: إصدار عدة تذاكر.
- `lastprinted.php`: آخر تذكرة مطبوعة.
- `set_cat_enabled.php`: تفعيل/تعطيل فئة.

## الشاشات
الشاشة العادية:

```text
beaa/display/
beaa/api/display/index.php
```

الشاشة الكبيرة:

```text
beaa/bigdisplay/
beaa/api/bigdisplay/
```

تعتمد الشاشات على polling عبر JavaScript، وتراقب تغييرات في:
- `displays.display_updated`
- `bigdisplays.display_updated`
- `settings.bulkStatus`

## التقارير
الملفات:

```text
beaa/admin/flow.php
beaa/admin/followups.php
beaa/admin/feedbacks.php
beaa/admin/tickets.php
beaa/admin/search.php
```

تعتمد على جداول:
- `events`
- `events_logs`
- `transfers`
- `followups`
- `feedback`

## متصفح الملفات المضاف
الملف:

```text
beaa/admin/file-browser.php
```

خصائصه:
- يعمل فقط بعد login.
- يحتاج صلاحية إعدادات النظام `$prev = 16`.
- read-only.
- يمنع الوصول إلى `.env`, `.git`, `.ssh`, dotfiles الحساسة.
- يسمح بفتح المجلدات وتنزيل الملفات العادية.

## ملفات الديمو والتشغيل
- `.env.example`: مثال إعدادات آمن.
- `database/create_demo_database.sql`: إنشاء قاعدة ومستخدم.
- `database/schema.sql`: إنشاء الجداول.
- `database/demo_seed.sql`: بيانات ديمو.
- `scripts/install_current_server.sh`: تثبيت وتشغيل كامل عند توفر root.
- `tests/smoke.sh`: اختبار سريع.
