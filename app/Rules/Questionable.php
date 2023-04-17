<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Questionable implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            if (strlen($value) < 3) {
                $fail('Question is too short');
            }
        } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
            if ($value->getSize() > 1000000) {
                $fail('File is too big');
            }
        } else {
            $fail('Question is not a string or file');
        }
    }
}
