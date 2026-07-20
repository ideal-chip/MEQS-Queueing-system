#!/usr/bin/env bash
# =============================================================================
#  MEQS / iDEAL-Q  —  المشغّل الشامل مع الفحص الذاتي والإصلاح التلقائي
#  All-in-one launcher: starts the BACKEND (MariaDB + database) and the
#  FRONTEND (PHP web app), auto-repairs the database if it is down or missing,
#  runs full health checks, and prints an error report.
#
#  الاستخدام:  bash scripts/run_meqs_full.sh
#  خيارات:
#     --reset-db     إعادة بناء قاعدة البيانات من الصفر (schema + seed)
#     --host=IP      تغيير عنوان الربط (الافتراضي 192.168.1.41)
#     --port=N       تغيير منفذ الويب (الافتراضي 8000)
#     --no-restart   لا تُعِد تشغيل خادم PHP إن كان يعمل أصلاً
# =============================================================================
set -u

# ---------------------------------------------------------------- config ----
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="$PROJECT_DIR/.runtime/env/bin/php"
PHP_INI="$PROJECT_DIR/.runtime/php-ext/php.ini"
MYSQLD_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadbd"
MYSQL_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadb"
RUN_DIR="$PROJECT_DIR/.runtime/run"
LOG_DIR="$PROJECT_DIR/.runtime/logs"
MYSQL_DATA="$PROJECT_DIR/.runtime/mysql-data"

HTTP_HOST="192.168.1.41"      # العنوان المطلوب
HTTP_PORT="8000"
DB_PORT="3307"
DB_NAME="project_demo_db"
DB_USER="project_demo_user"
DB_PASS="ProjectDemo@12345"

RESET_DB=0
DO_RESTART=1

for arg in "$@"; do
  case "$arg" in
    --reset-db)    RESET_DB=1 ;;
    --no-restart)  DO_RESTART=0 ;;
    --host=*)      HTTP_HOST="${arg#*=}" ;;
    --port=*)      HTTP_PORT="${arg#*=}" ;;
    *) echo "خيار غير معروف: $arg" ;;
  esac
done

mkdir -p "$RUN_DIR" "$LOG_DIR" "$RUN_DIR/sessions"
REPORT="$LOG_DIR/health-report-$(date +%Y%m%d-%H%M%S).txt"

# ---------------------------------------------------------------- report ----
if [ -t 1 ]; then C_OK=$'\e[32m'; C_WARN=$'\e[33m'; C_ERR=$'\e[31m'; C_HD=$'\e[1;36m'; C_0=$'\e[0m'
else C_OK=; C_WARN=; C_ERR=; C_HD=; C_0=; fi

PASS=0; WARN=0; FAIL=0
declare -a FAILURES=()

log()  { echo "$*" | tee -a "$REPORT" ; }
head_() { log ""; log "${C_HD}== $* ==${C_0}"; }
ok()   { PASS=$((PASS+1)); log "  ${C_OK}[نجاح]${C_0} $*"; }
warn() { WARN=$((WARN+1)); log "  ${C_WARN}[تحذير]${C_0} $*"; }
fail() { FAIL=$((FAIL+1)); FAILURES+=("$*"); log "  ${C_ERR}[خطأ]${C_0}  $*"; }

log "================================================================"
log "  MEQS / iDEAL-Q — تقرير التشغيل والفحص الشامل"
log "  الوقت: $(date '+%Y-%m-%d %H:%M:%S')"
log "  المشروع: $PROJECT_DIR"
log "  العنوان المستهدف: http://$HTTP_HOST:$HTTP_PORT"
log "  تقرير محفوظ في: $REPORT"
log "================================================================"

# ============================================================================
#  المرحلة 1: فحوص ما قبل التشغيل (Preflight)
# ============================================================================
head_ "المرحلة 1: فحوص ما قبل التشغيل"

if [ -x "$PHP_BIN" ] && "$PHP_BIN" -v >/dev/null 2>&1; then
  ok "مفسّر PHP يعمل ($("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null))"
