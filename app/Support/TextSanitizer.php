<?php

namespace App\Support;

class TextSanitizer
{
    public static function clean(string $text): string
    {
        // Remove invisible/control characters (zero-width, zalgo stacking, etc.)
        $text = (string) preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00AD}]/u', '', $text);
        // Remove \x00-\x08, \x0b-\x0c, \x0e-\x1f
        $text = (string) preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', '', $text);
        // Remove zalgo combining marks stacking (3+ consecutive combining characters)
        $text = (string) preg_replace('/(\p{M}{3})\p{M}+/u', '$1', $text);

        // Normalize Unicode to NFC
        if (class_exists('Normalizer')) {
            $normalized = \Normalizer::normalize($text, \Normalizer::NFC);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        // Collapse repeated characters: max 3 of same char in a row
        $text = (string) preg_replace('/(.)\1{3,}/u', '$1$1$1', $text);

        // Collapse repeated emoji: more than 3 → 3
        // Match emoji sequences (including ZWJ, variation selectors, etc.) repeated > 3 times
        $text = (string) preg_replace_callback('/(\X)\1{3,}/u', static function (array $m): string {
            return str_repeat($m[1], 3);
        }, $text);

        // Collapse excessive whitespace and blank lines
        $text = (string) preg_replace('/[ \t]+/', ' ', $text);
        $text = (string) preg_replace('/(\r\n|\r|\n){3,}/', "\n\n", $text);

        return trim($text);
    }
}
