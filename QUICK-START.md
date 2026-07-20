# 🚀 Quick Start Guide - iDEAL-Q System

## 📍 Server IP: 192.168.1.72:8000

---

## 🎯 All Portal URLs

### 1. Admin Portal
```
http://192.168.1.72:8000/beaa/admin/account/login.php

Username: admin.demo@example.com
Password: AdminDemo@123
```

### 2. Counter Portal
```
http://192.168.1.72:8000/beaa/counter/

Username: operator.demo@example.com
Password: OperatorDemo@123
Select: Counter 1 or Counter 2
```

### 3. Big Display (No login required)
```
http://192.168.1.72:8000/beaa/bigdisplay/?id=1
```

### 4. Regular Display (No login required)
```
http://192.168.1.72:8000/beaa/display/?id=1
```

### 5. Kiosk API (No login required)
```
http://192.168.1.72:8000/beaa/api/kiosk/get.php?id=1
http://192.168.1.72:8000/beaa/api/kiosk/set.php?id=1&cat=1&lang=ar
```

### 6. Feedback Portal (No login required)
```
http://192.168.1.72:8000/beaa/feedback/
```

### 7. File Browser (Admin only)
```
http://192.168.1.72:8000/beaa/admin/file-browser.php
(Login as admin first)
```

---

## 👥 All User Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin.demo@example.com | AdminDemo@123 |
| Operator | operator.demo@example.com | OperatorDemo@123 |
| Viewer | viewer.demo@example.com | ViewerDemo@123 |

---

## 🎬 Quick Testing

### Test 1: Admin Login
1. Open: http://192.168.1.72:8000/beaa/admin/account/login.php
2. Enter: admin.demo@example.com / AdminDemo@123
3. Click Login
4. ✅ You should see the admin dashboard

### Test 2: Counter Login
1. Open: http://192.168.1.72:8000/beaa/counter/
2. Select: Counter 1
3. Enter: operator.demo@example.com / OperatorDemo@123
4. Click Login
5. ✅ You should see the counter interface

### Test 3: Big Display
1. Open: http://192.168.1.72:8000/beaa/bigdisplay/?id=1
2. ✅ You should see the display screen (may be empty if no tickets)

### Test 4: Issue Ticket via API
```bash
curl "http://192.168.1.72:8000/beaa/api/kiosk/set.php?id=1&cat=1&lang=ar"
```
✅ This will create a new ticket

---

## 🔄 Server Control

### Start Server
```bash
cd /home/idealchip_server/meqs
bash scripts/start_local_runtime.sh
```

### Stop Server
```bash
cd /home/idealchip_server/meqs
bash scripts/stop_local_runtime.sh
```

### Check Status
```bash
ps aux | grep -E 'php|mariadb' | grep -v grep
netstat -tuln | grep :8000
```

---

## 📖 Full Documentation

See: `/home/idealchip_server/meqs/docs-ar/NETWORK-ACCESS-GUIDE.md`

---

**Server is ready! Access from any device on the network. 🎉**
