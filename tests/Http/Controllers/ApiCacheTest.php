<?php

namespace NUSWhispers\Tests\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use NUSWhispers\Models\Category;
use NUSWhispers\Models\Confession;

class ApiCacheTest extends ConfessionsControllerTestCase
{
    public function testApiResponsesCanBeReadFromFileCacheWithoutUnserializingObjects(): void
    {
        $path = sys_get_temp_dir() . '/nuswhispers-cache-' . bin2hex(random_bytes(8));
        config([
            'cache.default' => 'file',
            'cache.stores.file.path' => $path,
            'cache.serializable_classes' => false,
        ]);
        Cache::forgetDriver('file');
        $this->beforeApplicationDestroyed(static function () use ($path) {
            File::deleteDirectory($path);
        });

        $category = Category::factory()->create();
        $confession = Confession::factory()->approved()->create();
        $confession->categories()->attach($category);

        foreach (['/api/categories', '/api/confessions', '/api/confessions/' . $confession->getKey()] as $url) {
            $first = $this->getJson($url)->assertOk();
            $this->getJson($url)->assertOk()->assertExactJson($first->json());
        }
    }
}
