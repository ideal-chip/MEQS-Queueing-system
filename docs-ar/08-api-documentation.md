# 08 - توثيق API

Base URL محلي: `http://127.0.0.1:8000`

| Method | URL | الغرض | Auth |
|---|---|---|---|
| GET | `/beaa/api/checkupdate.php?type=kiosk&id=1` | فحص تحديث الكشك | لا |
| GET | `/beaa/api/checkupdate.php?type=bigdisplay&id=1` | فحص تحديث الشاشة الكبيرة | لا |
| GET | `/beaa/api/checkupdate.php?type=display&id=1` | فحص تحديث شاشة | لا |
| GET | `/beaa/api/update.php?type=kiosk&id=1` | تعليم الكشك كمحدث | جلسة admin غالباً |
| GET | `/beaa/api/update.php?type=bigdisplay&id=1` | تعليم الشاشة الكبيرة كمحدثة | جلسة admin غالباً |
| GET | `/beaa/api/kiosk/get.php?id=1` | جلب إعدادات الكشك | لا |
| GET | `/beaa/api/kiosk/set.php?id=1&cat=1&lang=ar` | إصدار تذكرة | لا |
| GET | `/beaa/api/kiosk/set-bulk.php?id=1&cat=1&qty=3&lang=ar` | إصدار عدة تذاكر | لا |
| GET | `/beaa/api/kiosk/lastprinted.php?id=1` | آخر تذكرة مطبوعة | لا |
| POST | `/beaa/api/counter/index.php` | عمليات الكاونتر حسب `type` | جلسة clerk لبعض العمليات |
| GET | `/beaa/api/bigdisplay/latest.php?id=1&max=5` | آخر النداءات | لا |
| GET | `/beaa/api/bigdisplay/latestwating.php?id=1&max=5` | آخر النداءات والانتظار | لا |
| GET | `/beaa/api/bigdisplay/countercalls.php?id=1&max=5` | نداءات كاونتر معين | لا |
| GET | `/beaa/api/bigdisplay/bulk.php?id=1&type=tickets` | بيانات النداء الجماعي | لا |
| GET | `/beaa/api/display/index.php?id=1` | بيانات شاشة العرض | لا |
| POST | `/beaa/api/feedback/index.php` | إرسال تقييم | لا |

## مثال تسجيل دخول الكاونتر
```bash
curl -X POST http://127.0.0.1:8000/beaa/api/counter/index.php \
  -d 'type=11&username=operator.demo@example.com&password=OperatorDemo@123&counter=1&autologin=false'
```

استجابة متوقعة: `1` عند النجاح، `3` عند كلمة مرور خاطئة، `0` عند خطأ عام.

## أخطاء شائعة
- `0`: فشل عام أو query غير ناجح.
- `OLD`: التذكرة من يوم سابق.
- HTTP 500: غالباً خطأ اتصال DB أو جدول ناقص.