else
  fail "مفسّر PHP غير موجود أو لا يعمل: $PHP_BIN"
  log ""; log "لا يمكن المتابعة بدون PHP. توقّف."; exit 1
fi

if [ -f "$PHP_INI" ]; then ok "ملف php.ini موجود"; else warn "php.ini غير موجود؛ ستُستخدم إعدادات افتراضية"; fi

EXT_OK=1
for ext in mysqli mbstring; do
  if "$PHP_BIN" -c "$PHP_INI" -r "exit(extension_loaded('$ext')?0:1);" 2>/dev/null; then
    ok "امتداد $ext محمّل"
  else
    fail "امتداد $ext غير محمّل (التطبيق يحتاجه)"; EXT_OK=0
  fi
done

for f in "$MYSQLD_BIN" "$MYSQL_BIN"; do
  [ -x "$f" ] && ok "أداة قاعدة البيانات موجودة: $(basename "$f")" || fail "أداة قاعدة البيانات مفقودة: $f"
done

# ============================================================================
#  المرحلة 2: الخلفية — قاعدة البيانات (تشغيل + إصلاح تلقائي)
# ============================================================================
head_ "المرحلة 2: الخلفية (Backend) — قاعدة البيانات MariaDB"

mysql_root() { "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -uroot "$@" 2>/dev/null; }

# 2.1 هل الخادم يعمل؟ وإلا شغّله
if pgrep -f "$MYSQLD_BIN.*$MYSQL_DATA" >/dev/null 2>&1; then
  ok "خادم MariaDB يعمل مسبقاً"
else
  if [ ! -d "$MYSQL_DATA/mysql" ]; then
    fail "مجلّد بيانات قاعدة البيانات مفقود/تالف: $MYSQL_DATA"
  else
    log "  … تشغيل خادم MariaDB على 127.0.0.1:$DB_PORT"
    "$MYSQLD_BIN" --no-defaults \
      --basedir="$PROJECT_DIR/.runtime/mariadb" \
      --datadir="$MYSQL_DATA" \
      --socket="$RUN_DIR/mysql.sock" \
      --pid-file="$RUN_DIR/mysql.pid" \
      --port="$DB_PORT" --bind-address=127.0.0.1 \
      --log-error="$LOG_DIR/mariadb.err" >/dev/null 2>&1 &
    echo $! > "$RUN_DIR/mariadb.wrapper.pid"
  fi
fi

# 2.2 انتظار قبول الاتصالات
DB_UP=0
for i in $(seq 1 30); do
  if mysql_root -e "SELECT 1" >/dev/null 2>&1; then DB_UP=1; break; fi
  sleep 1
done
if [ "$DB_UP" = 1 ]; then
  ok "قاعدة البيانات تقبل الاتصالات (المنفذ $DB_PORT)"
else
  fail "قاعدة البيانات لا تستجيب بعد الانتظار. آخر أسطر السجلّ:"
  tail -n 8 "$LOG_DIR/mariadb.err" 2>/dev/null | sed 's/^/        /' | tee -a "$REPORT"
fi

# 2.3 إعادة بناء اختيارية
if [ "$DB_UP" = 1 ] && [ "$RESET_DB" = 1 ]; then
  log "  … (--reset-db) إعادة بناء قاعدة البيانات من الصفر"
  mysql_root -e "DROP DATABASE IF EXISTS $DB_NAME;"
fi

# 2.4 التأكّد من وجود القاعدة والجداول، وإلا بناؤها
if [ "$DB_UP" = 1 ]; then
  HAS_DB=$(mysql_root -sN -e "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='$DB_NAME';" 2>/dev/null)
  HAS_TBL=$(mysql_root -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null)
  if [ "${HAS_DB:-0}" = "1" ] && [ "${HAS_TBL:-0}" -ge 20 ] 2>/dev/null; then
    ok "قاعدة البيانات '$DB_NAME' موجودة (${HAS_TBL} جدولاً)"
  else
    warn "قاعدة البيانات ناقصة/غير موجودة — سيجري بناؤها الآن (إصلاح تلقائي)"
    mysql_root < "$PROJECT_DIR/database/create_demo_database.sql" 2>>"$REPORT" \
      && ok "أُنشئت القاعدة والمستخدم والصلاحيات" || fail "فشل create_demo_database.sql"
    mysql_root "$DB_NAME" < "$PROJECT_DIR/database/schema.sql" 2>>"$REPORT" \
      && ok "طُبِّق المخطّط (schema.sql)" || fail "فشل schema.sql"
    mysql_root "$DB_NAME" < "$PROJECT_DIR/database/demo_seed.sql" 2>>"$REPORT" \
      && ok "حُمّلت البيانات التجريبية (demo_seed.sql)" || fail "فشل demo_seed.sql"
  fi

  # 2.5 التأكّد من مستخدم التطبيق (وإلا إعادة إنشائه)
  if "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
       -e "SELECT 1" >/dev/null 2>&1; then
    ok "مستخدم التطبيق '$DB_USER' يتّصل بنجاح"
  else
    warn "مستخدم التطبيق لا يتّصل — إعادة إنشاء المستخدم والصلاحيات"
    mysql_root < "$PROJECT_DIR/database/create_demo_database.sql" 2>>"$REPORT"
    "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
       -e "SELECT 1" >/dev/null 2>&1 \
       && ok "أُصلِح اتصال مستخدم التطبيق" || fail "ما زال مستخدم التطبيق لا يتّصل"
  fi

  # 2.6 فحص الاتصال عبر PHP mysqli (نفس مسار التطبيق الحقيقي)
  PHP_DB=$("$PHP_BIN" -c "$PHP_INI" -r '
    $m=@new mysqli("127.0.0.1","'"$DB_USER"'","'"$DB_PASS"'","'"$DB_NAME"'",'"$DB_PORT"');
    if($m->connect_errno){echo "ERR:".$m->connect_error;exit;}
    $r=$m->query("SELECT COUNT(*) c FROM users"); $x=$r?$r->fetch_assoc():["c"=>"?"];
    echo "OK:".$m->server_info.":users=".$x["c"];' 2>&1)
  case "$PHP_DB" in
    OK:*) ok "PHP↔MariaDB عبر mysqli: ${PHP_DB#OK:}" ;;
    *)    fail "PHP لا يستطيع الاتصال بقاعدة البيانات: $PHP_DB" ;;
  esac
