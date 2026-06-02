#!/usr/bin/env php
<?php
/**
 * Script xoá file upload từ CSV input (output của list_user_uploads.php)
 * Có preview + confirm trước khi xoá
 *
 * Usage: php scripts/delete_uploads.php <file.csv> [--basepath=/dongl/www/assets/upload]
 *
 * CSV cần có cột: file_path, file_exists
 */

if ($argc < 2 || in_array('--help', $argv)) {
    fwrite(STDERR, "Usage: php scripts/delete_uploads.php <file.csv> [--basepath=/dongl/www/assets/upload]\n");
    exit(1);
}

$csvFile = $argv[1];
if (!file_exists($csvFile)) {
    fwrite(STDERR, "File not found: $csvFile\n");
    exit(1);
}

$basePath = '/dongl/www/assets/upload';
foreach ($argv as $arg) {
    if (strpos($arg, '--basepath=') === 0) {
        $basePath = substr($arg, 11);
    }
}
$basePath = rtrim($basePath, '/');

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

// Read CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    fwrite(STDERR, "Cannot open file: $csvFile\n");
    exit(1);
}

$header = fgetcsv($handle);
$filePathIdx = array_search('file_path', $header);
$fileExistsIdx = array_search('file_exists', $header);
$regDateIdx = array_search('regDate', $header);
$memberIdx = array_search('memberIdx', $header);
$isDraftIdx = array_search('is_draft', $header);
$fileTypeIdx = array_search('file_type', $header);

if ($filePathIdx === false) {
    fwrite(STDERR, "CSV missing required column: file_path\n");
    exit(1);
}

$filesToDelete = [];
$totalSize = 0;
$skipped = 0;

while (($row = fgetcsv($handle)) !== false) {
    $relPath = $row[$filePathIdx] ?? '';
    if (empty($relPath)) continue;

    // Skip file đã không tồn tại
    if ($fileExistsIdx !== false && ($row[$fileExistsIdx] ?? '') === 'N') {
        $skipped++;
        continue;
    }

    $fullPath = $basePath . '/' . $relPath;

    if (!file_exists($fullPath)) {
        $skipped++;
        continue;
    }

    $size = filesize($fullPath);
    $totalSize += $size;

    $filesToDelete[] = [
        'rel_path'  => $relPath,
        'full_path' => $fullPath,
        'size'      => $size,
        'size_h'    => formatSize($size),
        'regDate'   => $regDateIdx !== false ? ($row[$regDateIdx] ?? '') : '',
        'memberIdx' => $memberIdx !== false ? ($row[$memberIdx] ?? '') : '',
        'is_draft'  => $isDraftIdx !== false ? ($row[$isDraftIdx] ?? '') : '',
        'file_type' => $fileTypeIdx !== false ? ($row[$fileTypeIdx] ?? '') : '',
    ];
}
fclose($handle);

if (empty($filesToDelete)) {
    echo "No files to delete. ($skipped skipped - not found on disk)\n";
    exit(0);
}

// === PREVIEW ===
$photoCount = 0;
$docCount = 0;
$draftCount = 0;
$members = [];
$oldest = null;
$newest = null;

foreach ($filesToDelete as $f) {
    if ($f['file_type'] === 'photo') $photoCount++;
    if ($f['file_type'] === 'document') $docCount++;
    if ($f['is_draft'] === 'Y') $draftCount++;
    if (!empty($f['memberIdx'])) $members[$f['memberIdx']] = true;
    if ($f['regDate']) {
        if ($oldest === null || $f['regDate'] < $oldest) $oldest = $f['regDate'];
        if ($newest === null || $f['regDate'] > $newest) $newest = $f['regDate'];
    }
}

echo "\n";
echo str_repeat('=', 50) . "\n";
echo "  PREVIEW - Files to delete\n";
echo str_repeat('=', 50) . "\n";
echo "Total files:     " . count($filesToDelete) . "\n";
echo "  Photos:        $photoCount\n";
echo "  Documents:     $docCount\n";
echo "  Drafts:        $draftCount\n";
echo "Total size:      " . formatSize($totalSize) . "\n";
echo "Members:         " . count($members) . " users\n";
echo "Date range:      $oldest ~ $newest\n";
echo "Skipped (gone):  $skipped\n";
echo str_repeat('=', 50) . "\n";

// === CONFIRM ===
echo "\nAre you sure you want to DELETE all " . count($filesToDelete) . " files? (yes/no): ";
$answer = trim(fgets(STDIN));

if (strtolower($answer) !== 'yes') {
    echo "Aborted.\n";
    exit(0);
}

// === DELETE ===
$deleted = 0;
$failed = 0;
$freedSize = 0;

foreach ($filesToDelete as $f) {
    if (@unlink($f['full_path'])) {
        $deleted++;
        $freedSize += $f['size'];
    } else {
        $failed++;
        fwrite(STDERR, "Failed to delete: " . $f['full_path'] . "\n");
    }
}

echo "\n";
echo "Done!\n";
echo "Deleted:     $deleted files\n";
echo "Failed:      $failed files\n";
echo "Freed space: " . formatSize($freedSize) . "\n";
