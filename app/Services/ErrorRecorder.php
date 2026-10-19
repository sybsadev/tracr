<?php

namespace App\Services;

use App\Models\App;
use App\Models\Error;
use App\Models\ErrorEvent;
use Illuminate\Support\Facades\DB;

class ErrorRecorder
{
    public function record(App $app, array $data)
    {
        $fingerprint = $this->createFingerprint($app, $data);

        $event = DB::transaction(function () use ($fingerprint, $data, $app) {
            $error = Error::firstOrCreate([
                'app_id' => $app->id,
                'fingerprint' => $fingerprint,
            ], [
                'app_id' => $app->id,
                'env' => $data['env'],
                'exception' => $data['exception'],
                'file' => $data['file'],
                'line' => $data['line'],
                'fingerprint' => $fingerprint,
            ]);

            return ErrorEvent::create([
                'error_id' => $error->id,
                'message' => $data['message'],
            ]);
        });

        return $event;
    }

    private function createFingerprint(App $app, array $data): string
    {
        $parts = [$data['env'], $data['file'], $data['line'], $data['exception']];

        $fingerprint = json_encode($parts);

        $hashed = hash('sha256', $fingerprint);

        return $hashed;
    }
}
