# 12 - توصية Web File Access

لم أجد file manager عام داخل المشروع. لا أنصح بإضافة مدير ملفات عام بدون تصميم أمني.

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
