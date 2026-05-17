# 11 - دليل النشر

## حزم السيرفر
```bash
sudo apt update
sudo apt install -y nginx php-fpm php-mysqli mysql-server unzip
```

## إعداد قاعدة البيانات
```bash
mysql -uroot -p < database/create_demo_database.sql
mysql -uroot -p project_demo_db < database/schema.sql
mysql -uroot -p project_demo_db < database/demo_seed.sql
```

## إعداد production `.env`
استخدم قيماً إنتاجية حقيقية خارج Git:
```env
APP_ENV=production
APP_BASE_URL=https://example.com
DB_HOST=localhost
DB_NAME=...
DB_USER=...
DB_PASSWORD=...
```

## Nginx مثال
```nginx
server {
  listen 80;
  server_name example.com;
  root /var/www/meqs;
  index index.php index.html;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php8.1-fpm.sock;
  }

  location ~ /\. {
    deny all;
  }
}
```

## النسخ الاحتياطي
```bash
mysqldump -u backup_user -p --single-transaction project_demo_db > backup-$(date +%F).sql
tar -czf files-backup-$(date +%F).tar.gz beaa/files
```

## rollback
- احتفظ بإصدار سابق من مجلد التطبيق.
- احتفظ بآخر dump قاعدة بيانات قبل أي migration.
- أعد توجيه symlink النشر للإصدار السابق ثم أعد تحميل Nginx/PHP-FPM.

## معلومات ناقصة
لم يتم توفير بيانات السيرفر الحالي أو الدومين/الـ IP، لذلك لم يتم تنفيذ نشر فعلي.
