<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Category;
use NUSWhispers\Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        cache()->clear();
    }

    public function testIndex()
    {
        $this->json('GET', '/api/categories')
            ->assertStatus(200);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();

        $this->json('GET', '/api/categories/' . $category->getKey())
            ->assertJsonFragment(['confession_category_id' => $category->getKey()]);
    }
}