fi

# ============================================================================
#  المرحلة 3: الواجهة — خادم PHP للويب على العنوان المطلوب
# ============================================================================
head_ "المرحلة 3: الواجهة (Frontend) — خادم الويب"

# 3.1 هل العنوان معيّن على الجهاز؟
# نربط دائماً على 0.0.0.0 (يستمع على كل الواجهات بما فيها العنوان المطلوب و127.0.0.1)،
# فيبقى http://192.168.1.41:8000 صالحاً وتبقى فحوص الصحّة عبر 127.0.0.1 موثوقة.
BIND_HOST="0.0.0.0"
if ip -o -4 addr show 2>/dev/null | grep -qw "$HTTP_HOST"; then
  ok "العنوان $HTTP_HOST معيّن على الجهاز (سيكون الرابط قابلاً للوصول)"
else
  warn "العنوان $HTTP_HOST غير معيّن حالياً على الجهاز — الخادم يستمع على كل الواجهات، لكن قد لا يُوصَل عبر هذا العنوان تحديداً حتى يُضبط IP الجهاز عليه"
fi

# 3.2 إيقاف أي خادم قديم على المنفذ (لإعادة الربط على العنوان الصحيح)
if [ "$DO_RESTART" = 1 ]; then
  OLD=$(pgrep -f "env/bin/php -c .* -S .*:$HTTP_PORT" 2>/dev/null)
  [ -n "$OLD" ] && { kill $OLD 2>/dev/null; sleep 1; log "  … أُوقِف خادم PHP سابق (PID: $OLD)"; }
fi

