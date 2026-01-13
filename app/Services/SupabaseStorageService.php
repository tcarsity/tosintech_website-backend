<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    protected static function headers()
    {
        return [
            'Authorization' => 'Bearer ' . config('services.supabase.service_key'),
            'apikey'        => config('services.supabase.service_key'),
        ];
    }


    public static function upload(string $path, string $filePath, string $mime)
    {
        $url = rtrim(config('services.supabase.url'), '/') .
            '/storage/v1/object/' .
            config('services.supabase.bucket') . '/' . $path;

        Http::withHeaders(self::headers())
            ->withBody(file_get_contents($filePath), $mime)
            ->put($url);
    }


    public static function delete(string $path)
    {
        $url = rtrim(config('services.supabase.url'), '/') .
            '/storage/v1/object/' .
            config('services.supabase.bucket') . '/' . $path;

        Http::withHeaders(self::headers())->delete($url);
    }

    public static function publicUrl(string $path): string
    {
        return rtrim(config('services.supabase.url'), '/') .
            '/storage/v1/object/public/' .
            config('services.supabase.bucket') . '/' . $path;
    }
}
