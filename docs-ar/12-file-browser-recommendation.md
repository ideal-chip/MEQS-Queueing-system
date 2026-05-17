# 12 - توصية Web File Access

لم أجد file manager عام داخل المشروع. بناءً على طلب التسليم تمت إضافة متصفح ملفات آمن داخل لوحة الإدارة:

```text
beaa/admin/file-browser.php
```

الرابط بعد تسجيل دخول admin:

```text
/beaa/admin/file-browser.php
```

المتصفح read-only، محصور داخل جذر المشروع، ويمنع الوصول إلى ملفات مثل `.env` و `.git` و `.ssh`.

## التصميم الآمن المقترح
- Admin-only authenticated route.
- Read-only افتراضياً.
- root directory محدد مثل `beaa/files`.
- منع traversal مثل `../`.
- audit logs لكل قراءة/رفع/حذف.
- upload limits للحجم والامتدادات.
- allowed extensions فقط: `jpg`, `png`, `pdf`, `mp3` حسب الحاجة.
- منع تنفيذ PHP داخل مجلد uploads.
- فحص MIME الحقيقي وليس الامتداد فقط.
