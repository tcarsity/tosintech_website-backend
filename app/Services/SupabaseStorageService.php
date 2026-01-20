<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected static function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.supabase.service_role_key'),
            'apikey'        => config('services.supabase.service_role_key'),
        ];
    }

    /**
     * Upload file to Supabase Storage
     *
     * @throws \Exception
     */

     public static function upload(string $path, string $filePath, string $mime): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $url = rtrim(config('services.supabase.url'), '/') .
            '/storage/v1/object/' .
            config('services.supabase.bucket') . '/' . $path .
            '?upsert=true';

        $headers = array_merge(
            self::headers(),
            ['Content-Type' => $mime]
        );

        $response = Http::withHeaders($headers)
            ->send('PUT', $url, [
                'body' => fopen($filePath, 'r'),
            ]);

        if (! $response->successful()) {
            throw new \Exception(
                'Supabase upload failed: ' .
                $response->status() . ' - ' . $response->body()
            );
        }
    }

    /**
     * Delete file from Supabase Storage
     */

    public static function delete(string $path): void
    {
        $url = rtrim(config('services.supabase.url'), '/') .
            '/storage/v1/object/' .
            config('services.supabase.bucket') . '/' . $path;

        $response = Http::withHeaders(self::headers())->delete($url);

        Log::info('SUPABASE DELETE RESPONSE', [
            'url'    => $url,
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
    }

    /**
     * Get public URL for file
     */
    public static function getPublicUrl(string $path): string
    {
        return rtrim(config('services.supabase.url'), '/') .
            '/storage/v1/object/public/' .
            config('services.supabase.bucket') . '/' . $path;
    }
}
