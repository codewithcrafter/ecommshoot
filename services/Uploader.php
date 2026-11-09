<?php
/**
 * ECOMSHOOT - Secure Media & File Upload Management Service
 */

class Uploader {
    private static array $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg'
    ];

    private static int $maxFileSize = 10485760; // 10 MB

    public static function uploadImage(array $fileInput, string $altText = '', string $caption = ''): array {
        if (!isset($fileInput['error']) || is_array($fileInput['error'])) {
            return ['success' => false, 'error' => 'Invalid file parameter provided.'];
        }

        switch ($fileInput['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was selected for upload.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'Uploaded file exceeds server file size limit.'];
            default:
                return ['success' => false, 'error' => 'An unknown file upload error occurred.'];
        }

        if ($fileInput['size'] > self::$maxFileSize) {
            return ['success' => false, 'error' => 'File size exceeds maximum permitted limit (10MB).'];
        }

        // Validate MIME type securely using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileInput['tmp_name']);

        if (!array_key_exists($mime, self::$allowedMimeTypes)) {
            return ['success' => false, 'error' => 'Forbidden file format. Only JPEG, PNG, WebP, and SVG images are permitted.'];
        }

        $extension = self::$allowedMimeTypes[$mime];
        $originalName = pathinfo($fileInput['name'], PATHINFO_FILENAME);
        $safeOriginalName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
        $newFileName = $safeOriginalName . '_' . bin2hex(random_bytes(6)) . '.' . $extension;

        if (!is_dir(UPLOADS_PATH)) {
            mkdir(UPLOADS_PATH, 0755, true);
        }

        $destination = UPLOADS_PATH . '/' . $newFileName;
        $relativeFilePath = 'uploads/' . $newFileName;

        if (!move_uploaded_file($fileInput['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file to target storage directory.'];
        }

        // Record in media table
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO media (file_name, original_name, file_path, file_size, mime_type, alt_text, caption) VALUES (:file_name, :orig_name, :path, :size, :mime, :alt, :cap)");
            $stmt->execute([
                'file_name' => $newFileName,
                'orig_name' => $fileInput['name'],
                'path' => $relativeFilePath,
                'size' => $fileInput['size'],
                'mime' => $mime,
                'alt' => $altText ?: $originalName,
                'cap' => $caption
            ]);
            $mediaId = $db->lastInsertId();

            return [
                'success' => true,
                'media_id' => $mediaId,
                'file_path' => $relativeFilePath,
                'file_url' => asset_url($relativeFilePath),
                'file_name' => $newFileName
            ];
        } catch (Exception $e) {
            error_log('Uploader DB Error: ' . $e->getMessage());
            return [
                'success' => true,
                'media_id' => null,
                'file_path' => $relativeFilePath,
                'file_url' => asset_url($relativeFilePath),
                'file_name' => $newFileName
            ];
        }
    }
}

