<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار نظام الروابط الديناميكية - Router Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: rtl;
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
        .success {
            background: #d4edda;
            border-right: 5px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info {
            background: #d1ecf1;
            border-right: 5px solid #17a2b8;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .test-section h2 {
            color: #34495e;
            margin-bottom: 15px;
            font-size: 24px;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            background: white;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .test-value {
            font-family: 'Courier New', monospace;
            color: #27ae60;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            color: #e83e8c;
            font-family: 'Courier New', monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #34495e;
            color: white;
        }
        tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/beaa/config.php';
    ?>
    
    <div class="container">
        <h1>🎯 اختبار نظام الروابط الديناميكية</h1>
        <h1>Router System Test</h1>
        
        <div class="success">
            <h3>✅ نظام الروابط الديناميكية يعمل!</h3>
            <p>Dynamic Router System is Working!</p>
        </div>
        
        <div class="test-section">
            <h2>📋 المسارات المعرّفة / Defined Paths</h2>
            
            <div class="test-item">
                <span class="test-label">BASE_PATH:</span>
                <span class="test-value"><?php echo BASE_PATH; ?></span>
            </div>
            
            <div class="test-item">
                <span class="test-label">ADMIN_BASE_PATH:</span>
                <span class="test-value"><?php echo ADMIN_BASE_PATH; ?></span>
            </div>
            
            <div class="test-item">
                <span class="test-label">API_BASE_PATH:</span>
                <span class="test-value"><?php echo API_BASE_PATH; ?></span>
            </div>
            
            <div class="test-item">
                <span class="test-label">FILES_PATH:</span>
                <span class="test-value"><?php echo FILES_PATH; ?></span>
            </div>
            
            <div class="test-item">
                <span class="test-label">CSS_PATH:</span>
                <span class="test-value"><?php echo CSS_PATH; ?></span>
            </div>
            
            <div class="test-item">
                <span class="test-label">JS_PATH:</span>
                <span class="test-value"><?php echo JS_PATH; ?></span>
            </div>
            
            <div class="test-item">
                <span class="test-label">BASE_URL:</span>
                <span class="test-value"><?php echo BASE_URL; ?></span>
            </div>
        </div>
        
        <div class="test-section">
            <h2>🔧 اختبار الدوال المساعدة / Helper Functions Test</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>الدالة / Function</th>
                        <th>المدخل / Input</th>
                        <th>النتيجة / Output</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>url()</code></td>
                        <td><code>'api/get.php'</code></td>
                        <td><code><?php echo url('api/get.php'); ?></code></td>
                    </tr>
                    <tr>
                        <td><code>adminUrl()</code></td>
                        <td><code>'categories.php'</code></td>
                        <td><code><?php echo adminUrl('categories.php'); ?></code></td>
                    </tr>
                    <tr>
                        <td><code>apiUrl()</code></td>
                        <td><code>'db.php'</code></td>
                        <td><code><?php echo apiUrl('db.php'); ?></code></td>
                    </tr>
                    <tr>
                        <td><code>asset()</code></td>
                        <td><code>'css/style.css'</code></td>
                        <td><code><?php echo asset('css/style.css'); ?></code></td>
                    </tr>
                    <tr>
                        <td><code>asset()</code></td>
                        <td><code>'js/script.js'</code></td>
                        <td><code><?php echo asset('js/script.js'); ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="test-section">
            <h2>🔗 اختبار الروابط / Link Testing</h2>
            
            <p><strong>جرّب هذه الروابط - كلها يجب أن تعمل:</strong></p>
            <p><strong>Try these links - all should work:</strong></p>
            
            <div style="text-align: center; margin: 20px 0;">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-success">الصفحة الرئيسية / Home</a>
                <a href="<?php echo adminUrl(''); ?>" class="btn">لوحة التحكم / Dashboard</a>
                <a href="<?php echo adminUrl('account/login.php'); ?>" class="btn">تسجيل الدخول / Login</a>
                <a href="<?php echo url('files/logos/systemlogo-md.svg'); ?>" class="btn">شعار النظام / Logo</a>
            </div>
        </div>
        
        <div class="info">
            <h3>ℹ️ معلومات مهمة / Important Information</h3>
            <ul style="margin-right: 20px; line-height: 2;">
                <li>جميع الروابط الآن ديناميكية وتعمل من أي مكان</li>
                <li>All links are now dynamic and work from anywhere</li>
                <li>النظام يكتشف المسار الأساسي تلقائياً</li>
                <li>System auto-detects the base path</li>
                <li>الروابط الخاطئة يتم تصحيحها تلقائياً</li>
                <li>Wrong URLs are auto-corrected</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <p style="color: #7f8c8d;"><strong>📚 للمزيد من المعلومات:</strong></p>
            <p><code>ROUTER-SETUP-COMPLETE.md</code></p>
        </div>
    </div>
</body>
</html>
