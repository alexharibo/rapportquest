<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

const MAX_FILE_SIZE  = 20 * 1024 * 1024; // 20 MB
const UPLOAD_DIR     = __DIR__ . '/../uploads/';
const ALLOWED_MIME   = ['application/pdf'];
const EXPIRY_DAYS    = 30;

function jsonError(string $message, int $status = 400): never
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function jsonSuccess(array $data): never
{
    http_response_code(200);
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/* ---- Only accept POST ---- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Metode ikke tilladt.', 405);
}

/* ---- File presence check ---- */
if (empty($_FILES['report']) || $_FILES['report']['error'] === UPLOAD_ERR_NO_FILE) {
    jsonError('Ingen fil valgt.');
}

$file = $_FILES['report'];

/* ---- Upload error codes ---- */
if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'Filen overskrider serverens maksimale filstørrelse.',
        UPLOAD_ERR_FORM_SIZE  => 'Filen overskrider formularens maksimale filstørrelse.',
        UPLOAD_ERR_PARTIAL    => 'Filen blev kun delvist uploadet. Prøv igen.',
        UPLOAD_ERR_NO_TMP_DIR => 'Midlertidig mappe mangler på serveren.',
        UPLOAD_ERR_CANT_WRITE => 'Filen kunne ikke skrives til disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload stoppet af en PHP-udvidelse.',
    ];
    jsonError($uploadErrors[$file['error']] ?? 'Ukendt upload-fejl.');
}

/* ---- Size check ---- */
if ($file['size'] > MAX_FILE_SIZE) {
    jsonError('Filen må ikke overstige 20 MB.');
}

if ($file['size'] === 0) {
    jsonError('Filen er tom.');
}

/* ---- MIME type validation ---- */
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, ALLOWED_MIME, true)) {
    jsonError('Kun PDF-filer er tilladt.');
}

/* ---- PDF header magic bytes check ---- */
$handle = fopen($file['tmp_name'], 'rb');
$magic  = fread($handle, 5);
fclose($handle);

if ($magic !== '%PDF-') {
    jsonError('Filen er ikke en gyldig PDF.');
}

/* ---- Extension check ---- */
$originalName = $file['name'];
$extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if ($extension !== 'pdf') {
    jsonError('Filen skal have .pdf-extension.');
}

/* ---- Sanitise original name ---- */
$safeName = preg_replace('/[^a-zA-Z0-9æøåÆØÅ._\- ]/', '_', $originalName);
$safeName = trim($safeName);

/* ---- Generate unique filename ---- */
$uniqueName = bin2hex(random_bytes(16)) . '.pdf';
$targetPath = UPLOAD_DIR . $uniqueName;

/* ---- Ensure upload directory exists ---- */
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        jsonError('Upload-mappen kunne ikke oprettes.', 500);
    }
}

/* ---- Move file ---- */
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    jsonError('Filen kunne ikke gemmes. Prøv igen.', 500);
}

/* ---- Save to database ---- */
try {
    $pdo = getDbConnection();

    $expiresAt = (new DateTimeImmutable())->modify('+' . EXPIRY_DAYS . ' days')->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        'INSERT INTO reports (filename, original_name, file_path, file_size, status, expires_at)
         VALUES (:filename, :original_name, :file_path, :file_size, :status, :expires_at)'
    );

    $stmt->execute([
        ':filename'      => $uniqueName,
        ':original_name' => $safeName,
        ':file_path'     => $targetPath,
        ':file_size'     => $file['size'],
        ':status'        => 'pending',
        ':expires_at'    => $expiresAt,
    ]);

    $reportId = (int) $pdo->lastInsertId();

} catch (PDOException $e) {
    // Remove uploaded file if DB insert fails
    @unlink($targetPath);
    jsonError('Databasefejl. Prøv igen.', 500);
}

jsonSuccess([
    'report_id'     => $reportId,
    'original_name' => $safeName,
    'file_size'     => $file['size'],
    'redirect'      => 'analyse.php?id=' . $reportId,
]);
