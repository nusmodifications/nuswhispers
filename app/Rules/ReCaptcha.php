<?php

namespace NUSWhispers\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var \ReCaptcha\ReCaptcha $recaptcha */
        $recaptcha = app(\ReCaptcha\ReCaptcha::class);

        if (! $recaptcha->verify($value, request()->getClientIp())->isSuccess()) {
            $fail('The :attribute was not entered correctly. Please try again.');
        }
    }
}
