<?php

namespace NUSWhispers\Tests\Http\Controllers;

use anlutro\LaravelSettings\Facade as Settings;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class SettingsAdminControllerTest extends TestCase
{
    public function testAdministratorCanViewExistingSettings(): void
    {
        $this->withoutVite();

        Settings::set([
            'word_blacklist' => 'blocked, forbidden',
            'rejection_net_score' => 17,
            'rejection_decay' => 45,
        ]);
        Settings::save();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('blocked, forbidden')
            ->assertSee('value="17"', false)
            ->assertSee('value="45"', false);
    }
}
