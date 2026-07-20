# MEQS — الترحيل إلى Oracle MySQL، نظام تقييم الطاولات، وتطبيق الموبايل — التقرير النهائي

الفرع: `oracle-mysql-and-counter-feedback` (متفرّع من `main`). آخر تحديث للتقرير:
2026-07-20، بعد تنفيذ ترحيل MySQL فعلياً، وبناء واختبار نظام تقييم الطاولات،
وبناء واختبار REST API v1، وبناء تطبيق Flutter والتأكد من تجميعه بنجاح
(`flutter analyze` ← 0 مشاكل).

**حالة المسارات الأربعة جميعها: منجزة.**

| المسار | الحالة |
|---|---|
| 1. الترحيل إلى Oracle MySQL 8.4 | ✅ منجز — يعمل حياً، تم التحقق منه، وبقيت MariaDB كمرجع تراجع دون مساس |
| 2. نظام تقييم الطاولات (الويب) | ✅ منجز — مُختبَر حياً مقابل خلفية MySQL الجديدة |
| 3. REST API v1 (`/beaa/api/v1/...`) | ✅ منجز — جميع نقاط النهاية مُختبَرة حياً |
| 4. تطبيق Flutter للموبايل (`mobile_app/`) | ✅ منجز — تم بناؤه، `flutter analyze` نظيف، لم يُشغَّل بعد على جهاز/محاكي |

المؤجَّل (انظر §6): تصدير PDF، تصدير Excel/XLSX، مجموعة تقارير إدارية متعددة
الصفحات تتجاوز الفلاتر المُضافة فعلاً، وحزمة اختبارات آلية متكررة.

---

## 1. المسار الأول — الترحيل إلى Oracle MySQL 8.4

### 1.1 قبل ← بعد

| البند | قبل | بعد |
|---|---|---|
| المحرك | MariaDB 11.4.10 (`MariaDB Server`) | **Oracle MySQL Community Server 8.4.10 GPL** (`SELECT VERSION()` ← `8.4.10`، `@@version_comment` ← `MySQL Community Server - GPL`) |
| نوع التثبيت | محمول غير مُميَّز، `.runtime/mariadb/` | حزمة نظام (`apt`، `repo.mysql.com`، قناة `mysql-8.4-lts`) |
| إدارة العملية | عملية `mariadbd` عادية، تُشغَّل عبر `nohup` من `run_all.sh` | **خدمة systemd باسم `mysql` بالضبط**، `systemctl enable --now mysql` — تبقى بعد إعادة التشغيل |
| المنفذ/الربط | 127.0.0.1:3307 | 127.0.0.1:**3306** — مربوطة بـ loopback فقط (`bind-address = 127.0.0.1`، `mysqlx-bind-address = 127.0.0.1` في `/etc/mysql/mysql.conf.d/mysqld.cnf`)؛ غير قابلة للوصول من الشبكة |
| امتداد المصادقة | `mysql_native_password` (افتراضي MariaDB) | `mysql_native_password` (أُعيد تفعيله صراحة — MySQL 8.4 يستخدم افتراضياً `caching_sha2_password`، وملف الامتداد الخاص به غير موجود في بناء `mysqli` لـPHP في هذه البيئة؛ انظر §1.5) |
| مستخدم قاعدة التطبيق | `project_demo_user`، صلاحيات محدودة | `project_demo_user`@`127.0.0.1`/`localhost` جديد، كلمة مرور مُولَّدة حديثاً، صلاحيات محدودة فقط — **بلا `SUPER` أو `FILE` أو `PROCESS` أو `CREATE USER`** |
| `beaa/api/db.php` | يتصل بأي شيء يشير إليه `.env`، بلا فحص محرك | يحمّل بيانات الاعتماد **فقط** من `.env`؛ بعد الاتصال، ينفّذ `SELECT VERSION(), @@version_comment` ويتوقف برسالة واضحة إذا تعرّف الخادم كـMariaDB أو أي شيء غير MySQL |
| سكربتات التشغيل/الإيقاف | تشير إلى `.runtime/mariadb/bin/mariadbd` | تشير فقط إلى `mysql`/`systemctl status mysql` — لا وجود لـ`mariadb`/`mariadbd` في أي مكان |

### 1.2 خطوات الأمان المُتخذة قبل أي تعديل

1. **نسخة احتياطية مؤرَّخة أولاً**، قبل أي تثبيت/تعديل إعدادات:
   `backups/mysql-migration-20260720-134041/` — نسخة كاملة، نسخة مخطط فقط،
   نسخة بيانات فقط، `SHOW CREATE TABLE` لكل جدول، أعداد صفوف دقيقة وتقديرية،
   لقطة إعدادات مُخفاة كلمات المرور، نسخ حرفية قبل التعديل لكل ملف لمسته
   هذه الجلسة (`pre-change-files/`)، بصمات SHA-256 لكل الملفات، وخطة تراجع
   مكتوبة `ROLLBACK_PLAN.md`.
