<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Category;
use NUSWhispers\Models\Confession;

class ConfessionsControllerCategoryTest extends ConfessionsControllerTestCase
{
    public function testCategory()
    {
        $category = factory(Category::class)->create();

        factory(Confession::class)
            ->states('approved')
            ->create()
            ->categories()
            ->sync([$category->getKey()]);

        $this->json('GET', '/api/confessions/category/' . $category->getKey())
            ->assertJsonFragment(['confession_category_id' => $category->getKey()]);
    }
}
