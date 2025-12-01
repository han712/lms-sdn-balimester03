<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStatus implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        return in_array($value, ['hadir', 'izin', 'sakit', 'alpha']);
    }

    public function message()
    {
        return 'Status harus: hadir, izin, sakit, atau alpha.';
    }
}
