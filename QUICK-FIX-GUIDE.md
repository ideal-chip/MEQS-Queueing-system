# 🚨 QUICK FIX GUIDE - Common Issues

## Problem #1: Links Don't Work / 404 Error

### ❌ **WRONG URL:**
```
http://192.168.1.72:8000/admin/flow.php
```

### ✅ **CORRECT URL:**
```
http://192.168.1.72:8000/beaa/admin/flow.php
                         ^^^^^ Always include /beaa/
```

### **Why?**
The application is installed in the `/beaa/` directory. All admin URLs must include `/beaa/admin/`.

---

## Problem #2: Images Not Showing

### **Solution:**
All images are now SVG files and should load automatically.

**Test image loading:**
```
http://192.168.1.72:8000/beaa/files/logos/systemlogo-md.svg
http://192.168.1.72:8000/files/logos/systemlogo.svg
```

### **If images still don't show:**
1. Hard refresh: `Ctrl+F5` (Windows) or `Cmd+Shift+R` (Mac)
2. Clear browser cache
3. Try different browser

---

## Problem #3: Pages Appear Empty

### **Reasons:**
1. **Not logged in** - Must login first
2. **Wrong URL** - Must include `/beaa/` in path
3. **Session expired** - Login again
4. **JavaScript disabled** - Enable JavaScript

### **Solution:**
1. Go to: `http://192.168.1.72:8000/beaa/admin/account/login.php`
2. Login with: `admin.demo@example.com` / `AdminDemo@123`
3. Use navigation menu (don't manually type URLs)

---

## Problem #4: "languageName" Showing in Menu

### **Reason:**
Language texts not loaded properly.

### **Solution:**
This is normal for some missing translations. The menu still works.

---

## Problem #5: Can't Click Dropdowns

### **Solution:**
Make sure JavaScript is loaded:
1. Open browser console (F12)
2. Check for JavaScript errors
3. Hard refresh the page (Ctrl+F5)

---

## 🎯 **BEST WAY TO USE THE SYSTEM:**

### **Step 1: Start Here**
```
http://192.168.1.72:8000/
```

### **Step 2: Click "Admin" Button**
This takes you to login page automatically.

### **Step 3: Login**
```
Email:    admin.demo@example.com
Password: AdminDemo@123
```

### **Step 4: Use Navigation Menu**
Don't manually type URLs - use the dropdown menus:
- Click "Reports & Operations" → Select "Flow Report"
- Click "System Settings" → Select "Categories"
- Click "Users/Clerks" → Select "Users"

---

## 📋 **Quick Reference - Most Used Pages:**

| Page | Correct URL |
|------|-------------|
| **Home** | http://192.168.1.72:8000/ |
| **Admin Login** | http://192.168.1.72:8000/beaa/admin/account/login.php |
| **Dashboard** | http://192.168.1.72:8000/beaa/admin/ |
| **Flow Report** | http://192.168.1.72:8000/beaa/admin/flow.php |
| **Categories** | http://192.168.1.72:8000/beaa/admin/categories.php |
| **Counters** | http://192.168.1.72:8000/beaa/admin/counters.php |
| **Users** | http://192.168.1.72:8000/beaa/admin/users.php |
| **Clerks** | http://192.168.1.72:8000/beaa/admin/clerks.php |

---

## 🔧 **If Nothing Works:**

### **Restart Server:**
```bash
cd /home/idealchip_server/meqs
bash scripts/stop_local_runtime.sh
bash scripts/start_local_runtime.sh
```

### **Clear Browser Data:**
1. Chrome: Settings → Privacy → Clear browsing data
2. Safari: Preferences → Privacy → Manage Website Data → Remove All
3. Firefox: Settings → Privacy → Clear Data

### **Try Different Browser:**
- Chrome
- Firefox
- Safari
- Edge

---

## ✅ **Verify Everything Works:**

### **Test #1: Homepage**
```bash
curl -I http://192.168.1.72:8000/
# Should return: HTTP/1.1 200 OK
```

### **Test #2: Admin Login**
```bash
curl -I http://192.168.1.72:8000/beaa/admin/account/login.php
# Should return: HTTP/1.1 200 OK
```

### **Test #3: Logo Image**
```bash
curl -I http://192.168.1.72:8000/beaa/files/logos/systemlogo-md.svg
# Should return: HTTP/1.1 200 OK
# Content-Type: image/svg+xml
```

### **Test #4: JavaScript**
```bash
curl -I http://192.168.1.72:8000/beaa/js/jquery-3.1.1.min.js
# Should return: HTTP/1.1 200 OK
```

### **Test #5: CSS**
```bash
curl -I http://192.168.1.72:8000/beaa/css/common.css
# Should return: HTTP/1.1 200 OK
```

---

## 📱 **Mobile Access:**

1. Connect phone to same WiFi
2. Open Safari/Chrome
3. Go to: `http://192.168.1.72:8000/`
4. Click "Admin"
5. Login
6. Use navigation menus

---

## 🆘 **Still Having Problems?**

### **Check Server Status:**
```bash
ps aux | grep -E 'php|mariadb' | grep -v grep
```

Should show:
- PHP server running on port 8000
- MariaDB running on port 3307

### **Check Logs:**
```bash
tail -50 /home/idealchip_server/meqs/.runtime/logs/php-server.log
tail -50 /home/idealchip_server/meqs/.runtime/logs/mariadb.err
```

---

## 📚 **Full Documentation:**

- `CORRECT-URLS.md` - All correct URLs
- `ACCESS-INFO.txt` - Quick access info
- `FINAL-FIX-SUMMARY.md` - Complete fix summary
- `docs-ar/` - Arabic documentation (17 files)

---

**Last Updated:** May 17, 2026 12:20 PM

**Remember: Always use `/beaa/` in URLs!**
