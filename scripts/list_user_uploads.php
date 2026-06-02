#!/usr/bin/env php
<?php
/**
 * Script liệt kê file upload của user từ bảng write_list
 * Chỉ lấy file do user upload (photos, pdfFiles) - không lấy libraryFiles (file hệ thống)
 * Ưu tiên draft trước, sort theo thời gian tạo từ cũ nhất
 *
 * Usage: php scripts/list_user_uploads.php [--csv] [--limit=100] [--exists] [--months=3] [--basepath=/var/www/html]
 */

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'dongl';
$dbPass = getenv('DB_PASS') ?: 'dycell2156@!';
$dbName = getenv('DB_NAME') ?: 'dongl';
$dbPort = 3306;

// Parse arguments
$outputCsv = in_array('--csv', $argv);
$onlyExisting = in_array('--exists', $argv); // chỉ hiện file còn tồn tại
$limit = 0;
$months = 0; // lấy record từ N tháng trước trở về trước
$basePath = '/dongl/www/assets/upload'; // base path chứa photos/ và files/
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = (int) substr($arg, 8);
    }
    if (strpos($arg, '--months=') === 0) {
        $months = (int) substr($arg, 9);
    }
    if (strpos($arg, '--basepath=') === 0) {
        $basePath = substr($arg, 11);
    }
}
$basePath = rtrim($basePath, '/');

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($conn->connect_error) {
    fwrite(STDERR, "Connection failed: " . $conn->connect_error . "\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

$sql = "
    SELECT
        idx,
        regDate,
        memberIdx,
        productName,
        state,
        CASE WHEN state = 'T' THEN 1 ELSE 0 END AS is_draft,
        photos,
        pdfFiles
    FROM write_list
    WHERE ((photos IS NOT NULL AND photos != '' AND photos != '[]')
       OR (pdfFiles IS NOT NULL AND pdfFiles != '' AND pdfFiles != '[]'))
";

if ($months > 0) {
    $sql .= " AND regDate < DATE_SUB(NOW(), INTERVAL $months MONTH) ";
}

$sql .= "
    ORDER BY
        CASE WHEN state = 'T' THEN 0 ELSE 1 END ASC,
        regDate ASC
";

if ($limit > 0) {
    $sql .= " LIMIT " . $limit;
}

$result = $conn->query($sql);
if (!$result) {
    fwrite(STDERR, "Query failed: " . $conn->error . "\n");
    exit(1);
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

// Mỗi file một dòng
$rows = [];
$totalSize = 0;
$totalFiles = 0;
$missingFiles = 0;

while ($row = $result->fetch_assoc()) {
    $photos = json_decode($row['photos'], true) ?: [];
    $pdfFiles = json_decode($row['pdfFiles'], true) ?: [];

    if (empty($photos) && empty($pdfFiles)) {
        continue;
    }

    $base = [
        'idx'         => $row['idx'],
        'regDate'     => $row['regDate'],
        'memberIdx'   => $row['memberIdx'],
        'productName' => $row['productName'],
        'is_draft'    => $row['is_draft'] ? 'Y' : 'N',
        'state'       => $row['state'],
    ];

    foreach ($photos as $p) {
        if (empty($p['fileName'])) continue;
        $relPath = 'photos/' . $p['fileName'];
        $fullPath = $basePath . '/' . $relPath;
        $exists = file_exists($fullPath);
        $size = $exists ? filesize($fullPath) : 0;

        $totalFiles++;
        if ($exists) {
            $totalSize += $size;
        } else {
            $missingFiles++;
        }

        if ($onlyExisting && !$exists) continue;

        $rows[] = $base + [
            'file_type'   => 'photo',
            'file_path'   => $relPath,
            'file_exists' => $exists ? 'Y' : 'N',
            'file_size'   => $size,
            'file_size_h' => $exists ? formatSize($size) : '-',
        ];
    }

    foreach ($pdfFiles as $f) {
        if (empty($f['fileName'])) continue;
        $relPath = 'files/' . $f['fileName'];
        $fullPath = $basePath . '/' . $relPath;
        $exists = file_exists($fullPath);
        $size = $exists ? filesize($fullPath) : 0;

        $totalFiles++;
        if ($exists) {
            $totalSize += $size;
        } else {
            $missingFiles++;
        }

        if ($onlyExisting && !$exists) continue;

        $rows[] = $base + [
            'file_type'   => 'document',
            'file_path'   => $relPath,
            'file_exists' => $exists ? 'Y' : 'N',
            'file_size'   => $size,
            'file_size_h' => $exists ? formatSize($size) : '-',
        ];
    }
}

$result->free();
$conn->close();

if (empty($rows)) {
    echo "No records found.\n";
    exit(0);
}

$header = ['idx', 'regDate', 'memberIdx', 'productName', 'is_draft', 'state', 'file_type', 'file_path', 'file_exists', 'file_size', 'file_size_h'];

if ($outputCsv) {
    fputcsv(STDOUT, $header);
    foreach ($rows as $r) {
        $out = [];
        foreach ($header as $h) {
            $out[] = $r[$h] ?? '';
        }
        fputcsv(STDOUT, $out);
    }
} else {
    // Table output - truncate paths for readability
    $colWidths = [];
    foreach ($header as $h) {
        $colWidths[$h] = mb_strlen($h);
    }
    foreach ($rows as $r) {
        foreach ($header as $h) {
            $val = (string)($r[$h] ?? '');
            $len = mb_strlen($val);
            if ($h === 'file_path') $len = min($len, 55);
            if ($h === 'productName') $len = min($len, 25);
            if ($len > ($colWidths[$h] ?? 0)) {
                $colWidths[$h] = $len;
            }
        }
    }
    $colWidths['file_path'] = min($colWidths['file_path'], 55);
    $colWidths['productName'] = min($colWidths['productName'], 25);

    // Header
    $line = '';
    foreach ($header as $h) {
        $line .= str_pad($h, $colWidths[$h] + 2);
    }
    echo $line . "\n";
    echo str_repeat('-', mb_strlen($line)) . "\n";

    // Rows
    foreach ($rows as $r) {
        $line = '';
        foreach ($header as $h) {
            $val = (string)($r[$h] ?? '');
            if (mb_strlen($val) > $colWidths[$h]) {
                $val = mb_substr($val, 0, $colWidths[$h] - 3) . '...';
            }
            $line .= str_pad($val, $colWidths[$h] + 2);
        }
        echo $line . "\n";
    }

    echo "\n";
    echo "Total files: $totalFiles\n";
    echo "Existing:    " . ($totalFiles - $missingFiles) . "\n";
    echo "Missing:     $missingFiles\n";
    echo "Total size:  " . formatSize($totalSize) . "\n";
}