# 3.3 تشغيل الخادم
if pgrep -f "env/bin/php -c .* -S .*:$HTTP_PORT" >/dev/null 2>&1; then
  ok "خادم PHP يعمل مسبقاً (لم يُعَد تشغيله)"
else
  nohup "$PHP_BIN" -c "$PHP_INI" -S "$BIND_HOST:$HTTP_PORT" -t "$PROJECT_DIR" "$PROJECT_DIR/router.php" \
       > "$LOG_DIR/php-server.log" 2>&1 &
  echo $! > "$RUN_DIR/php-server.pid"
  disown 2>/dev/null || true
  log "  … شُغِّل خادم PHP (PID: $(cat "$RUN_DIR/php-server.pid"))"
fi

# 3.4 انتظار استجابة الخادم
WEB_UP=0
for i in $(seq 1 15); do
  if wget -q --timeout=12 --tries=1 -O /dev/null "http://127.0.0.1:$HTTP_PORT/" 2>/dev/null; then WEB_UP=1; break; fi
  sleep 1
done
[ "$WEB_UP" = 1 ] && ok "خادم الويب يستجيب على المنفذ $HTTP_PORT" \
                  || fail "خادم الويب لا يستجيب. راجع $LOG_DIR/php-server.log"

# ============================================================================
#  المرحلة 4: فحوص صحّة الواجهات (Health checks)
# ============================================================================
head_ "المرحلة 4: فحص الواجهات والبوابات"

BASE="http://127.0.0.1:$HTTP_PORT"
HDR="$RUN_DIR/.hc_hdr"; BODY="$RUN_DIR/.hc_body"

# check_url <path> <expected-code-regex> <label>
check_url() {
  local path="$1" expect="$2" label="$3"
  # timeout صارم: لا يتجاوز أي فحص 15 ثانية مهما كان حجم الصفحة أو بطؤها
  timeout 15 wget -q --timeout=10 --tries=1 -S -O "$BODY" "$BASE$path" 2>"$HDR"
  local code; code=$(grep -m1 'HTTP/' "$HDR" | awk '{print $2}')
  local err;  err=$(grep -ioE 'Fatal error|Parse error|Uncaught|call to undefined|mysqli_sql_exception' "$BODY" 2>/dev/null | head -1)
  if [ -n "$err" ]; then
    fail "$label — خطأ PHP في الصفحة: $err  ($path)"
  elif echo "${code:-000}" | grep -qE "$expect"; then
    ok "$label — HTTP ${code}  ($path)"
  else
    fail "$label — رمز غير متوقّع: HTTP ${code:-؟}  ($path)"
  fi
}

