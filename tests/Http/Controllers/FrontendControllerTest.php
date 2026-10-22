<?php

namespace NUSWhispers\Tests\Http\Controllers;

use Illuminate\Support\Facades\File;
use NUSWhispers\Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FrontendControllerTest extends TestCase
{
    private string $frontendPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->frontendPath = sys_get_temp_dir() . '/nuswhispers-frontend-' . bin2hex(random_bytes(8));
        File::makeDirectory($this->frontendPath . '/_void/pages', 0755, true);
        File::put($this->frontendPath . '/app.html', '<html>root shell</html>');
        File::put($this->frontendPath . '/app-route.html', '<html>route shell</html>');
        File::put($this->frontendPath . '/_void/pages/index.json', '{"component":"index","props":{}}');
        File::put($this->frontendPath . '/_void/pages/home.json', '{"component":"[...path]","props":{}}');
        $this->app->usePublicPath($this->frontendPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->frontendPath);

        parent::tearDown();
    }

    public function testRootAndDeepLinksUseTheCorrectStaticComponent(): void
    {
        $response = $this->get('/')->assertOk()->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $this->assertSame($this->frontendPath . '/app.html', $response->baseResponse->getFile()->getPathname());

        foreach (['/category/7/', '/tag/nus/', '/search/hello%20world/', '/confession/42/', '/mobile_submit', '/policy/'] as $url) {
            $response = $this->withHeader('User-Agent', 'Mozilla/5.0')->get($url)->assertOk();
            $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
            $this->assertSame($this->frontendPath . '/app-route.html', $response->baseResponse->getFile()->getPathname());
        }
    }

    public function testDynamicVoidNavigationUsesTheCatchAllPayload(): void
    {
        foreach (['category/7.json', 'search/hello%20world/.json', 'confession/42.json'] as $path) {
            $response = $this->get('/_void/pages/' . $path)->assertOk()->assertHeader('Content-Type', 'application/json');
            $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
            $this->assertSame($this->frontendPath . '/_void/pages/home.json', $response->baseResponse->getFile()->getPathname());
        }

        $this->get('/_void/pages/category/7.php')->assertNotFound();
    }

    public function testApiRoutesStillTakePriorityOverTheSpaShell(): void
    {
        $this->getJson('/api/categories')->assertOk()->assertJsonPath('data.categories', []);
        $this->getJson('/api/not-a-route')->assertOk()->assertContent('');
    }
}
