<?php
/**
 * System Verification Script
 * Tests all components and generates a report
 */

require_once __DIR__ . '/beaa/config.php';
require_once __DIR__ . '/beaa/router.php';
require_once __DIR__ . '/beaa/api/db.php';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فحص النظام - System Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
        }
        .test-group {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .test-group h2 {
            color: #34495e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: white;
            border-radius: 5px;
            border-right: 5px solid #ddd;
        }
        .test-item.success {
            border-right-color: #28a745;
        }
        .test-item.fail {
            border-right-color: #dc3545;
        }
        .test-item.warning {
            border-right-color: #ffc107;
        }
        .test-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .test-result {
            font-family: 'Courier New', monospace;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .test-result.pass {
            background: #d4edda;
            color: #155724;
        }
        .test-result.fail {
            background: #f8d7da;
            color: #721c24;
        }
        .test-result.warn {
            background: #fff3cd;
            color: #856404;
        }
        .summary {
            background: #e8f4f8;
            border: 3px solid #3498db;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        .summary h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .stat {
            display: inline-block;
            margin: 10px 20px;
            font-size: 24px;
        }
        .stat-value {
            font-weight: bold;
            font-size: 36px;
        }
        .stat-pass { color: #28a745; }
        .stat-fail { color: #dc3545; }
        .stat-warn { color: #ffc107; }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            color: #e83e8c;
            font-family: 'Courier New', monospace;
        }
        .details {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 فحص النظام الشامل</h1>
        <h1>Comprehensive System Verification</h1>
        
        <?php
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;
        $warningTests = 0;
        
        function testItem($label, $condition, $successMsg, $failMsg, $details = '') {
            global $totalTests, $passedTests, $failedTests;
            $totalTests++;
            
            $status = $condition ? 'success' : 'fail';
            $resultClass = $condition ? 'pass' : 'fail';
            $resultText = $condition ? '✅ ' . $successMsg : '❌ ' . $failMsg;
            
            if ($condition) {
                $passedTests++;
            } else {
                $failedTests++;
            }
            
            echo "<div class='test-item $status'>";
            echo "<div>";
            echo "<div class='test-label'>$label</div>";
            if ($details) {
                echo "<div class='details'>$details</div>";
            }
            echo "</div>";
            echo "<div class='test-result $resultClass'>$resultText</div>";
            echo "</div>";
        }
        
        function testWarning($label, $condition, $warnMsg, $details = '') {
            global $totalTests, $warningTests;
            $totalTests++;
            $warningTests++;
            
            echo "<div class='test-item warning'>";
            echo "<div>";
            echo "<div class='test-label'>$label</div>";
            if ($details) {
                echo "<div class='details'>$details</div>";
            }
            echo "</div>";
            echo "<div class='test-result warn'>⚠️ $warnMsg</div>";
            echo "</div>";
        }
        ?>
        
        <!-- Configuration Tests -->
        <div class="test-group">
            <h2>⚙️ اختبارات الإعدادات / Configuration Tests</h2>
            
            <?php
            testItem(
                'ملف الإعدادات / Config File',
                defined('BASE_PATH'),
                'موجود',
                'غير موجود',
                'beaa/config.php'
            );
            
            testItem(
                'المسار الأساسي / Base Path',
                BASE_PATH === '/beaa',
                BASE_PATH,
                'مسار خاطئ',
                'Expected: /beaa'
            );
            
            testItem(
                'مسار الأدمن / Admin Path',
                ADMIN_BASE_PATH === '/beaa/admin',
                ADMIN_BASE_PATH,
                'مسار خاطئ'
            );
            
            testItem(
                'مسار API',
                API_BASE_PATH === '/beaa/api',
                API_BASE_PATH,
                'مسار خاطئ'
            );
            
            testItem(
                'مسار الملفات / Files Path',
                FILES_PATH === '/beaa/files',
                FILES_PATH,
                'مسار خاطئ'
            );
            
            testItem(
                'دالة url() / url() Function',
                function_exists('url'),
                'متوفرة',
                'غير موجودة'
            );
            
            testItem(
                'دالة adminUrl()',
                function_exists('adminUrl'),
                'متوفرة',
                'غير موجودة'
            );
            ?>
        </div>
        
        <!-- Router Tests -->
        <div class="test-group">
            <h2>🔀 اختبارات الروتر / Router Tests</h2>
            
            <?php
            testItem(
                'ملف الروتر / Router File',
                file_exists(__DIR__ . '/beaa/router.php'),
                'موجود',
                'غير موجود',
                'beaa/router.php'
            );
            
            testItem(
                'كلاس Router',
                class_exists('Router'),
                'معرّف',
                'غير معرّف'
            );
            
            $testUrl = url('api/test.php');
            testItem(
                'اختبار دالة url()',
                $testUrl === '/beaa/api/test.php',
                $testUrl,
                'نتيجة خاطئة'
            );
            
            $testAdminUrl = adminUrl('categories.php');
            testItem(
                'اختبار دالة adminUrl()',
                $testAdminUrl === '/beaa/admin/categories.php',
                $testAdminUrl,
                'نتيجة خاطئة'
            );
            ?>
        </div>
        
        <!-- Database Tests -->
        <div class="test-group">
            <h2>💾 اختبارات قاعدة البيانات / Database Tests</h2>
            
            <?php
            testItem(
                'ملف الاتصال / DB Connection File',
                file_exists(__DIR__ . '/beaa/api/db.php'),
                'موجود',
                'غير موجود',
                'beaa/api/db.php'
            );
            
            $dbConnected = false;
            try {
                $testQuery = getValue("SELECT 1");
                $dbConnected = ($testQuery == 1);
            } catch (Exception $e) {
                $dbConnected = false;
            }
            
            testItem(
                'اتصال قاعدة البيانات / DB Connection',
                $dbConnected,
                'متصل',
                'غير متصل'
            );
            
            if ($dbConnected) {
                $tablesCount = getValue("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'project_demo_db'");
                testItem(
                    'عدد الجداول / Tables Count',
                    $tablesCount > 0,
                    "$tablesCount جدول",
                    'لا توجد جداول',
                    'Expected: ~29 tables'
                );
                
                $usersCount = getValue("SELECT COUNT(*) FROM users");
                testItem(
                    'بيانات المستخدمين / Users Data',
                    $usersCount > 0,
                    "$usersCount مستخدم",
                    'لا توجد بيانات'
                );
                
                $categoriesCount = getValue("SELECT COUNT(*) FROM categories");
                testItem(
                    'بيانات التصنيفات / Categories Data',
                    $categoriesCount > 0,
                    "$categoriesCount تصنيف",
                    'لا توجد بيانات'
                );
            }
            ?>
        </div>
        
        <!-- Files Tests -->
        <div class="test-group">
            <h2>📁 اختبارات الملفات / Files Tests</h2>
            
            <?php
            $cssFiles = [
                'beaa/css/paper.bootstrap.min.css',
                'beaa/css/font-awesome.min.css',
                'beaa/css/common.css',
                'beaa/css/admin.css'
            ];
            
            foreach ($cssFiles as $file) {
                $exists = file_exists(__DIR__ . '/' . $file);
                $size = $exists ? filesize(__DIR__ . '/' . $file) : 0;
                testItem(
                    basename($file),
                    $exists && $size > 0,
                    'موجود (' . number_format($size) . ' bytes)',
                    'غير موجود أو فارغ',
                    $file
                );
            }
            
            $jsFiles = [
                'beaa/js/jquery-3.1.1.min.js',
                'beaa/js/bootstrap.min.js',
                'beaa/js/common.js'
            ];
            
            foreach ($jsFiles as $file) {
                $exists = file_exists(__DIR__ . '/' . $file);
                $size = $exists ? filesize(__DIR__ . '/' . $file) : 0;
                testItem(
                    basename($file),
                    $exists && $size > 0,
                    'موجود (' . number_format($size) . ' bytes)',
                    'غير موجود أو فارغ',
                    $file
                );
            }
            
            $logoFiles = [
                'beaa/files/logos/systemlogo-md.svg',
                'beaa/files/logos/ideal-q-small.svg',
                'files/logos/systemlogo.svg'
            ];
            
            foreach ($logoFiles as $file) {
                $exists = file_exists(__DIR__ . '/' . $file);
                testItem(
                    basename($file),
                    $exists,
                    'موجود',
                    'غير موجود',
                    $file
                );
            }
            ?>
        </div>
        
        <!-- Admin Pages Tests -->
        <div class="test-group">
            <h2>📄 اختبارات صفحات الإدارة / Admin Pages Tests</h2>
            
            <?php
            $adminPages = [
                'index.php',
                'categories.php',
                'flow.php',
                'users.php',
                'clerks.php',
                'counters.php',
                'followups.php',
                'feedbacks.php'
            ];
            
            foreach ($adminPages as $page) {
                $exists = file_exists(__DIR__ . '/beaa/admin/' . $page);
                testItem(
                    $page,
                    $exists,
                    'موجود',
                    'غير موجود',
                    'beaa/admin/' . $page
                );
            }
            
            testItem(
                'php_head.php (مُحدّث)',
                file_exists(__DIR__ . '/beaa/admin/common/php_head.php'),
                'موجود',
                'غير موجود',
                'Includes router and dynamic paths'
            );
            
            testItem(
                'init.php (جديد)',
                file_exists(__DIR__ . '/beaa/admin/common/init.php'),
                'موجود',
                'غير موجود',
                'New initialization file'
            );
            ?>
        </div>
        
        <!-- Documentation Tests -->
        <div class="test-group">
            <h2>📚 اختبارات التوثيق / Documentation Tests</h2>
            
            <?php
            $docFiles = [
                'README-FINAL.md',
                'ROUTER-SETUP-COMPLETE.md',
                'الحل-النهائي-للروابط.md',
                'CORRECT-URLS.md',
                'QUICK-FIX-GUIDE.md',
                'START-HERE.html',
                'links.html',
                'test-router.php',
                'verify-system.php'
            ];
            
            foreach ($docFiles as $file) {
                $exists = file_exists(__DIR__ . '/' . $file);
                testItem(
                    $file,
                    $exists,
                    'موجود',
                    'غير موجود'
                );
            }
            ?>
        </div>
        
        <!-- Summary -->
        <div class="summary">
            <h2>📊 الملخص النهائي / Final Summary</h2>
            
            <div class="stat">
                <div>إجمالي الاختبارات</div>
                <div class="stat-value"><?php echo $totalTests; ?></div>
                <div>Total Tests</div>
            </div>
            
            <div class="stat stat-pass">
                <div>نجح</div>
                <div class="stat-value"><?php echo $passedTests; ?></div>
                <div>Passed</div>
            </div>
            
            <div class="stat stat-fail">
                <div>فشل</div>
                <div class="stat-value"><?php echo $failedTests; ?></div>
                <div>Failed</div>
            </div>
            
            <div class="stat stat-warn">
                <div>تحذير</div>
                <div class="stat-value"><?php echo $warningTests; ?></div>
                <div>Warnings</div>
            </div>
            
            <div style="margin-top: 30px; font-size: 18px;">
                <?php
                $percentage = round(($passedTests / $totalTests) * 100);
                if ($percentage >= 90) {
                    echo "✅ <strong style='color: #28a745;'>النظام يعمل بشكل ممتاز! System Excellent!</strong>";
                } elseif ($percentage >= 75) {
                    echo "✔️ <strong style='color: #3498db;'>النظام يعمل بشكل جيد! System Good!</strong>";
                } elseif ($percentage >= 50) {
                    echo "⚠️ <strong style='color: #ffc107;'>النظام يحتاج تحسينات! Needs Improvements!</strong>";
                } else {
                    echo "❌ <strong style='color: #dc3545;'>النظام يحتاج إصلاحات! Needs Fixes!</strong>";
                }
                echo "<br><br>";
                echo "<div style='font-size: 48px; font-weight: bold; color: " . ($percentage >= 90 ? '#28a745' : ($percentage >= 75 ? '#3498db' : '#ffc107')) . ";'>";
                echo "$percentage%";
                echo "</div>";
                echo "معدل النجاح / Success Rate";
                ?>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <p style="color: #2c3e50; margin-bottom: 15px;"><strong>🔗 الروابط السريعة / Quick Links:</strong></p>
            <a href="<?php echo BASE_URL; ?>" style="margin: 5px; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">الصفحة الرئيسية / Home</a>
            <a href="<?php echo adminUrl(''); ?>" style="margin: 5px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">لوحة التحكم / Dashboard</a>
            <a href="/links.html" style="margin: 5px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">جميع الروابط / All Links</a>
        </div>
    </div>
</body>
</html>
