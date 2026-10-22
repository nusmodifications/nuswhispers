<?php

namespace NUSWhispers\Models\Concerns;

use DateTimeInterface;

trait SerializesApiDates
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
