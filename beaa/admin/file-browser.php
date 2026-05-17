<?php
$prev = 16;

require_once './common/php_head.php';

$title = 'File Browser';
$projectRoot = realpath(__DIR__ . '/../..');
$relativePath = isset($_GET['path']) ? trim($_GET['path']) : '';
$downloadPath = isset($_GET['download']) ? trim($_GET['download']) : '';
$blockedNames = array('.git', '.env', '.ssh', '.bash_history', '.profile', '.bashrc');

function fb_safe_path($root, $path) {
    $path = str_replace("\0", '', $path);
    $path = ltrim($path, "/\\");
    $fullPath = realpath($root . DIRECTORY_SEPARATOR . $path);
    if ($fullPath === false || strpos($fullPath, $root) !== 0) {
        return false;
    }
    return $fullPath;
}

function fb_is_blocked($root, $fullPath, $blockedNames) {
    $relative = ltrim(str_replace($root, '', $fullPath), DIRECTORY_SEPARATOR);
    $parts = $relative === '' ? array() : preg_split('/[\/\\\\]+/', $relative);
    foreach ($parts as $part) {
        if (in_array($part, $blockedNames, true) || strpos($part, '.env') === 0) {
            return true;
        }
    }
    return false;
}

function fb_size($bytes) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    }
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

if ($downloadPath !== '') {
    $downloadFullPath = fb_safe_path($projectRoot, $downloadPath);
    if ($downloadFullPath && is_file($downloadFullPath) && !fb_is_blocked($projectRoot, $downloadFullPath, $blockedNames)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($downloadFullPath) . '"');
        header('Content-Length: ' . filesize($downloadFullPath));
        readfile($downloadFullPath);
        exit();
    }
    http_response_code(403);
    echo 'Forbidden';
    exit();
}

$currentPath = fb_safe_path($projectRoot, $relativePath);
if (!$currentPath || !is_dir($currentPath) || fb_is_blocked($projectRoot, $currentPath, $blockedNames)) {
    $relativePath = '';
    $currentPath = $projectRoot;
}

$items = scandir($currentPath);
$relativeCurrent = ltrim(str_replace($projectRoot, '', $currentPath), DIRECTORY_SEPARATOR);
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>
        <style>
            .file-browser-path {font-family: monospace; direction: ltr; text-align: left;}
            .file-browser-actions a {margin-left: 8px;}
        </style>
    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container marg-bottom-50">
            <div class="well well-header">متصفح ملفات المشروع - قراءة فقط</div>
            <div class="alert alert-warning">
                هذا المتصفح مخصص للمدير فقط، محصور داخل جذر المشروع، ويمنع الوصول إلى ملفات الأسرار مثل .env و .git.
            </div>
            <p class="file-browser-path">/<?php echo htmlspecialchars($relativeCurrent); ?></p>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>الحجم</th>
                        <th>آخر تعديل</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($relativeCurrent !== '') { ?>
                        <?php $parentPath = dirname($relativeCurrent); ?>
                        <tr>
                            <td><i class="fa fa-folder-open"></i> ..</td>
                            <td>مجلد</td>
                            <td>-</td>
                            <td>-</td>
                            <td><a href="?path=<?php echo urlencode($parentPath === '.' ? '' : $parentPath); ?>">فتح</a></td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($items as $item) {
                        if ($item === '.' || $item === '..') {
                            continue;
                        }
                        $fullPath = $currentPath . DIRECTORY_SEPARATOR . $item;
                        $safeFullPath = fb_safe_path($projectRoot, ltrim(str_replace($projectRoot, '', $fullPath), DIRECTORY_SEPARATOR));
                        if (!$safeFullPath || fb_is_blocked($projectRoot, $safeFullPath, $blockedNames)) {
                            continue;
                        }
                        $itemRelativePath = ltrim(str_replace($projectRoot, '', $safeFullPath), DIRECTORY_SEPARATOR);
                        $isDir = is_dir($safeFullPath);
                        ?>
                        <tr>
                            <td class="file-browser-path">
                                <i class="fa <?php echo $isDir ? 'fa-folder' : 'fa-file-o'; ?>"></i>
                                <?php echo htmlspecialchars($item); ?>
                            </td>
                            <td><?php echo $isDir ? 'مجلد' : 'ملف'; ?></td>
                            <td><?php echo $isDir ? '-' : fb_size(filesize($safeFullPath)); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', filemtime($safeFullPath)); ?></td>
                            <td class="file-browser-actions">
                                <?php if ($isDir) { ?>
                                    <a href="?path=<?php echo urlencode($itemRelativePath); ?>">فتح</a>
                                <?php } else { ?>
                                    <a href="?download=<?php echo urlencode($itemRelativePath); ?>">تنزيل</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php include_once './common/footer.php'; ?>
        <?php include_once './common/foot_scripts.php'; ?>
    </body>
</html>