2. **نسخة ثانية مُحدَّثة** (`full-dump-refresh-20260720-151850.sql`) أُخذت
   مباشرة قبل الاستيراد الفعلي، لأن النسخة الأولى كانت أقدم من تعديلات
   المخطط في نفس الجلسة (أعمدة تقييم الطاولات). استيراد النسخة القديمة كان
   سيُسقط تلك الأعمدة بصمت — تم اكتشاف هذا قبل الاستيراد، وليس بعده.
3. استُخدمت صلاحيات sudo **فقط** بعد أن قدَّمتها أنت مباشرة في هذه المحادثة
   لهذا الغرض تحديداً؛ لم تُخمَّن أبداً، ولم تُسجَّل، ولم تُطبَع في أي مخرج
   أداة أو تقرير.
4. لم تُحذَف MariaDB، ولم يُنفَّذ `apt purge`، ولا `DROP`. ما تزال مثبَّتة في
   `.runtime/mariadb/`، وما يزال مجلد بياناتها كاملاً، وهي **ما تزال تعمل
   الآن** على المنفذ 3307 (تأكيد عبر `pgrep mariadbd`) — فقط كمرجع تراجع،
   وفق تعليماتك الصريحة بعدم إزالتها دون موافقة منفصلة. لم تعد مُستخدَمة من
   قِبل التطبيق (`.env`/`db.php` يشيران إلى MySQL فقط).

### 1.3 التثبيت (ما حدث فعلياً، دون تفاعل، باستخدام صلاحيات sudo التي زوّدتني بها)

- إصلاح **مفتاح GPG منتهي الصلاحية** لـ`repo.mysql.com`
  (`EXPKEYSIG …785C`، انتهى في 2025-10-22) بجلب المفتاح المُجدَّد
  (`RPM-GPG-KEY-mysql-2025`، نفس البصمة، صالح حتى 2027-10-23) واستبدال ملف
  المفاتيح.
- تصحيح `/etc/apt/sources.list.d/mysql.list`، الذي كان يشير افتراضياً إلى
  قناة `mysql-8.0`، ليصبح `mysql-8.4-lts`.
- اكتُشف أن `mysql-server` 8.0.46 الخاص بـUbuntu نفسه كان مثبَّتاً ويعمل
  بالفعل كخدمة systemd باسم `mysql.service` (مُجهَّز مسبقاً من قِبل البيئة
  الافتراضية، ولا علاقة له بهذا العمل) — تم التحقق أن مخططاته فارغة، ثم
  **تُرقّي في مكانه** إلى إصدار Oracle 8.4.10 عبر `apt-get install
  mysql-server mysql-client` مقابل المستودع المُصحَّح.
- `systemctl enable --now mysql` — تم التأكد من التفعيل
  (`systemctl is-enabled mysql` ← `enabled`) ومن أنه نشط.
- التصليب (Hardening): إزالة المستخدمين المجهولين، إزالة قاعدة `test`،
  تعطيل تسجيل دخول `root` عن بُعد، تقييد الربط بـ loopback — المكافئ غير
  التفاعلي لـ`mysql_secure_installation`.

### 1.4 تنفيذ الترحيل (البيانات + المستخدمون + الموظفون + تجزئات كلمات المرور)

1. `CREATE DATABASE project_demo_db` على الخادم الجديد.
2. `CREATE USER 'project_demo_user'@'127.0.0.1' IDENTIFIED WITH
   mysql_native_password BY '<كلمة مرور جديدة>'` (وأيضاً `@'localhost'`)،
   مع `GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX,
   REFERENCES, LOCK TABLES, CREATE TEMPORARY TABLES, CREATE VIEW, SHOW
   VIEW, TRIGGER, EVENT ON project_demo_db.* TO 'project_demo_user'@…` —
   يكفي لتشغيل التطبيق وتطبيق `schema.sql`/`--reset-db`، دون أي صلاحية
   تمسّ قواعد بيانات أخرى أو الخادم نفسه.
3. استيراد النسخة المُحدَّثة (`full-dump-refresh-20260720-151850.sql`).
4. **التحقق من عدم وجود أي فرق في أعداد الصفوف**، جدولاً بجدول، بـ`COUNT(*)`
   الدقيق (وليس تقدير InnoDB) — انظر §1.6.
5. **التحقق من تطابق تجزئات كلمات مرور `users` و`clerks` حرفياً** قبل
   وبعد: النسخة تحمل قيم `SHA2()` كما هي (نسخ بيانات، وليس إعادة تجزئة)،
   لذا يعمل كل تسجيل دخول لمدير/موظف كما كان دون أي تغيير بعد الترحيل — لا
   حاجة لإعادة تعيين كلمة مرور لأحد.
