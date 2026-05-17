# 06 - Runbook تشغيل Backend

## المتطلبات
```bash
sudo apt install -y php php-mysqli mysql-server
```

## التشغيل المحلي
```bash
cp .env.example .env
php -S 127.0.0.1:8000
```

## المنافذ
- PHP local: `8000`
- MySQL: `3306`

## التحقق
```bash
BASE_URL=http://127.0.0.1:8000 tests/smoke.sh
```

## مشاكل شائعة
- `php: command not found`: ثبت PHP.
- `mysql: command not found`: ثبت MySQL client/server.
- خطأ اتصال DB: راجع `.env` وتأكد من تنفيذ `database/create_demo_database.sql`.
- صفحة بيضاء: فعّل عرض الأخطاء مؤقتاً في بيئة local فقط.
