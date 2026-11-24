<?php

namespace Modules\Filemanager\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

/**
 * Upload hardening for the Filemanager:
 *  - the client-supplied Content-Type must match the file extension,
 *  - the MIME type detected from the file bytes must match the file extension,
 *  - image metadata (EXIF / XMP / IPTC / comments / text chunks) and any
 *    data appended after the image must not contain code or markup,
 *  - SVG / text files must not contain active content.
 */
class FileSecurityScanner
{
    /**
     * The only message ever shown to the user. Reasons are written to the log, never returned.
     */
    public const MALICIOUS_MESSAGE = 'Malicious file upload detected.';

    /** Reject images above this pixel count (decompression-bomb protection). */
    protected const MAX_PIXELS = 40_000_000;

    protected const IMAGE_TYPES = [
        'jpg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
        'gif' => IMAGETYPE_GIF,
        'webp' => IMAGETYPE_WEBP,
        'bmp' => IMAGETYPE_BMP,
        'avif' => IMAGETYPE_AVIF,
    ];

    protected const IMAGE_DECODERS = [
        'jpg' => 'imagecreatefromjpeg',
        'png' => 'imagecreatefrompng',
        'gif' => 'imagecreatefromgif',
        'webp' => 'imagecreatefromwebp',
        'bmp' => 'imagecreatefrombmp',
        'avif' => 'imagecreatefromavif',
    ];

    /**
     * Extra Content-Type values real browsers/OSes send for some extensions
     * that are not in Symfony's canonical map.
     */
    protected const CLIENT_MIME_ALIASES = [
        'csv' => ['application/vnd.ms-excel', 'text/plain'],
        'jpg' => ['image/jpg'],
        'docx' => ['application/zip'],
        'xlsx' => ['application/zip'],
    ];

    /**
     * MIME types finfo may detect for some extensions in addition to the canonical ones.
     */
    protected const DETECTED_MIME_ALIASES = [
        'csv' => ['text/plain'],
        'docx' => ['application/zip', 'application/octet-stream'],
        'xlsx' => ['application/zip', 'application/octet-stream'],
        'svg' => ['text/xml', 'application/xml', 'text/plain'],
    ];