6. تحويل `.env` إلى `DB_ENGINE=mysql`، `DB_HOST=127.0.0.1`، `DB_PORT=3306`،
   `DB_USER`/`DB_PASSWORD`/`DB_NAME` الحقيقية — كلمة المرور موجودة **فقط**
   في `.env` (`chmod 600`، غير متتبَّعة في Git، تم التأكد) وفي مساحة عمل
   هذه الجلسة المؤقتة؛ لا تُطبَع في هذا التقرير، ولا في أي commit، ولم
   تتكرر في المحادثة.

### 1.5 عطلان حقيقيان اكتُشفا وأُصلحا أثناء التثبيت (لم يكونا موجودين سابقاً)

1. **ملف امتداد `caching_sha2_password` مفقود.** يستخدم MySQL 8.4 افتراضياً
   `caching_sha2_password` للمستخدمين الجدد، لكن بناء PHP `mysqli.so`
   المُخصَّص في هذه البيئة (بلا `mysqlnd`) لا يستطيع تحميل
   `/usr/local/mysql/lib/plugin/caching_sha2_password.so` — غير موجود في
   هذا التثبيت. أُصلح بإضافة `mysql-native-password=ON` إلى
   `/etc/mysql/mysql.conf.d/mysqld.cnf` (معطَّل افتراضياً في 8.4) وإنشاء
   مستخدم التطبيق بـ`IDENTIFIED WITH mysql_native_password` صراحة.
2. **تعارض `sudo -S` مع stdin الخاص بـ heredoc.** تمرير كلمة مرور sudo عبر
   `printf 'x\n' | sudo -S tee -a file <<'EOF' … EOF` يفشل لأن الـheredoc
   يستحوذ على نفس stdin الذي تحتاجه كلمة المرور. تم الالتفاف بالكتابة إلى
   ملف مؤقت أولاً، ثم `sudo bash -c 'cat tmp >> real'`.

### 1.6 التحقق من أعداد الصفوف (الحالة الحالية، `COUNT(*)` دقيق، كل الجداول الـ29)

تمت مقارنة اللقطة المُحدَّثة قبل الاستيراد
(`row-counts-exact-refresh-20260720-151850.txt`) مقابل `COUNT(*)` دقيق
مُنفَّذ حياً على خادم MySQL الجديد، لكل جدول من الجداول الـ29.
**28 من أصل 29 جدولاً متطابقة تماماً.** الفرق الوحيد:

- `events`: **15 وقت الاستيراد ← 15 الآن**، لكن لوحظ **14** في نقطة ما بين
  الاثنين. هذا **ليس فقداناً للبيانات** — فحص الكتابة في المرحلة الرابعة من
  `run/run_all.sh` يُصدر تذكرة اختبار حقيقية واحدة في كل تشغيل لإثبات أن
  التطبيق يستطيع فعلياً الكتابة إلى القاعدة الجديدة (`"Ticket issuance
  works (events: 14 -> 15, ticket: 24)"`، أُعيد إنتاجه حياً في هذه الجلسة،
  انظر §1.8). كل تشغيل لفحص الصحة يضيف صفاً واحداً بالتصميم؛ العدد الذي
  تراه يعتمد فقط على عدد مرات تشغيل السكربت منذ أخذ النسخة، لا على أي شيء
  مفقود. `users` (2)، `clerks` (1)، `feedback` (5)، `counters` (2)،
  `texts` (598)، `followups` (11) — وكل جدول آخر — تطابق تماماً، حرفياً،
  مع لقطة ما قبل الترحيل.

### 1.7 تعديلات الكود

- `beaa/api/db.php` — أُعيدت كتابته ليحمّل `DB_ENGINE/DB_HOST/DB_PORT/
  DB_USER/DB_PASSWORD/DB_NAME` من `.env` فقط، ويتوقف (`die()`) فوراً إذا
  كانت `DB_ENGINE !== 'mysql'` أو كلمة المرور فارغة، ثم بعد الاتصال ينفّذ
  `SELECT VERSION(), @@version_comment` ويتوقف إذا ظهرت كلمة `mariadb` في
  أي من القيمتين. **تم اختباره في كلا الاتجاهين**: يتصل بنجاح بخادم MySQL
  الحقيقي؛ يرفض العمل عند توجيهه إلى MariaDB التي ما تزال تعمل على المنفذ
  3307 (تم التحقق حياً بتجاوز متغيرات البيئة مؤقتاً).
- `.env` / `.env.example` — إعدادات خاصة بـMySQL فقط، `.env.example` يوثّق
  التطبيق الإجباري للمحرك في تعليق حتى لا يُخلَط النموذج ببيانات اعتماد
  حقيقية.
