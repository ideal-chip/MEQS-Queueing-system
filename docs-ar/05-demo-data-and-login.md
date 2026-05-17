# 05 - بيانات الديمو وتسجيل الدخول

## تشغيل بيانات الديمو
```bash
mysql -uroot -p < database/create_demo_database.sql
mysql -uroot -p project_demo_db < database/schema.sql
mysql -uroot -p project_demo_db < database/demo_seed.sql
```

## حسابات الديمو
### Admin
- الرابط: `/beaa/admin/account/login.php`
- username/email: `admin.demo@example.com`
- password: `AdminDemo@123`
- الصلاحيات: كاملة `255`

### Operator
- الرابط: `/beaa/counter/`
- username/email: `operator.demo@example.com`
- password: `OperatorDemo@123`
- مخصص لتسجيل دخول موظف كاونتر.

### Viewer
- الرابط: `/beaa/admin/account/login.php`
- username/email: `viewer.demo@example.com`
- password: `ViewerDemo@123`
- صلاحيات قراءة/تقارير محدودة.

## بيانات أعمال تجريبية
تمت إضافة صالة رئيسية، كاونترين، شاشة عرض، شاشة كبيرة، كشك، فئتي خدمة، تذاكر تجريبية، تقييم، وبطاقة متابعة.
