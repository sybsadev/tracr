<?php

namespace App\Services;

use App\Exceptions\ApiKeyGenException;
use App\Models\App;
use App\Models\AppKey;

class ApiKeyService
{
    private const PREFIX = 'tracr_live_';

    public function __construct()
    {

    }

    public function new_key(App $app, string $name): array
    {
        if (! $app->is_active) {
            throw new ApiKeyGenException('App isnt active');
        }

	$name = trim($name);

        if ($name === '') {
            throw new ApiKeyGenException('Name is required for key');
        }

        $public = $this->generate_public();
        $secret = $this->generate_secret();
        $secretHash = hash('sha256', $secret);

        $combined = $this->combine_key($public, $secret);

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

    private function generate_secret(): string
    {
        $bytes = random_bytes(32);

        $secret = bin2hex($bytes);

        return $secret;
    }

    private function generate_public(): string
    {
        $bytes = random_bytes(8);
        $public = bin2hex($bytes);

        return $public;
    }

    private function combine_key(string $public, string $secret): string
    {
        return self::PREFIX.$public.'_'.$secret;
    }
}