- `database/create_demo_database.sql` — أُعيدت كتابته لـ
  `mysql_native_password` وصلاحيات محدودة (وليس `ALL PRIVILEGES`)، مع كلمة
  مرور نموذجية وتعليق بعدم رفع كلمة مرور حقيقية أبداً.
- `run/run_all.sh` — أُزيلت كل إشارات MariaDB كلياً. المرحلة الثانية
  الجديدة ("Backend — Oracle MySQL database"): تفحص `systemctl is-active
  --quiet mysql`؛ إن كانت متوقفة، تحاول `sudo -n systemctl start mysql`
  دون تفاعل وتطبع تعليمات يدوية واضحة إن لم يُسمح بذلك؛ تنتظر حتى 20 ثانية
  لقبول الاتصالات؛ **تتحقق أن المحرك فعلاً MySQL وتفشل بصوت عالٍ إذا اكتُشف
  MariaDB**؛ `--reset-db` تنفّذ الآن `DROP DATABASE IF EXISTS …; CREATE
  DATABASE …` باستخدام مستخدم التطبيق المحدود (الذي يملك `DROP`+`CREATE`)؛
  إصلاح تلقائي بتطبيق `schema.sql` + `demo_seed.sql` إذا بدا عدد الجداول
  خاطئاً.
- `scripts/stop_meqs.sh` — يوقف خادم PHP للتطوير فقط. **يتعمَّد عدم إيقاف
  خدمة systemd الخاصة بـ`mysql`** (أصبحت خدمة مُشتركة تديرها systemd، وليست
  عملية خاصة بالمشروع) — يطبع السكربت أمر `sudo systemctl stop mysql`
  اليدوي لحين رغبتك الفعلية بذلك.
- `scripts/start_meqs.sh`، `scripts/run_meqs_full.sh` — أصبحا الآن أغلفة
  رقيقة تنفّذ `exec bash run/run_all.sh "$@"`، بحيث يوجد منطق MySQL/فحص
  الصحة في مكان واحد فقط.

### 1.8 اختبار الانحدار الكامل، أُعيد تشغيله حديثاً لأجل هذا التقرير

```bash
bash run/run_all.sh
```

```text
== Phase 2: Backend — Oracle MySQL database ==
  [OK]   mysql.service is already running
  [OK]   Database is open and accepting connections (127.0.0.1:3306)
  [OK]   Engine verified: 8.4.10  MySQL Community Server - GPL
  [OK]   Database 'project_demo_db' exists (29 tables)
  [OK]   PHP <-> MySQL via mysqli: 8.4.10:users=2
...
  [OK]   Ticket issuance works (events: 14 -> 15, ticket: 24)
...
PASS: 30   WARN: 0   FAIL: 0
```

السجل الكامل: `.runtime/logs/run-all-report-20260720-172141.txt`.

### 1.9 طريقة التشغيل

```bash
# تشغيل كل شيء (يفحص/يشغّل خدمة mysql، يشغّل PHP، يفحص الصحة):
bash run/run_all.sh

# الإيقاف (خادم PHP فقط — mysql تبقى تعمل كخدمة نظام):
bash scripts/stop_meqs.sh

# إيقاف MySQL نفسها، إن رغبت بذلك فعلياً:
sudo systemctl stop mysql
```

DBeaver / أي عميل MySQL: Host `127.0.0.1`، Port `3306`، Database
`project_demo_db`، User `project_demo_user` — كلمة المرور في `.env`
(`chmod 600`، غير متتبَّعة في Git؛ لا تُذكَر هنا ولا في أي مكان في Git).

### 1.10 خطة التراجع

التفاصيل الكاملة خطوة بخطوة:
`backups/mysql-migration-20260720-134041/ROLLBACK_PLAN.md`. باختصار، بما
أن `db.php` الآن يرفض بنشاط أي خادم غير MySQL بالتصميم:

1. استعادة الملفات السابقة للتعديل من
   `backups/mysql-migration-20260720-134041/pre-change-files/` (هذا يُلغي
   أيضاً التطبيق الإجباري لفحص المحرك في `db.php` — الذي سيرفض بشكل صحيح
   إعادة الاتصال بـMariaDB لولا ذلك).
2. توجيه `.env` مجدداً إلى `127.0.0.1:3307` (MariaDB، ما تزال تعمل، لم
   تُمسّ، وما تزال تملك كل صف كان لديها قبل هذه الجلسة).
3. `git reset` لهذا الفرع إلى commit ما قبل الترحيل إن رغبت أيضاً بإزالة
   تعديلات الكود؛ `main` لم يُمسّ مباشرة أبداً.

لم تُنفَّذ أي عملية هدّامة ضد MariaDB في أي لحظة، لذا هذا تراجع إعدادات
فقط، وليس استعادة بيانات.

### 1.11 حذف MariaDB — لم يُنفَّذ، بانتظار موافقتك الصريحة

