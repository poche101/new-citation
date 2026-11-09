<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Fails when a text has more than $max words.
 *
 * Words are counted the same way as the live counter on the forms
 * (split on whitespace), so what the user sees is what gets enforced.
 * Unlike str_word_count(), this also handles numbers and accented
 * characters (e.g. Yoruba names) correctly.
 */
class MaxWords implements ValidationRule
{
    public function __construct(private int $max)
    {
    }

    public static function count(?string $text): int
    {
        $words = preg_split('/\s+/u', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY);

        return $words === false ? 0 : count($words);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (self::count($value) > $this->max) {
            $fail("The :attribute must not exceed {$this->max} words.");
        }
    }
}
