<?php

namespace NUSWhispers\Tests;

class ExampleTest extends TestCase
{
    /**
     * Test visiting admin page as guest.
     *
     * @return void
     */
    public function testAdminAsGuest()
    {
        $this->get('/admin')
            ->assertRedirect('/login');
    }
}
