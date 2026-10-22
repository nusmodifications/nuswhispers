<?php

namespace NUSWhispers\Tests\Models;

use Carbon\Carbon;
use NUSWhispers\Models\ApiKey;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionLog;
use NUSWhispers\Models\ConfessionQueue;
use NUSWhispers\Models\ModeratorComment;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class DateSerializationTest extends TestCase
{
    public function testDatesKeepTheExistingApiFormat(): void
    {
        $date = Carbon::create(2026, 10, 22, 12, 34, 56);
        $models = [
            [new Confession(), ['created_at', 'updated_at', 'status_updated_at']],
            [new ConfessionLog(), ['created_on']],
            [new ConfessionQueue(), ['update_status_at']],
            [new ApiKey(), ['created_on', 'last_used_on']],
            [new ModeratorComment(), ['created_at']],
            [new User(), ['created_at', 'updated_at']],
        ];

        foreach ($models as [$model, $fields]) {
            foreach ($fields as $field) {
                $model->setAttribute($field, $date);
                $this->assertInstanceOf(Carbon::class, $model->getAttribute($field));
                $this->assertSame('2026-10-22 12:34:56', $model->toArray()[$field]);
            }
        }
    }
}
