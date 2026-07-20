# iDEAL-Q — سجل الإصلاحات

سجل زمني للملفات التي جرى التعامل معها خلال جلسة الإصلاح بتاريخ 2026-07-20،
والأوامر الدقيقة المُستخدَمة للتحقق من كل دفعة. راجع أرقام الأعطال (Bug IDs)
مقابل [BUG_REGISTER_AR.md](BUG_REGISTER_AR.md) لتفاصيل السبب الجذري.

## ملفات جديدة تم إنشاؤها
- `router.php` — ملف توجيه خادم التطوير (BUG-0001)
- `run/run_all.sh` — سكربت شامل لمسح الذاكرة المؤقتة/تشغيل كل شيء/فحص الصحة/التقرير
- `beaa/css/feedback.css`، `beaa/css/bulkcall.css`، `beaa/css/bigdisplay.css`، `beaa/css/bd-bulk-slides.css`، `beaa/css/report.css`، `beaa/css/search.css`، `beaa/css/counter.css`
- `beaa/js/feedback.js`، `beaa/js/counter.js`، `beaa/js/followup.js`، `beaa/js/audios.js`، `beaa/js/morepapers.js`، `beaa/js/adminbulk.js`، `beaa/js/flow.js`، `beaa/js/search.js`، `beaa/js/subcategories.js`، `beaa/js/report.js`، `beaa/js/Chartjs_utils.js`، `beaa/js/report_followups.js`، `beaa/js/report_followups_charts.js`
- `beaa/api/feedback/set.php` — نقطة نهاية استقبال التقييمات (BUG-0003)
- مكتبات خارجية مُضمَّنة (إصدارات رسمية دون تعديل): `beaa/js/jquery.barrating.min.js`، `beaa/css/animate.css`، ملفات سمة `fontawesome-stars(-o).css`، `beaa/js/moment.min.js`، `beaa/js/Chartjs.min.js`، `beaa/js/jQuery.print.min.js`، `beaa/js/jquery-ui(-1.12.1).min.js` + `.css`، `beaa/js/FileSaver.min.js`، `beaa/js/chartist/*`، `beaa/js/echarts.common.min.js`، `beaa/js/minified/*` (sceditor وإضافاته)
- صور مُولَّدة: `beaa/files/add.png`، `delete.png`، `check.png`، `uncheck.png`، `shortcut_icons/star.png`؛ وأُعيد استخدام `logo-idealchip.png`، `moenv-logo-ar/en.jpg`

## ملفات تم تعديلها (كود)
- `scripts/start_meqs.sh`، `scripts/run_meqs_full.sh` — ربط `router.php`
- `.htaccess` — إضافة قواعد إعادة التوجيه الناقصة
- `beaa/feedback/index.php` — خيار افتراضي فارغ `<option>` في قوائم التقييم (BUG-0004)
- 18 ملفًا ضمن `beaa/admin/views/*/list.php`، و`beaa/admin/languages.php`، و`beaa/admin/morepapers.php` — إصلاح الشرطة المائلة في `$filesPath` (BUG-0006)
- `beaa/admin/views/{countersCategories,clerks,displays,zones,bigDisplayBulk}/list.php`، `beaa/api/audio/index.php` — إصلاح منفذ `mysqli_connect()` (BUG-0007)
- `beaa/admin/views/followups/process.php` — قيمة افتراضية لوسيط `GetPercenatage()` (BUG-0015)
- `beaa/css/common.css` — إضافة نحو 70 صنف أداة مساعدة غير مُعرَّفة سابقًا (`pad-*`، `s-*`، `round-*`، `sh-*`، `font-*`، `corner-*`، `ribbon-*`، `bg-*`، `.modal-center`، ...) مُستخدَمة في الموقع بأكمله

## تغييرات قاعدة البيانات
- `database/schema.sql` — جدول `followups`: إضافة `day_order_no`، `event_id`، `extension_no`؛ جدول `transfers`: إضافة `transfer_zone`، وتخفيف قيد `transfer_cat` ليقبل NULL (BUG-0010، BUG-0011)
- `database/demo_seed.sql` — 598 ترجمة نصية (كانت نحو 14 فقط)، إضافة 11 مفتاح إعداد (`bigdisplayMessageCount`، `maxCount`، `counterSwitchServices`، `counter_callDelaySeconds`، `counter_recallTimes`، `displayType`، `bulkDelay`، `maxTransactions`، `maxBulkNumber`، `minimumCategoriesCount`)، 3 أرقام تحويلة، 10 صفوف إضافية في `followups` موزّعة على آخر 10 أيام
- طُبِّقت التغييرات ذاتها حيًا عبر `mariadb` على قاعدة `project_demo_db` الحيّة (تحديثات schema.sql/demo_seed.sql تجعلها قابلة لإعادة الإنتاج عند تشغيل `--reset-db` من جديد)

## أوامر التحقق المُستخدَمة

```bash
# إعادة تشغيل كامل النظام + فحص صحة من 31 نقطة
bash run/run_all.sh

# فحص صياغة PHP لأي ملف
.runtime/env/bin/php -c .runtime/php-ext/php.ini -l <file>

# تسجيل دخول حي كمدير (للزحف الموثَّق)
CJ=/tmp/cookies.txt
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" -O /dev/null \
  "http://127.0.0.1:8000/beaa/admin/account/login.php"
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
  --post-data="username=admin.demo@example.com&password=AdminDemo@123" \
  -O /dev/null "http://127.0.0.1:8000/beaa/admin/account/login.php"

# تسجيل دخول حي كموظف (اختبار سير عمل الطاولة)
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" -O /dev/null \
  "http://127.0.0.1:8000/beaa/counter/?id=1"
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
  --post-data="username=operator.demo@example.com&password=OperatorDemo@123&autologin=false&counter=1" \
  -O - "http://127.0.0.1:8000/beaa/api/counter/?op=11"
```

نُفِّذ زحف كامل للموقع للتحقق من الأصول والأخطاء (39 صفحة، يستخرج كل
`href`/`src`، يزيل تعليقات HTML، يفحص حالة HTTP لكل أصل فريد، ويبحث في نص كل
صفحة عن `Fatal error|Parse error|Uncaught|Warning|Notice|TypeError`) كسكربت
Python مؤقت في كل تكرار بدلاً من حفظه كملف ضمن المستودع — راجع نص المحادثة
للحصول على السكربت الدقيق إن رغبت بإعادة تشغيله؛ وهو مرشّح معقول لتحويله إلى
`run/crawl_check.py` إذا استمر العمل على هذا الإصلاح.

## الحالة النهائية المُتحقَّق منها (نهاية هذه الجلسة)
- `run/run_all.sh`: نجاح 31 من 31، صفر إخفاقات
- زحف كامل على الصفحات الـ39 المُكتشَفة (الإدارة + شاشة الموظف + الشاشة الكبيرة + النداء الجماعي + التقييم + الصوت + الشاشة): **صفر أصول معطوبة، صفر أخطاء PHP ظاهرة**
- تم التحقق من سير عمل الموظف الكامل من طرف إلى طرف (تسجيل دخول ← نداء ← إعادة نداء ← انتظار مؤجل ← تحويل بين الطاولات ← إغلاق ← إصدار بطاقة متابعة) مقابل قاعدة البيانات الحيّة
- نُظِّفت بيانات الاختبار/التجربة بعد كل جولة تحقق؛ الحالة النهائية لقاعدة البيانات: 14 تذكرة (events)، 11 بطاقة متابعة، صفر تحويلات مفتوحة، 3 صفوف تقييم، وكلا الطاولتين مُغلقتان/خاملتان
