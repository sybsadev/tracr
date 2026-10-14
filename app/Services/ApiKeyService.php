<?php

namespace App\Services;

use App\Exceptions\ApiKeyGenException;
use App\Exceptions\ApiKeyVerificationException;
use App\Models\App;
use App\Models\AppKey;

class ApiKeyService
{
    private const PREFIX = 'tracr_live_';

    public function create(App $app, string $name): array
    {
        if (! $app->is_active) {
            throw new ApiKeyGenException('App isnt active');
        }

        $name = trim($name);

        if ($name === '') {
            throw new ApiKeyGenException('Name is required for key');
        }

        $public = $this->generatePublic();
        $secret = $this->generateSecret();
        $secretHash = hash('sha256', $secret);

        $combined = $this->combineKey($public, $secret);

        $key = AppKey::create([
            'app_id' => $app->id,
            'name' => $name,
            'public_id' => $public,
            'secret_hash' => $secretHash,
            'is_active' => true,
        ]);

        return [
            'key_record' => $key,
            'secret_key' => $combined,
        ];
    }

    public function verify(string $key): AppKey
    {
        if (! str_starts_with($key, self::PREFIX)) {
            $this->verificationFailed();
        }

        $noPrefix = explode(self::PREFIX, $key);

        $sep = explode('_', $noPrefix[1]);

        if (count($sep) < 2) {
            $this->verificationFailed();
        }

        $public = $sep[0];
        $secret = $sep[1];

        $record = AppKey::where('public_id', $public)->first();

        if (! $record) {
            $this->verificationFailed();
        }

        if (! $record->is_active) {
            $this->verificationFailed();
        }

        if (! $record->app->is_active) {
            $this->verificationFailed();
        }

        if (! $record->app->user->is_active) {
            $this->verificationFailed();
        }

        if ($record->expires_at !== null && $record->expires_at <= now()) {
            $this->verificationFailed();
        }

        $hash = hash('sha256', $secret);

        if (! hash_equals($record->secret_hash, $hash)) {
            $this->verificationFailed();
        }

        return $record;
    }

    private function verificationFailed(): never
    {
        throw new ApiKeyVerificationException;
    }

    private function generateSecret(): string
    {
        $bytes = random_bytes(32);

        $secret = bin2hex($bytes);

        return $secret;
    }

    private function generatePublic(): string
    {
        $bytes = random_bytes(8);
        $public = bin2hex($bytes);

        return $public;
    }

    private function combineKey(string $public, string $secret): string
    {
        return self::PREFIX.$public.'_'.$secret;
    }
}
