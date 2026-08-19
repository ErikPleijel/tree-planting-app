<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * Thin wrapper around PHP's native exif_read_data() — deliberately not
 * intervention/image, whose own EXIF support is just this same native
 * function under the hood. Never throws: any missing/unreadable/malformed
 * data results in an all-null return, so a photo upload never fails
 * because of provenance extraction.
 */
class ExifExtractor
{
    public function extract(string $filePath): array
    {
        $empty = [
            'captured_at' => null,
            'latitude'    => null,
            'longitude'   => null,
        ];

        if (! function_exists('exif_read_data')) {
            return $empty;
        }

        try {
            $data = @exif_read_data($filePath, null, true);

            if (! is_array($data)) {
                return $empty;
            }

            return [
                'captured_at' => $this->extractCapturedAt($data),
                'latitude'    => $this->extractCoordinate($data, 'GPSLatitude', 'GPSLatitudeRef'),
                'longitude'   => $this->extractCoordinate($data, 'GPSLongitude', 'GPSLongitudeRef'),
            ];
        } catch (Throwable) {
            return $empty;
        }
    }

    private function extractCapturedAt(array $data): ?Carbon
    {
        $raw = $data['EXIF']['DateTimeOriginal']
            ?? $data['EXIF']['DateTime']
            ?? $data['IFD0']['DateTime']
            ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            // EXIF datetime format: "YYYY:MM:DD HH:MM:SS"
            return Carbon::createFromFormat('Y:m:d H:i:s', $raw);
        } catch (Throwable) {
            return null;
        }
    }

    private function extractCoordinate(array $data, string $valueKey, string $refKey): ?float
    {
        $gps = $data['GPS'] ?? null;

        if (! is_array($gps) || empty($gps[$valueKey]) || ! is_array($gps[$valueKey])) {
            return null;
        }

        $ref = is_string($gps[$refKey] ?? null) ? $gps[$refKey] : '';

        return $this->dmsToDecimal($gps[$valueKey], $ref);
    }

    /**
     * Converts an EXIF DMS coordinate (array of three rational strings,
     * e.g. ["40/1", "26/1", "3600/1600"]) plus a hemisphere reference
     * ('N'/'S'/'E'/'W') into signed decimal degrees. 'S' and 'W' negate
     * the result; 'N' and 'E' (or anything else) leave it positive.
     */
    private function dmsToDecimal(array $dms, string $ref): ?float
    {
        if (count($dms) < 3) {
            return null;
        }

        $degrees = $this->rationalToFloat((string) $dms[0]);
        $minutes = $this->rationalToFloat((string) $dms[1]);
        $seconds = $this->rationalToFloat((string) $dms[2]);

        if ($degrees === null || $minutes === null || $seconds === null) {
            return null;
        }

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        $hemisphere = strtoupper(substr(trim($ref), 0, 1));

        if (in_array($hemisphere, ['S', 'W'], true)) {
            $decimal *= -1;
        }

        return $decimal;
    }

    private function rationalToFloat(string $rational): ?float
    {
        if (! str_contains($rational, '/')) {
            return is_numeric($rational) ? (float) $rational : null;
        }

        [$numerator, $denominator] = explode('/', $rational, 2);

        if (! is_numeric($numerator) || ! is_numeric($denominator) || (float) $denominator === 0.0) {
            return null;
        }

        return (float) $numerator / (float) $denominator;
    }
}
