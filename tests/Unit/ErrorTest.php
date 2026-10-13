<?php

namespace Tests\Unit;

use App\Models\App;
use App\Models\User;
use App\Models\Error;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorTest extends TestCase
{
    use RefreshDatabase;

    public function -()
    {
        $user = User::factory()->create();

        $app = App::factory()->for($user)->create();

        $this->assertEquals(1, $user->apps()->count());

        $this->assertEquals($app->user_id, $user->id);
    }
}