    /**
     * Signatures that are never legitimate anywhere in an uploaded file.
     * Kept long enough to avoid false positives in random binary data.
     */
    protected const FILE_SIGNATURES = [
        '/<\?php/i',
        '/<script/i',
        '/__halt_compiler/i',
        '/(eval|assert|system|exec|passthru|popen|proc_open|shell_exec|base64_decode|gzinflate|str_rot13|create_function)\s*\(/i',
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)\b/',
        '/javascript\s*:/i',
        '/\bon(load|error)\s*=/i',
    ];

    protected const SHORT_TAG_SIGNATURES = [
        '/<\?(=|\s)[^\n]{0,200}?(\?>|;)/',
        '/<%[=@][^\n]{0,200}?%>/',
    ];

    /**
     * Signatures checked inside metadata, appended data and text-based files.
     */
    protected const METADATA_SIGNATURES = [
        '/<\?(php|=|\s)/i',
        '/<%/',
        '/<script/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
        '/<foreignObject/i',
        '/<!ENTITY/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:\s*text\/html/i',
        '/\bon(load|error|click|dblclick|mouse[a-z]*|pointer[a-z]*|key[a-z]*|focus[a-z]*|blur|submit|change|input|begin|end|abort|animation[a-z]*|transition[a-z]*|toggle|wheel|drag[a-z]*|drop|copy|paste|unload|resize|scroll|message|hashchange)\s*=/i',
        '/\b(eval|assert|system|exec|shell_exec|passthru|popen|proc_open|pcntl_exec|base64_decode|gzinflate|str_rot13|create_function|call_user_func|file_put_contents|fopen|include|require)\s*\(/i',
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER|FILES)\b/',
    ];

    protected const SVG_SIGNATURES = [
        '/<script/i',
        '/<foreignObject/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
        '/<!ENTITY/i',
        '/\bon[a-z]+\s*=/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:\s*text\/html/i',
        '/(xlink:)?href\s*=\s*["\']\s*(?!#|data:image\/)/i',
        '/<\?(php|=|\s)/i',
    ];

    protected const TEXT_EXTENSIONS = ['txt', 'csv', 'json', 'xml', 'md', 'html', 'htm'];

    public function assertSafe(UploadedFile $file, string $extension): void
    {
        $this->assertClientMimeType($file, $extension);
        $this->assertDetectedMimeType($file, $extension);

        $contents = (string) @file_get_contents($file->getRealPath());

        if ($contents === '') {
            $this->reject('empty or unreadable file', $file);
        }

        $this->assertNoSignatures($contents, self::FILE_SIGNATURES, 'code signature in file body', $file);

        // Short tags (<?= , <? , <%= ) are too short to match safely against raw binary,
        // so only look for them inside readable text runs.
        if (preg_match_all('/[\x20-\x7E\t\r\n]{8,}/', $contents, $runs) > 0) {
            $this->assertNoSignatures(implode("\n", $runs[0]), self::SHORT_TAG_SIGNATURES, 'short code tag in file body', $file);
        }

        if (isset(self::IMAGE_TYPES[$extension])) {
            $this->assertIntactImage($file, $extension);
        }

        match (true) {
            $extension === 'svg' => $this->assertNoSignatures($contents, self::SVG_SIGNATURES, 'active content in SVG', $file),
            $extension === 'jpg' => $this->scanJpeg($file, $contents),
            $extension === 'png' => $this->scanPng($file, $contents),
            $extension === 'gif' => $this->scanGif($file, $contents),
            $extension === 'webp' => $this->scanWebp($file, $contents),
            in_array($extension, self::TEXT_EXTENSIONS, true) => $this->assertNoSignatures($contents, self::METADATA_SIGNATURES, 'code in text file', $file),
            default => null,
        };
    }

    protected function assertClientMimeType(UploadedFile $file, string $extension): void
    {
        $clientMime = strtolower(trim(explode(';', (string) $file->getClientMimeType())[0]));
        $allowed = array_merge(
            MimeTypes::getDefault()->getMimeTypes($extension),
            self::CLIENT_MIME_ALIASES[$extension] ?? []
        );

        if ($clientMime === '' || !in_array($clientMime, $allowed, true)) {
            $this->reject("client Content-Type '{$clientMime}' does not match .{$extension}", $file);
        }
    }

    protected function assertDetectedMimeType(UploadedFile $file, string $extension): void
    {
        // Detected from file contents (finfo); the client header is untrusted.
        $detected = $file->getMimeType();
        $allowed = array_merge(
            MimeTypes::getDefault()->getMimeTypes($extension),
            self::DETECTED_MIME_ALIASES[$extension] ?? []
        );

        if ($detected === null || !in_array($detected, $allowed, true)) {
            $this->reject("detected MIME '{$detected}' does not match .{$extension}", $file);
        }
    }

    /**
     * Fully decode the image. Header sniffing (finfo / getimagesize) only reads the
     * first bytes, so a file with a valid header and a tampered/garbage body passes
     * those checks; a full decode with warnings treated as errors does not.
     */
    protected function assertIntactImage(UploadedFile $file, string $extension): void
    {
        $path = $file->getRealPath();
        $info = @getimagesize($path);

        if ($info === false || ($info[0] ?? 0) <= 0 || ($info[1] ?? 0) <= 0) {
            $this->reject('image header unreadable', $file);
        }

        if (($info[2] ?? null) !== self::IMAGE_TYPES[$extension]) {
            $this->reject('image header type does not match .' . $extension, $file);
        }

        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            $this->reject("image dimensions too large ({$info[0]}x{$info[1]})", $file);
        }

        $decoder = self::IMAGE_DECODERS[$extension];

        if (!function_exists($decoder)) {
            $this->reject("no decoder available for .{$extension}", $file);
        }

        $warnings = [];
        $previousIgnore = ini_set('gd.jpeg_ignore_warning', '0');
        set_error_handler(static function (int $errno, string $message) use (&$warnings): bool {
            $warnings[] = $message;

            return true;
        });

        try {
            $image = $decoder($path);
        } catch (\Throwable $e) {
            $image = false;
            $warnings[] = $e->getMessage();
        } finally {
            restore_error_handler();
            if ($previousIgnore !== false) {
                ini_set('gd.jpeg_ignore_warning', $previousIgnore);
            }
        }

        if ($image === false || $warnings !== []) {
            $this->reject('image failed to decode cleanly: ' . implode(' | ', $warnings), $file);
        }

        unset($image);
    }

    /**
     * JPEG: scan APPn (EXIF, XMP, IPTC, ...) and COM segments, plus anything after EOI.
     */
    protected function scanJpeg(UploadedFile $file, string $data): void
    {
        $len = strlen($data);
        $pos = 2; // skip SOI
        $reachedScan = false;

        // 1) Header segments must chain exactly: FF <marker> <len> ... FF <marker> ...
        while ($pos + 4 <= $len) {
            if ($data[$pos] !== "\xFF") {
                $this->reject("JPEG segment chain broken at offset {$pos}", $file);
            }

            $marker = ord($data[$pos + 1]);

            if ($marker === 0xFF) { // fill byte
                $pos++;
                continue;
            }

            $pos = $this->readJpegSegment($file, $data, $pos, $marker);

            if ($marker === 0xDA) {
                $reachedScan = true;
                break;
            }
        }

        if (!$reachedScan) {
            $this->reject('JPEG start-of-scan marker missing', $file);
        }

        // 2) Entropy-coded data: every 0xFF must be byte-stuffed (FF 00), a restart
        //    marker in strict RST0..RST7 order, or a valid table/scan marker.
        $nextRst = 0;
        $endOfImage = null;

        while (($pos = strpos($data, "\xFF", $pos)) !== false) {
            if ($pos + 1 >= $len) {
                $this->reject('JPEG ends inside a marker', $file);
            }

            $marker = ord($data[$pos + 1]);

            if ($marker === 0x00) {
                $pos += 2;
            } elseif ($marker === 0xFF) {
                $pos++;
            } elseif ($marker >= 0xD0 && $marker <= 0xD7) {
                if ($marker - 0xD0 !== $nextRst) {
                    $this->reject('JPEG restart markers out of sequence', $file);
                }
                $nextRst = ($nextRst + 1) % 8;
                $pos += 2;
            } elseif ($marker === 0xD9) {
                $endOfImage = $pos;
                break;
            } elseif (in_array($marker, [0xC4, 0xCC, 0xDA, 0xDB, 0xDD, 0xFE], true) || ($marker >= 0xE0 && $marker <= 0xEF)) {
                // Progressive JPEGs carry tables and further scans between scans.
                $pos = $this->readJpegSegment($file, $data, $pos, $marker);
                $nextRst = 0;
            } else {
                $this->reject(sprintf('unexpected JPEG marker 0x%02X in image data at offset %d', $marker, $pos), $file);
            }
        }

        if ($endOfImage === null) {
            $this->reject('JPEG end-of-image marker missing', $file);
        }

        // Anything after EOI (some phones append extra frames) must not contain code.
        $this->assertCleanMetadata(substr($data, $endOfImage + 2), $file);

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($file->getRealPath(), null, true);
            if (is_array($exif)) {
                $this->assertCleanMetadataArray($exif, $file);
            }
        }
    }

    /**
     * Validate one length-prefixed JPEG segment and return the offset after it.
     */
    protected function readJpegSegment(UploadedFile $file, string $data, int $pos, int $marker): int
    {
        $len = strlen($data);

        // Standalone markers have no length field and are invalid in this position.
        if ($marker === 0x00 || $marker === 0x01 || $marker === 0xD8 || $marker === 0xD9 || ($marker >= 0xD0 && $marker <= 0xD7)) {
            $this->reject(sprintf('unexpected JPEG marker 0x%02X at offset %d', $marker, $pos), $file);
        }

        if ($pos + 4 > $len) {
            $this->reject('JPEG truncated in segment header', $file);
        }

        $segLen = unpack('n', substr($data, $pos + 2, 2))[1];

        if ($segLen < 2 || $pos + 2 + $segLen > $len) {
            $this->reject('corrupt JPEG segment length', $file);
        }

        // APP0..APP15 and COM carry metadata (EXIF, XMP, IPTC, comments).
        if (($marker >= 0xE0 && $marker <= 0xEF) || $marker === 0xFE) {
            $this->assertCleanMetadata(substr($data, $pos + 4, $segLen - 2), $file);
        }

        return $pos + 2 + $segLen;
    }

    /**
     * PNG: scan textual/metadata chunks and anything after IEND.
     */
    protected function scanPng(UploadedFile $file, string $data): void
    {
        $len = strlen($data);
        $pos = 8; // signature

        $seenEnd = false;

        while ($pos + 8 <= $len) {
            $chunkLen = unpack('N', substr($data, $pos, 4))[1];
            $type = substr($data, $pos + 4, 4);

            if ($pos + 12 + $chunkLen > $len) {
                $this->reject("PNG chunk {$type} overruns file", $file);
            }

            $body = substr($data, $pos + 8, $chunkLen);
            $crc = unpack('N', substr($data, $pos + 8 + $chunkLen, 4))[1];

            // Every PNG chunk carries a CRC: any edited byte breaks it.
            if ((crc32($type . $body) & 0xFFFFFFFF) !== $crc) {
                $this->reject("PNG chunk {$type} CRC mismatch (tampered binary)", $file);
            }

            if (in_array($type, ['tEXt', 'iTXt', 'eXIf'], true)) {
                $this->assertCleanMetadata($body, $file);
            } elseif ($type === 'zTXt') {
                $this->assertCleanMetadata($body, $file);
                $nul = strpos($body, "\0");
                if ($nul !== false) {
                    $inflated = @gzuncompress(substr($body, $nul + 2));
                    if (is_string($inflated)) {
                        $this->assertCleanMetadata($inflated, $file);
                    }
                }
            }

            $pos += 12 + $chunkLen;

            if ($type === 'IEND') {
                $seenEnd = true;
                $this->assertCleanMetadata(substr($data, $pos), $file);
                break;
            }
        }

        if (!$seenEnd) {
            $this->reject('PNG IEND chunk missing', $file);
        }
    }

    /**
     * GIF: scan comment / application extension blocks and trailing data.
     */
    protected function scanGif(UploadedFile $file, string $data): void
    {
        // Comment (0x21 0xFE) and application (0x21 0xFF) extensions.
        if (preg_match_all('/\x21[\xFE\xFF](.{0,65535}?)\x00/s', $data, $m)) {
            foreach ($m[1] as $block) {
                $this->assertCleanMetadata($block, $file);
            }
        }

        $trailer = strrpos($data, "\x00\x3B");
        if ($trailer === false) {
            $this->reject('GIF trailer missing', $file);
        }
        $this->assertCleanMetadata(substr($data, $trailer + 2), $file);
    }

    /**
     * WEBP: scan EXIF / XMP chunks and data beyond the RIFF size.
     */
    protected function scanWebp(UploadedFile $file, string $data): void
    {
        $len = strlen($data);

        if ($len < 20 || substr($data, 0, 4) !== 'RIFF' || substr($data, 8, 4) !== 'WEBP') {
            $this->reject('invalid WEBP container', $file);
        }

        // The RIFF header stores the exact file size: inserted/removed bytes break it.
        $riffEnd = 8 + unpack('V', substr($data, 4, 4))[1];

        if ($riffEnd !== $len) {
            $this->reject("WEBP size mismatch (header {$riffEnd}, actual {$len})", $file);
        }

        $pos = 12;

        while ($pos < $riffEnd) {
            if ($pos + 8 > $riffEnd) {
                $this->reject('WEBP chunk header truncated', $file);
            }

            $type = substr($data, $pos, 4);
            $chunkLen = unpack('V', substr($data, $pos + 4, 4))[1];

            if (!preg_match('/^[A-Z0-9 ]{4}$/', $type)) {
                $this->reject('invalid WEBP chunk id', $file);
            }

            $next = $pos + 8 + $chunkLen + ($chunkLen % 2);

            if ($next > $riffEnd) {
                $this->reject("WEBP chunk {$type} overruns file", $file);
            }

            if (in_array($type, ['EXIF', 'XMP '], true)) {
                $this->assertCleanMetadata(substr($data, $pos + 8, $chunkLen), $file);
            }

            $pos = $next;
        }
    }

    protected function assertCleanMetadataArray(array $values, UploadedFile $file): void
    {
        array_walk_recursive($values, function ($value) use ($file) {
            if (is_string($value)) {
                $this->assertCleanMetadata($value, $file);
            }
        });
    }

    protected function assertCleanMetadata(string $value, UploadedFile $file): void
    {
        if ($value === '') {
            return;
        }

        // Metadata is often binary (EXIF IFDs, thumbnails) or UTF-16 (XPComment).
        // Drop NUL bytes, then only inspect readable text runs so random binary
        // bytes do not trigger false positives.
        $value = str_replace("\0", '', $value);

        if (preg_match_all('/[\x20-\x7E\t\r\n]{4,}/', $value, $runs) < 1) {
            return;
        }

        $this->assertNoSignatures(implode("\n", $runs[0]), self::METADATA_SIGNATURES, 'code in image metadata', $file);
    }

    protected function assertNoSignatures(string $value, array $signatures, string $context, ?UploadedFile $file = null): void
    {
        foreach ($signatures as $pattern) {
            if (preg_match($pattern, $value, $match) === 1) {
                $this->reject("{$context}: matched {$pattern} ('" . substr($match[0], 0, 40) . "')", $file);
            }
        }
    }

    /**
     * Log the real reason for the security team, show the user only a generic message.
     */
    protected function reject(string $reason, ?UploadedFile $file = null): never
    {
        Log::warning('Filemanager: malicious upload rejected', [
            'reason' => $reason,
            'original_name' => $file?->getClientOriginalName(),
            'client_mime' => $file?->getClientMimeType(),
            'size' => $file?->getSize(),
            'ip' => request()?->ip(),
            'user_id' => auth()->id(),
        ]);

        throw new InvalidArgumentException(self::MALICIOUS_MESSAGE);
    }
}