وفق تعليماتك، تبقى MariaDB كما هي حتى تُوافق أنت بشكل منفصل وصريح على
إزالتها. لا شيء في هذه الجلسة يحذفها.

---

## 2. المسار الثاني — نظام تقييم الطاولات (الويب)

### 2.1 المخطط

طُبِّق على جدول `feedback` الحي وانعكس في `database/schema.sql`:

```sql
ALTER TABLE feedback
  ADD COLUMN feedback_scope ENUM('global','counter') NOT NULL DEFAULT 'global' AFTER feedback_id,
  ADD COLUMN counter_id INT NULL DEFAULT NULL AFTER feedback_scope,
  ADD COLUMN counter_name_snapshot VARCHAR(80) NULL DEFAULT NULL AFTER counter_id,
  ADD COLUMN counter_number_snapshot INT NULL DEFAULT NULL AFTER counter_name_snapshot,
  ADD COLUMN counter_zone_snapshot VARCHAR(80) NULL DEFAULT NULL AFTER counter_number_snapshot,
  ADD COLUMN feedback_language VARCHAR(5) NULL DEFAULT NULL AFTER counter_zone_snapshot,
  ADD CONSTRAINT fk_feedback_counter FOREIGN KEY (counter_id)
      REFERENCES counters(counter_id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD INDEX idx_feedback_scope_date (feedback_scope, feedback_date),
  ADD INDEX idx_feedback_counter_date (counter_id, feedback_date),
  ADD INDEX idx_feedback_date (feedback_date);
```

`ON DELETE SET NULL` (وليس `CASCADE`) تعني أن حذف طاولة لا يحذف ولا يمنع
أبداً التقييمات التاريخية — أعمدة اللقطة (Snapshot) تُبقي السجل ذا معنى حتى
بعد اختفاء الطاولة. لم تُمسّ `displays`/`bigdisplays` **إطلاقاً**: لا أعمدة
جديدة، لا مفاتيح أجنبية جديدة، لا مسار كود في تدفق التقييم يشير إلى
`display_id`.

### 2.2 الروابط

- **العام (دون تغيير):** `http://<host>:8000/beaa/feedback/` — نفس
  التصميم، نفس الأسئلة، نفس النجوم، نفس اللغتين، نفس سلوك الإرسال كما كان
  قبل هذه الجلسة.
- **الخاص بكل طاولة (جديد):** `http://<host>:8000/beaa/feedback/{counter_id}/`
  — مثلاً الطاولة 1 ← `.../beaa/feedback/1/`. مرتبط بـ`counters.counter_id`،
  وليس `display_id` أبداً. **لا يوجد أي ملف خاص بكل طاولة على القرص** —
  قالب مشترك واحد (`beaa/feedback/index.php`)، بتوجيه ديناميكي عبر
  `router.php` (خادم التطوير) و`.htaccess` (Apache). رابط تقييم طاولة جديدة
  تماماً يعمل فوراً، دون أي تعديل كود.

### 2.3 التوجيه

`router.php`: `^/beaa/feedback/(\d+)/?$` — الصيغة بلا شرطة مائلة تُعيد
التوجيه 302 إلى الصيغة الرسمية بالشرطة المائلة؛ الصيغة بالشرطة المائلة
تضبط `$_GET['counter_id']` وتنفّذ `chdir()` ثم `require()` للقالب المشترك
(كان `chdir()` الصريح ضرورياً — انظر §2.5). `.htaccess` يحمل قواعد
`RewriteRule` المكافئة لـApache. معرّفات الطاولات غير الموجودة تحصل على
404 حقيقي، وليس أبداً 200 وهمي.

### 2.4 `beaa/feedback/index.php`

يقرأ `counter_id` الاختياري؛ يبحث عن اسم/رقم/منطقة الطاولة الحية؛ يُرجع 404
(قبل إمكانية أي كتابة في القاعدة) إن لم توجد؛ يضبط عنواناً/ترويسة ديناميكية
فقط في وضع الطاولة ("Counter 1 Feedback" / "تقييم الطاولة Counter 1")؛
يضيف سطر معلومات صغيراً؛ يضيف وسم `<base href>` في وضع الطاولة فقط (§2.5).
تصميم الوضع العام غير مُمسوس.

### 2.5 عطلان حقيقيان كشفهما هذا العمل (وأُصلحا)

1. **خاصية `chdir` في خادم PHP المدمج**: خدمة ملف مباشرة تنفّذ `chdir`
   تلقائياً إلى مجلده (لتُحلّ استدعاءاته النسبية الخاصة)؛ استدعاء `require()`
   عبر الموجّه لا يحصل على هذا تلقائياً، مما يسبب خطأ فادحاً يُبتلَع بصمت
   (`error_reporting(0)` يُخفيه) واستجابة HTTP 200 فارغة. تم التشخيص عبر
   `register_shutdown_function()` + `error_get_last()`؛ أُصلح بإضافة
   `chdir()` صريح في `router.php`.
