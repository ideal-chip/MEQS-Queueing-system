#!/usr/bin/env bash
# =============================================================================
#  MEQS / iDEAL-Q  —  المشغّل الشامل (يُفوّض الآن إلى run/run_all.sh)
#
#  أصبح run/run_all.sh هو السكربت المرجعي الوحيد: يمسح الذاكرة المؤقتة، يتحقق
#  من تشغيل Oracle MySQL 8.4 عبر systemd (وليس MariaDB المحمولة القديمة)،
#  يشغّل خادم PHP، وينفّذ فحوصاً شاملة. أُبقي هذا الملف كواجهة رفيعة فقط حتى
#  تستمر أي استدعاءات قديمة لـ scripts/run_meqs_full.sh بالعمل.
#
#  This now just delegates to run/run_all.sh (the canonical launcher).
#  Kept as a thin wrapper for backward compatibility with old invocations.
# =============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
exec bash "$PROJECT_DIR/run/run_all.sh" "$@"
