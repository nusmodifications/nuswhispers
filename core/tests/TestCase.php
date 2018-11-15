<?php

namespace NUSWhispers\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use DatabaseTransactions;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost:8080';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Runs a GraphQL query.
     *
     * @param string $query
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function graphql(string $query): TestResponse
    {
        return $this->post(
            config('lighthouse.route.prefix') . '/' . config('lighthouse.route_name'),
            ['query' => $query]
        );
    }
}
