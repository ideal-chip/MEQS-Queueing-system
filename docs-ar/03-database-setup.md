# 03 - إعداد قاعدة بيانات MySQL

## الحالة الحالية
لم أتمكن من إنشاء قاعدة البيانات فعلياً على هذه البيئة لأن أوامر `mysql` غير مثبتة. تم تجهيز ملفات SQL قابلة للتنفيذ:
- `database/create_demo_database.sql`
- `database/schema.sql`
- `database/demo_seed.sql`

## أوامر الإعداد
```bash
sudo apt update
sudo apt install -y mysql-server php php-mysqli

mysql -uroot -p < database/create_demo_database.sql
mysql -uroot -p project_demo_db < database/schema.sql
mysql -uroot -p project_demo_db < database/demo_seed.sql
```

## قاعدة الديمو
- Database: `project_demo_db`
- User: `project_demo_user`
- Password: `ProjectDemo@12345`

## ملاحظات
لا توجد migrations رسمية. لذلك تم إنشاء schema مستنتج من استخدام الجداول داخل الكود. قبل الإنتاج يجب مقارنته مع dump حقيقي من قاعدة الإنتاج بدون بيانات حساسة.