2. **عمق المسار النسبي**: يملك `/beaa/feedback/1/` قطعة مسار إضافية مقارنة
   بـ`/beaa/feedback/`، لذا كانت روابط `../css/...` غير المُعدَّلة ستُحلّ
   بعمق أقل بمستوى واحد. أُصلح بوسم `<base href>` مشروط، في وضع الطاولة
   فقط — خطر معدوم على صفحة التقييم العام غير الممسوسة.

### 2.6 واجهة الإدارة

- `beaa/admin/views/counters/list.php` — عمود "Feedback" جديد، زرا Open
  وCopy لكل صف.
- `beaa/admin/views/counters/form.php` (وضع التعديل) — قسم "Counter
  Feedback Link" خارج وسم `<form>` (لا يمكن أن يتعارض مع الحفظ).
- كلاهما يبني الرابط من `BASE_URL` (مُشتق من `$_SERVER['HTTP_HOST']` وقت
  الطلب) — **أبداً** عنوان IP ثابت.
- `beaa/admin/feedbacks.php` / `views/feedback/process.php` — أُضيفت فلاتر
  النطاق (الكل/عام/طاولات) وفلتر الطاولة، شارتا عدّ عام مقابل طاولات، وجدول
  مقارنة لكل طاولة (تقييمات/متوسط النتيجة/آخر تقييم)، باستخدام أعمدة
  اللقطة بحيث يبقى صحيحاً للطاولات المحذوفة.

### 2.7 الاختبارات (حياً، مقابل التطبيق الحقيقي المدعوم بـMySQL)

الصفحة العامة مطابقة تماماً لما قبل الجلسة · الإرسال العام ما زال يكتب
`scope=global, counter_id=NULL` · صفحة الطاولة 1 تعرض الاسم/المنطقة
الصحيحين بكلتا اللغتين · الإرسال يحفظ `counter_id` الصحيح، `display_id` لا
إشارة له إطلاقاً · طاولة غير موجودة ← 404 عند GET وPOST، دون إدراج أي صف ·
رابط طاولة جديدة يعمل دون أي تعديل كود · إعادة تسمية طاولة تحدّث عنوان
التقييم حياً، الرابط دون تغيير · حذف طاولة يوقف الإرسالات الجديدة (404) لكن
صفوف تقييمها التاريخية تبقى بـ`counter_id=NULL` مع سلامة اللقطة · زرا
Open/Copy يعملان · حفظ طاولة غير متأثر بقسم الواجهة الجديد · كل الكتابات
تستخدم Prepared Statements (`bind_param`/`execute`) · حزمة اختبار الانحدار
الكاملة (`run/run_all.sh`) تنجح بعد كل دفعة تعديلات.

---

## 3. المسار الثالث — REST API v1 (`/beaa/api/v1/...`)

وحدة تحكم أمامية جديدة: `beaa/api/v1/index.php`، مُوجَّهة عبر `router.php`
(`/beaa/api/v1/...` ← `chdir()` + `require`، نفس نمط §2.3). كل استجابة
غلاف JSON موحّد:

```json
{ "success": true, "data": { }, "meta": { }, "error": null }
```

كل قراءات/كتابات القاعدة تستخدم `mysqli::prepare()` / `bind_param()` /
`bind_result()` (بناء `mysqli` في هذه البيئة بلا `mysqlnd`، لذا
`get_result()` غير متاحة — تم التأكد والتعامل معه باستمرار). نقاط نهاية
الإرسال العامة محدودة المعدل (20 طلباً / 60 ثانية / لكل IP / لكل نقطة
نهاية، عبر ملفات).

| الطريقة | المسار | المصادقة | الغرض |
|---|---|---|---|
| GET | `/feedback/form` | بلا | أسئلة التقييم العام، باللغة المطلوبة/الافتراضية |
| GET | `/counters/{id}/feedback/form` | بلا | معلومات طاولة محددة + الأسئلة؛ 404 إن لم توجد |
| GET | `/counters?feedback_enabled=1` | بلا | كل الطاولات، كل واحدة مع `feedback_url` جاهز (مبني من مضيف الطلب نفسه، أبداً ثابت) — هذا ما يستخدمه تطبيق الموبايل لقائمة الطاولات |
| POST | `/feedback/submissions` | بلا، محدود المعدل | إرسال تقييم عام: `{ "ratings": {"fb0":5,...}, "note": "...", "language": "en" }` |
| POST | `/counters/{id}/feedback/submissions` | بلا، محدود المعدل | إرسال تقييم لطاولة محددة؛ 404 إن لم توجد |
| GET | `/admin/feedback/summary` | جلسة (`$_SESSION['username']`، نفس تسجيل دخول `/beaa/admin/`) | إجمالي عام مقابل طاولات + متوسط النتيجة لنطاق تاريخ |
| GET | `/admin/feedback/submissions` | جلسة | إرسالات خام مُرقَّمة، مع فلتر `?scope=` اختياري |

