# 07 - Runbook الواجهة

لا يوجد frontend مستقل مثل React/Vue. الواجهة مدمجة داخل PHP.

## روابط الاختبار
- Admin: `http://127.0.0.1:8000/beaa/admin/account/login.php`
- Counter: `http://127.0.0.1:8000/beaa/counter/`
- Big display: `http://127.0.0.1:8000/beaa/bigdisplay/?id=1`
- Bulk call: `http://127.0.0.1:8000/beaa/bulkcall/`
- Feedback: `http://127.0.0.1:8000/beaa/feedback/`

## البناء production
لا توجد build step. يتم نشر ملفات PHP/CSS/JS كما هي على Apache/Nginx مع PHP-FPM.
