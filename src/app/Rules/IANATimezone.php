<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use DateTimeZone;

class IANATimezone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, DateTimeZone::listIdentifiers(DateTimeZone::ALL))) {
            $fail('The :attribute must be a valid IANA timezone.');
        }
    }
}