if [ "$WEB_UP" = 1 ]; then
  # الواجهات العامّة
  check_url "/"                                     '^(200)$'     "الصفحة الرئيسية"
  check_url "/beaa/admin/account/login.php"         '^(200)$'     "صفحة دخول الإدارة"
  check_url "/beaa/feedback/"                       '^(200)$'     "شاشة التقييم"
  check_url "/beaa/counter/?id=1"                   '^(200)$'     "طاولة الموظف"
  check_url "/beaa/display/?id=1"                   '^(200)$'     "شاشة الطاولة"
  check_url "/beaa/bigdisplay/?id=1"                '^(200)$'     "الشاشة الكبيرة"
  # واجهات API
  check_url "/beaa/api/kiosk/get.php?id=0&kiosk=1&language=ar"  '^(200)$'  "API قائمة خدمات الكشك"
  check_url "/beaa/api/checkupdate.php?display=1"   '^(200)$'     "API فحص التحديث"

  # فحص المصادقة من طرف إلى طرف (دخول المدير)
  CJ="$RUN_DIR/.hc_cookies"; rm -f "$CJ"
  timeout 15 wget -q --timeout=10 --tries=1 --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
       -O /dev/null "$BASE/beaa/admin/account/login.php" 2>/dev/null
  LOGIN_CODE=$(timeout 15 wget -q --timeout=12 --tries=1 --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
       --max-redirect=0 \
       --post-data="username=admin.demo@example.com&password=AdminDemo@123" \
       -S -O /dev/null "$BASE/beaa/admin/account/login.php" 2>&1 | grep -m1 'HTTP/' | awk '{print $2}')
  if [ "${LOGIN_CODE:-}" = "302" ]; then
    timeout 15 wget -q --timeout=10 --tries=1 --load-cookies "$CJ" -O "$BODY" "$BASE/beaa/admin/index.php" 2>/dev/null
    if grep -qi 'mainPage\|iDEAL' "$BODY" 2>/dev/null && ! grep -qiE 'Fatal|Uncaught' "$BODY"; then
      ok "تسجيل دخول المدير + لوحة التحكم يعملان (مصادقة كاملة)"
    else
      fail "دخول المدير نجح لكن لوحة التحكم بها مشكلة"
    fi
  else
    fail "تسجيل دخول المدير لم يُعِد التوجيه المتوقّع (HTTP ${LOGIN_CODE:-؟})"
  fi

  # فحص كتابة فعلي: إصدار تذكرة (يؤكّد أن الخلفية تكتب في القاعدة)
  if [ "$DB_UP" = 1 ]; then
    BEFORE=$(mysql_root -sN -e "SELECT COUNT(*) FROM $DB_NAME.events;" 2>/dev/null)
    TID=$(timeout 15 wget -q --timeout=12 --tries=1 -O - "$BASE/beaa/api/kiosk/set.php?category=1&kiosk=1&lang=ar" 2>/dev/null)
    AFTER=$(mysql_root -sN -e "SELECT COUNT(*) FROM $DB_NAME.events;" 2>/dev/null)
    if [ "${AFTER:-0}" -gt "${BEFORE:-0}" ] 2>/dev/null; then
      ok "إصدار تذكرة يعمل (events: $BEFORE → $AFTER، رقم الحدث: $TID)"
    else
      warn "إصدار التذكرة لم يزد العدّاد (قد يكون بسبب بيانات مفقودة) — الاستجابة: $TID"
    fi
  fi
  rm -f "$HDR" "$BODY" "$CJ"
fi

# ============================================================================
#  التقرير النهائي
# ============================================================================
head_ "التقرير النهائي"
log "  ${C_OK}نجاح: $PASS${C_0}   ${C_WARN}تحذير: $WARN${C_0}   ${C_ERR}أخطاء: $FAIL${C_0}"
if [ "$FAIL" -gt 0 ]; then
  log ""
  log "  ${C_ERR}قائمة الأخطاء:${C_0}"
  for e in "${FAILURES[@]}"; do log "   - $e"; done
fi

log ""
log "----------------------------------------------------------------"
if [ "$FAIL" -eq 0 ]; then
  log "  ${C_OK}✔ النظام يعمل بالكامل${C_0}"
else
  log "  ${C_ERR}✖ النظام يعمل مع وجود مشاكل (راجع الأخطاء أعلاه)${C_0}"
fi
log ""
log "  الروابط:"
log "    الرئيسية    : http://$HTTP_HOST:$HTTP_PORT/"
log "    دخول الإدارة : http://$HTTP_HOST:$HTTP_PORT/beaa/admin/account/login.php"
log "    طاولة الموظف : http://$HTTP_HOST:$HTTP_PORT/beaa/counter/?id=1"
log "    التقييم     : http://$HTTP_HOST:$HTTP_PORT/beaa/feedback/"
log "    الشاشة الكبيرة: http://$HTTP_HOST:$HTTP_PORT/beaa/bigdisplay/?id=1"
log "    شاشة الطاولة : http://$HTTP_HOST:$HTTP_PORT/beaa/display/?id=1"
log ""
log "  دخول المدير : admin.demo@example.com / AdminDemo@123"
log "  الإيقاف     : bash scripts/stop_meqs.sh"
log "  هذا التقرير : $REPORT"
log "----------------------------------------------------------------"

[ "$FAIL" -eq 0 ] && exit 0 || exit 1
