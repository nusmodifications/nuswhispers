<?php

namespace NUSWhispers\Rules;

use Illuminate\Contracts\Validation\Rule;

class ReCaptcha implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        /** @var \ReCaptcha\ReCaptcha $recaptcha */
        $recaptcha = app(\ReCaptcha\ReCaptcha::class);

        return $recaptcha->verify($value, request()->getClientIp())->isSuccess();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute was not entered correctly. Please try again.';
    }
}
