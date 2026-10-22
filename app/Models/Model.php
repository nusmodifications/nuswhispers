<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use NUSWhispers\Models\Concerns\SerializesApiDates;

abstract class Model extends EloquentModel
{
    use SerializesApiDates;
}