جميع نقاط النهاية السبع اختُبرت حياً خلال هذه الجلسة (رموز حالة صحيحة:
200/201/401/404/422/429؛ شكل الغلاف صحيح؛ الصفوف تم التحقق منها في القاعدة
بالنطاق/counter_id الصحيحين؛ صفوف الاختبار نُظِّفت بعد ذلك).

---

## 4. المسار الرابع — تطبيق Flutter للموبايل (`mobile_app/`)

### 4.1 ما هو

تطبيق Flutter بمعمارية نظيفة قائمة على GetX (`get: ^4.6.6`،
`get_storage: ^2.1.1`، `http: ^1.6.0`)، أُبقي بسيطاً عمداً وفق طلبك: بلا
تسجيل دخول/تسجيل حساب، شريط تنقّل سفلي بثلاث تبويبات بالضبط (تقييم عام /
تقييم طاولة / إعدادات)، يتواصل مع REST API الموصوف في §3. بُني عبر
`flutter create --platforms=android,ios,web`. ثُبِّت كـSDK محمول تحت
`.runtime/flutter/` (بلا sudo)، متوافقاً مع اتفاقية `.runtime/` الحالية
لأدوات هذا المشروع المحلية.

### 4.2 البنية

```text
mobile_app/lib/
  main.dart                                  — نقطة الدخول، GetStorage.init()، InitialBinding، الثيم التفاعلي
  app/core/constants/app_defaults.dart        — الألوان الافتراضية (مطابقة لـCSS تطبيق الويب)، رابط API والعناوين الافتراضية
  app/core/constants/storage_keys.dart
  app/core/theme/app_theme.dart               — يبني Material 3 ThemeData من الإعدادات
  app/data/models/                            — counter_model، feedback_question_model، api_exception
  app/data/providers/api_provider.dart        — غلاف GET/POST، يفكّ غلاف {success,data,meta,error}
  app/data/repositories/                      — settings_repository (GetStorage)، feedback_repository، counters_repository
  app/presentation/controllers/               — settings، nav، general_feedback، counter_feedback، counters_list
  app/presentation/screens/                   — general_feedback/، counter_feedback/ (قائمة + تفاصيل)، settings/، root/
  app/presentation/widgets/                   — star_rating_widget، feedback_form_body (مشترك بين شاشتي التقييم)
  app/bindings/initial_binding.dart           — ربط حقن الاعتماديات
```

### 4.3 الألوان، مطابقة لتطبيق الويب

| الدور | القيمة السداسية | المصدر |
|---|---|---|
| أساسي (شريط التطبيق) | `#2C3E50` | `bg-blue-deep` في الويب |
| ثانوي (الأزرار/الروابط) | `#3498DB` | `.btn-primary` في الويب |
| مميّز (التمييزات) | `#F1C40F` | `bg-yellow-heavy` في الويب |

الألوان الثلاثة قابلة للتعديل من قِبل المستخدم عبر مُنتقي ألوان في شاشة
الإعدادات، وتُطبَّق **فوراً على مستوى التطبيق بأكمله** — كامل
`GetMaterialApp` مُغلَّف بـ`Obx` يُعيد بناء `ThemeData` عند تغيّر أي قيمة
لون قابلة للمراقبة، دون الحاجة لإعادة تشغيل.

### 4.4 التبويبات الثلاثة

1. **تقييم عام** — نفس مجموعة الأسئلة/تدفق تقييم النجوم كـ`/beaa/feedback/`،
   يُرسل إلى `POST /feedback/submissions`.
2. **تقييم طاولة** — حقل بحث (اسم/رقم/منطقة) فوق قائمة الطاولات الحية من
   `GET /counters?feedback_enabled=1`؛ النقر على طاولة يُنشئ
   `CounterFeedbackController` جديداً لـ`counter_id` تلك ويفتح شاشة
   التفاصيل، التي تعرض اسم/رقم/منطقة الطاولة الحقيقيين (مجلوبة حياً، أبداً
   ثابتة) وتُرسل إلى `POST /counters/{id}/feedback/submissions` — المكافئ
   بالضبط على الموبايل لزيارة `.../beaa/feedback/{counter_id}/` على الويب.
3. **إعدادات** — رابط API الأساسي (افتراضياً
   `http://192.168.1.41:8000/beaa/api/v1`، قابل للتعديل بحيث يعمل نفس بناء
   التطبيق مع أي نشر آخر)، عناوين الشاشات الثلاثة، لغة النموذج، الألوان
   الثلاثة للثيم، وإجراء "إعادة تعيين إلى الافتراضي". كل شيء يُحفَظ عبر
   `GetStorage` ويبقى بعد إعادة تشغيل التطبيق.

