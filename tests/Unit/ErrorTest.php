<?php

namespace Tests\Unit;

use App\Models\App;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_app()
    {
        $user = User::factory()->create();

        $app = App::factory()->for($user)->create();

        $this->assertEquals(1, $user->apps()->count());

        $this->assertEquals($app->user_id, $user->id);
    }
}
