<?php

namespace App\Support;

class ContentFilter
{
    public static function containsLink(?string $text): bool
    {
        if ($text === null || $text === '') {
            return false;
        }
        $pattern = '/(https?:\/\/|www\.|(?:[a-zA-Z0-9-]+\.(?:com|net|org|id|io|co|me|ly|app|link|page)))/i';

        return (bool) preg_match($pattern, $text);
    }

    public static function containsBadWord(?string $text): bool
    {
        if ($text === null || $text === '') {
            return false;
        }
        /** @var array<int, string> $badWords */
        $badWords = require __DIR__.'/bad_words.php';

        // Normalize: lowercase
        $normalized = mb_strtolower($text, 'UTF-8');
        // Leet-speak substitutions
        $normalized = str_replace(
            ['4', '3', '0', '1', '5'],
            ['a', 'e', 'o', 'i', 's'],
            $normalized
        );
        // Also handle '1' as 'l' — do a second pass for l
        $normalized = str_replace('1', 'l', $normalized);
        // Remove inserted spaces, dots, underscores
        $normalized = preg_replace('/[ ._]+/', '', $normalized);

        foreach ($badWords as $word) {
            if (str_contains($normalized, $word)) {
                return true;
            }
        }

        return false;
    }
}