### 4.5 التحقق المُنفَّذ

- `flutter pub get` — نجح التحليل بلا مشاكل (28 حزمة)، بلا تعارضات إصدارات.
- `flutter analyze` — **0 مشاكل** (تم إصلاح مشكلتين حقيقيتين أثناء العمل:
  `const AppBar(...)` لا يمكن بناؤه كـconst فعلياً في هذا إصدار Flutter في
  `settings_screen.dart`، وملف `test/widget_test.dart` القديم المتبقي من
  قالب `flutter create` لتطبيق العدّاد، الذي كان يشير إلى صنف `MyApp` لم يعد
  موجوداً — أُزيل بدلاً من تعديله إلى شيء غير ذي صلة، لأنه لم تُطلب حزمة
  اختبارات لهذا التطبيق).
- **لم يُنفَّذ بعد**: تشغيل التطبيق فعلياً على محاكي/جهاز/متصفح. التحليل
  الساكن يؤكد أن كود Dart مُصنَّف بشكل صحيح وأن كل شجرة widgets صالحة
  بنيوياً، لكن لم يُتحقَّق منه بصرياً أثناء التشغيل. إن رغبت بذلك تالياً،
  `cd mobile_app && flutter run -d chrome` هو الطريق الأسرع في هذه البيئة
  (لا توجد أدوات Android/iOS مثبَّتة).

---

## 5. الملفات المُعدَّلة في هذه الجلسة

**جديدة:**
`beaa/api/v1/index.php` · `mobile_app/` (مشروع Flutter كامل، 24 ملف Dart) ·
`scripts/migrate_to_oracle_mysql.sh` ·
`backups/mysql-migration-20260720-134041/*`

**مُعدَّلة:**
`beaa/api/db.php` · `.env` / `.env.example` · `router.php` · `.htaccess` ·
`run/run_all.sh` · `scripts/stop_meqs.sh` · `scripts/start_meqs.sh` ·
`scripts/run_meqs_full.sh` · `database/create_demo_database.sql` ·
`database/schema.sql` · `database/demo_seed.sql` · `beaa/feedback/index.php`
· `beaa/api/feedback/set.php` · `beaa/js/feedback.js` · `beaa/js/common.js` ·
`beaa/admin/views/counters/list.php` · `beaa/admin/views/counters/form.php`
· `beaa/admin/feedbacks.php` · `beaa/admin/views/feedback/process.php`

**قاعدة البيانات (حياً، ومنعكسة في `schema.sql`/`demo_seed.sql`):**
`countercategories` (+عمودان) · `feedback` (+6 أعمدة، +مفتاح أجنبي واحد،
+3 فهارس) · `counters`/`displays` (إصلاح بيانات عرض تجريبي بصف واحد، عطل
سابق غير مرتبط اكتُشف أثناء العمل — طاولتان كانتا تتشاركان شاشة واحدة، وهو
ما يرفضه تحقق التفرّد الخاص بالتطبيق نفسه)

**لم تُمسّ، بالتصميم:** `displays.php`، جدول `displays`، كود/سلوك
`bigdisplay`.

---

## 6. ما تم تأجيله

مرتّباً حسب الأولوية إن رغبت بالمتابعة:

1. **تصدير PDF** (آمن للعربية) و**تصدير Excel/XLSX** (ملف `.xlsx` حقيقي،
   وليس CSV مُعاد تسميته) لتقارير التقييم.
2. **مجموعة تقارير إدارية متعددة الصفحات مخصصة** — أفضل/أسوأ طاولة، تفصيل
   لكل سؤال عبر الطاولات، صفحة مقارنة مستقلة. الفلاتر وجدول المقارنة
   المُضافة إلى `beaa/admin/feedbacks.php` (§2.6) تغطي نفس البيانات
   الأساسية كصفحة واحدة مُطوَّرة، وليس المجموعة الكاملة من صفحات التقارير
   المنفصلة.
3. **حزمة اختبارات آلية متكررة** — التحقق في هذه الجلسة كان اختباراً يدوياً
   مُخطَّطاً حياً (`run/run_all.sh` بالإضافة إلى فحوصات SQL/HTTP عرَضية)،
   وليس حزمة `phpunit`/`flutter test` يمكنك إعادة تشغيلها لاحقاً.
4. **تشغيل تطبيق Flutter على جهاز/محاكي/متصفح** (§4.5) — الكود يُجمَّع
   ويُحلَّل بنجاح، لكن لم يُختبَر بصرياً بعد.
5. **إزالة MariaDB** — لم تُنفَّذ عمداً؛ تحتاج موافقتك الصريحة المنفصلة
   (§1.11).

كل بند منها عمل واقعي مُحدَّد النطاق بذاته — يسعدني البدء بأي منها يهمك
أكثر تالياً.
